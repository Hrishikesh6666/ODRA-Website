<?php

namespace App\Services;

use App\Models\Enhancement;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteSnapshot;
use App\Models\QuoteVersion;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class QuoteService
{
    /**
     * Generate the next available quote sequence number.
     */
    public function generateNextQuoteNumber(): string
    {
        $maxQuoteNumber = Quote::withoutGlobalScopes()
            ->selectRaw('MAX(CAST(quote_number AS UNSIGNED)) as max_num')
            ->value('max_num');

        $nextNum = $maxQuoteNumber ? (int) $maxQuoteNumber + 1 : 3001;

        return (string) max($nextNum, 3001);
    }

    /**
     * Create a new draft quote.
     */
    public function createDraft(User $user, array $data): Quote
    {
        return DB::transaction(function () use ($user, $data) {
            $quoteNumber = $data['quote_number'] ?? $this->generateNextQuoteNumber();

            $quote = Quote::create([
                'user_id' => $user->id,
                'quote_number' => $quoteNumber,
                'current_version' => 1,
                'status' => 'Draft',
            ]);

            $version = QuoteVersion::create([
                'quote_id' => $quote->id,
                'version_number' => 1,
                'status' => 'Draft',
            ]);

            $snapshot = $this->createOrUpdateSnapshot($version, $data);

            AuditService::log(
                action: 'QUOTE_DRAFT_CREATED',
                entityType: 'Quote',
                entityId: $quote->id,
                newValue: [
                    'quote_number' => $quote->quote_number,
                    'version' => 1,
                    'customer' => $snapshot->customer_company,
                    'model' => $snapshot->model_code,
                    'total' => $snapshot->total,
                ],
                notes: "Draft Q-{$quote->quote_number} v1 created for {$snapshot->customer_company}",
                userId: $user->id
            );

            return $quote->load(['versions.snapshot']);
        });
    }

    /**
     * Update an existing draft quote.
     */
    public function updateDraft(Quote $quote, array $data): Quote
    {
        return DB::transaction(function () use ($quote, $data) {
            $version = $quote->versions()->where('version_number', $quote->current_version)->firstOrFail();

            if ($version->isFinalized()) {
                throw new Exception('Finalized quotations cannot be edited directly. Please create a new revision.');
            }

            $snapshot = $this->createOrUpdateSnapshot($version, $data);

            AuditService::log(
                action: 'QUOTE_DRAFT_UPDATED',
                entityType: 'Quote',
                entityId: $quote->id,
                newValue: [
                    'quote_number' => $quote->quote_number,
                    'version' => $version->version_number,
                    'customer' => $snapshot->customer_company,
                    'total' => $snapshot->total,
                ],
                notes: "Draft Q-{$quote->quote_number} v{$version->version_number} saved",
                userId: $quote->user_id
            );

            return $quote->load(['versions.snapshot']);
        });
    }

    /**
     * Finalize a quote, freezing all snapshot prices and writing normalized items.
     */
    public function finalizeQuote(Quote $quote, array $data): Quote
    {
        return DB::transaction(function () use ($quote, $data) {
            $version = $quote->versions()->where('version_number', $quote->current_version)->firstOrFail();

            if ($version->isFinalized()) {
                return $quote;
            }

            // Update snapshot with latest state
            $snapshot = $this->createOrUpdateSnapshot($version, $data);

            // Mark version and quote as Finalized
            $version->update([
                'status' => 'Finalized',
                'finalized_at' => now(),
            ]);

            $quote->update([
                'status' => 'Finalized',
            ]);

            // Create normalized QuoteItems from snapshot
            $this->syncQuoteItems($version, $snapshot);

            AuditService::log(
                action: 'QUOTE_FINALIZED',
                entityType: 'Quote',
                entityId: $quote->id,
                newValue: [
                    'quote_number' => $quote->quote_number,
                    'version' => $version->version_number,
                    'customer' => $snapshot->customer_company,
                    'model' => $snapshot->model_code,
                    'total' => $snapshot->total,
                    'finalized_at' => now()->toDateTimeString(),
                ],
                notes: "Quotation Q-{$quote->quote_number} v{$version->version_number} finalized for {$snapshot->customer_company}. Snapshot frozen.",
                userId: $quote->user_id
            );

            return $quote->load(['versions.snapshot', 'versions.items']);
        });
    }

    /**
     * Create a new revision of a finalized quote.
     */
    public function createRevision(Quote $quote, User $user): Quote
    {
        return DB::transaction(function () use ($quote, $user) {
            $latestVersion = $quote->versions()->orderByDesc('version_number')->firstOrFail();
            $newVersionNumber = $latestVersion->version_number + 1;

            // Mark previous version as Revised if it was Finalized
            if ($latestVersion->status === 'Finalized') {
                $latestVersion->update(['status' => 'Revised']);
            }

            $newVersion = QuoteVersion::create([
                'quote_id' => $quote->id,
                'version_number' => $newVersionNumber,
                'status' => 'Draft',
            ]);

            $quote->update([
                'current_version' => $newVersionNumber,
                'status' => 'Draft',
            ]);

            // Clone previous snapshot data
            $prevSnapshot = $latestVersion->snapshot;
            if ($prevSnapshot) {
                $snapshotData = $prevSnapshot->toArray();
                unset($snapshotData['id'], $snapshotData['created_at'], $snapshotData['updated_at'], $snapshotData['pdf_path'], $snapshotData['pdf_generated_at']);
                $snapshotData['quote_version_id'] = $newVersion->id;
                
                // Update date in full_state to today
                if (isset($snapshotData['full_state']['date'])) {
                    $snapshotData['full_state']['date'] = now()->format('Y-m-d');
                }

                QuoteSnapshot::create($snapshotData);
            }

            AuditService::log(
                action: 'QUOTE_REVISION_CREATED',
                entityType: 'Quote',
                entityId: $quote->id,
                notes: "New revision v{$newVersionNumber} created from v{$latestVersion->version_number}",
                userId: $user->id
            );

            return $quote->load(['versions.snapshot']);
        });
    }

    /**
     * Store PDF binary / base64 content for a quote version.
     */
    public function storePdf(Quote $quote, int $versionNumber, string $pdfContent): string
    {
        $version = $quote->versions()->where('version_number', $versionNumber)->firstOrFail();
        $snapshot = $version->snapshot;

        $path = "quotes/{$quote->user_id}/Q-{$quote->quote_number}_v{$versionNumber}.pdf";

        // If base64, decode it
        if (str_starts_with($pdfContent, 'data:application/pdf;base64,')) {
            $pdfContent = base64_decode(substr($pdfContent, 28));
        }

        Storage::disk('local')->put("private/{$path}", $pdfContent);

        if ($snapshot) {
            $snapshot->update([
                'pdf_path' => $path,
                'pdf_generated_at' => now(),
            ]);
        }

        return $path;
    }

    /**
     * Helper to populate or update snapshot record.
     */
    protected function createOrUpdateSnapshot(QuoteVersion $version, array $data): QuoteSnapshot
    {
        $state = $data['state'] ?? [];
        $enhancements = $state['enh'] ?? [];

        return QuoteSnapshot::updateOrCreate(
            ['quote_version_id' => $version->id],
            [
                'customer_company' => $state['toCompany'] ?? $data['customer'] ?? 'Untitled Customer',
                'customer_contact' => $state['toContact'] ?? null,
                'customer_address_1' => $state['toAddr1'] ?? null,
                'customer_address_2' => $state['toAddr2'] ?? null,
                'model_code' => $state['model'] ?? 'MT4H',
                'model_description' => $data['model_description'] ?? '',
                'chassis_configuration' => $state['chassis'] ?? '',
                'pricing_basis' => $state['basis'] ?? 'MSRP (Commercial)',
                'sales_rep_name' => $state['salesRep'] ?? auth()->user()->name,
                'rep_territory' => $state['repTerritory'] ?? null,
                'po_number' => $state['po'] ?? null,
                'customer_account' => $state['acct'] ?? null,
                'fob_terms' => $state['fob'] ?? null,
                'base_qty' => (int) ($state['baseQty'] ?? 1),
                'base_msrp' => (float) ($data['base_msrp'] ?? 0),
                'base_list_price' => (float) ($data['base_list_price'] ?? 0),
                'base_unit_price' => (float) ($data['base_unit_price'] ?? 0),
                'show_discount_amount' => (float) ($state['baseShowDiscount'] ?? 0),
                'freight_amount' => (float) ($state['freight'] ?? 0),
                'subtotal' => (float) ($data['subtotal'] ?? 0),
                'total' => (float) ($data['total'] ?? 0),
                'quote_code' => $data['quote_code'] ?? null,
                'enhancements_snapshot' => $enhancements,
                'full_state' => $state,
            ]
        );
    }

    /**
     * Helper to populate normalized items table upon finalization.
     */
    protected function syncQuoteItems(QuoteVersion $version, QuoteSnapshot $snapshot): void
    {
        $version->items()->delete();

        // 1. Base unit item
        QuoteItem::create([
            'quote_version_id' => $version->id,
            'item_type' => 'base_unit',
            'description' => "{$snapshot->model_code} Sweeper - {$snapshot->chassis_configuration}",
            'category' => 'BASE UNIT',
            'qty' => $snapshot->base_qty,
            'msrp_at_time' => $snapshot->base_msrp,
            'list_price_at_time' => $snapshot->base_list_price,
            'unit_price_at_time' => $snapshot->base_unit_price,
            'extended_price' => $snapshot->base_qty * $snapshot->base_unit_price,
            'sort_order' => 0,
        ]);

        // 2. Enhancement items
        $enhancements = $snapshot->enhancements_snapshot ?? [];
        $order = 1;

        foreach ($enhancements as $enh) {
            if (empty($enh['desc'])) continue;

            $sourceEnh = Enhancement::where('description', $enh['desc'])->first();

            QuoteItem::create([
                'quote_version_id' => $version->id,
                'item_type' => 'enhancement',
                'source_enhancement_id' => $sourceEnh?->id,
                'description' => $enh['desc'],
                'category' => $enh['cat'] ?? ($sourceEnh?->category ?? 'ENHANCEMENT'),
                'qty' => (int) ($enh['qty'] ?? 1),
                'msrp_at_time' => (float) ($enh['msrp'] ?? 0),
                'list_price_at_time' => (float) ($enh['list'] ?? 0),
                'unit_price_at_time' => (float) ($enh['unit'] ?? 0),
                'extended_price' => ((int) ($enh['qty'] ?? 1)) * ((float) ($enh['unit'] ?? 0)),
                'sort_order' => $order++,
            ]);
        }
    }
}
