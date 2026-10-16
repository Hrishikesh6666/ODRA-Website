<?php

namespace App\Services;

use App\Models\Enhancement;
use App\Models\FreightRule;
use App\Models\ModelConfiguration;
use App\Models\ModelPrice;
use App\Models\PricingBasis;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteSnapshot;
use App\Models\QuoteVersion;
use App\Models\SweeperModel;
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
        $driver = DB::connection()->getDriverName();
        $castType = ($driver === 'pgsql' || $driver === 'sqlite') ? 'INTEGER' : 'UNSIGNED';

        $query = Quote::withoutGlobalScopes();
        if ($driver === 'pgsql') {
            $query->whereRaw("quote_number ~ '^[0-9]+$'");
        }

        $maxQuoteNumber = $query
            ->selectRaw("MAX(CAST(quote_number AS {$castType})) as max_num")
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

        // If base64 data URI (e.g. data:application/pdf;filename=...;base64,...), decode it to raw PDF binary
        if (preg_match('/^data:application\/pdf.*?;base64,(.+)$/s', $pdfContent, $matches)) {
            $decoded = base64_decode($matches[1]);
            if ($decoded !== false) {
                $pdfContent = $decoded;
            }
        } elseif (str_starts_with($pdfContent, 'data:application/pdf;base64,')) {
            $decoded = base64_decode(substr($pdfContent, 28));
            if ($decoded !== false) {
                $pdfContent = $decoded;
            }
        } elseif (! str_starts_with($pdfContent, '%PDF-')) {
            $decoded = base64_decode(trim($pdfContent));
            if ($decoded !== false && str_starts_with($decoded, '%PDF-')) {
                $pdfContent = $decoded;
            }
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
     * Helper to populate or update snapshot record with authoritative server recalculation.
     */
    protected function createOrUpdateSnapshot(QuoteVersion $version, array $data): QuoteSnapshot
    {
        $state = $data['state'] ?? [];
        $rawEnhancements = $data['enhancements'] ?? $state['enh'] ?? [];

        $modelCode = $state['model'] ?? $data['model_code'] ?? 'MT4H';
        $chassisLabel = $state['chassis'] ?? $data['chassis_configuration'] ?? '';
        $basisName = $state['basis'] ?? $data['pricing_basis'] ?? 'MSRP (Commercial)';
        $fobTerms = $state['fob'] ?? $data['fob_terms'] ?? 'Transport, estimate only noted in quote';
        $baseQty = max(1, (int) ($state['baseQty'] ?? $data['base_qty'] ?? 1));

        // Authoritative sales rep name: if authenticated user is not an admin, lock strictly to the logged-in user
        if (auth()->check() && !auth()->user()->isAdmin()) {
            $repName = auth()->user()->name;
        } else {
            $repName = $state['salesRep'] ?? $data['sales_rep_name'] ?? auth()->user()?->name ?? $version->quote->user?->name ?? '';
        }

        // 1. Authoritative Model & Pricing Lookup
        $model = SweeperModel::where('code', $modelCode)->first();
        $modelPrice = $model ? ModelPrice::where('sweeper_model_id', $model->id)->current()->latest('effective_from')->first() : null;
        if (! $modelPrice && $model) {
            $modelPrice = ModelPrice::where('sweeper_model_id', $model->id)->latest('id')->first();
        }

        $basis = PricingBasis::where('name', $basisName)
            ->orWhere('slug', strtolower($basisName))
            ->first();
        $isCoop = $basis ? ($basis->price_field === 'sw_net' || $basis->show_discount) : (! str_contains(strtolower($basisName), 'msrp'));
        $useMunicipality = $basis ? (bool) $basis->use_municipality_freight : $isCoop;

        $baseMsrp = $modelPrice ? (float) $modelPrice->msrp : (float) ($data['base_msrp'] ?? 0);
        $baseList = $isCoop ? ($modelPrice ? (float) $modelPrice->sw_list : $baseMsrp) : $baseMsrp;

        // Calculate cooperative unit price or commercial unit price
        if ($isCoop) {
            $discPct = $basis && $basis->discount_percent !== null ? (float) $basis->discount_percent : 12.00;
            $stdBaseUnitPrice = round($baseList * (1 - ($discPct / 100)), 2);
        } else {
            $stdBaseUnitPrice = $baseMsrp;
        }

        // 2. Extra Discount Validation and Deduction
        $showDiscount = max(0, (float) ($state['baseShowDiscount'] ?? $data['show_discount_amount'] ?? 0));
        if ($showDiscount > 20000.00) {
            throw new Exception("Extra discount cannot exceed the maximum permitted cap of $20,000.00 (entered: $" . number_format($showDiscount, 2) . ").");
        }

        // Apply discount directly so the customer actually benefits from the price reduction
        $baseUnitPrice = max(0, round($stdBaseUnitPrice - $showDiscount, 2));
        $baseExtended = round($baseUnitPrice * $baseQty, 2);

        // 3. Chassis Upgrade Calculation
        $chassisCfg = ModelConfiguration::where('label', $chassisLabel)->first();
        $chassisRawPrice = $chassisCfg ? (float) $chassisCfg->price : 0.00;
        $chassisUnitPrice = 0.00;
        $chassisExtended = 0.00;
        if ($chassisRawPrice > 0) {
            $chassisDiscPct = $isCoop ? 12.00 : 0.00;
            $chassisUnitPrice = round($chassisRawPrice * (1 - ($chassisDiscPct / 100)), 2);
            $chassisExtended = round($chassisUnitPrice * $baseQty, 2);
        }

        // 4. Authoritative Enhancements Calculation
        $enhancementsSnapshot = [];
        $enhancementsTotal = 0.00;

        if ($chassisRawPrice > 0) {
            $enhancementsSnapshot[] = [
                'desc' => "Chassis Option: {$chassisLabel}",
                'category' => 'CHASSIS CONFIGURATION',
                'qty' => $baseQty,
                'msrp' => $chassisRawPrice,
                'list' => $chassisRawPrice,
                'unit' => $chassisUnitPrice,
                'extended' => $chassisExtended,
            ];
        }

        foreach ($rawEnhancements as $enh) {
            $desc = is_array($enh) ? ($enh['desc'] ?? '') : '';
            if (empty($desc) || str_starts_with($desc, 'Chassis Option:')) {
                continue;
            }

            $qty = max(1, (int) ($enh['qty'] ?? 1));
            $dbEnh = Enhancement::where('description', $desc)->first();

            if ($dbEnh) {
                $enhMsrp = (float) $dbEnh->msrp;
                $enhList = (float) $dbEnh->sw_list ?: $enhMsrp;
                if ($isCoop) {
                    $enhUnit = (float) $dbEnh->sw_net ?: $enhList;
                } else {
                    $enhUnit = $enhMsrp;
                }
                $enhExt = round($enhUnit * $qty, 2);
                $enhancementsTotal += $enhExt;

                $enhancementsSnapshot[] = [
                    'desc' => $dbEnh->description,
                    'category' => $dbEnh->category,
                    'qty' => $qty,
                    'msrp' => $enhMsrp,
                    'list' => $enhList,
                    'unit' => $enhUnit,
                    'extended' => $enhExt,
                ];
            }
        }

        // 5. Authoritative Freight Matrix Calculation
        $isExw = str_contains(strtolower($fobTerms), 'exw') || str_contains(strtolower($fobTerms), 'factory pick-up');
        $freightAmount = 0.00;
        $repTerritory = $state['repTerritory'] ?? null;

        if ($isExw) {
            $freightAmount = 0.00;
        } else {
            // Find sales rep and their assigned location
            $repUser = User::where('name', $repName)->first();
            if (! $repUser && $version->quote && $version->quote->user) {
                $repUser = $version->quote->user;
            }

            // Determine destination state and city selected by the representative
            $destState = $state['destState'] ?? null;
            $destCity = $state['destCity'] ?? null;

            // If not directly in state, try parsing repTerritory "City, State"
            if ((!$destState || !$destCity) && !empty($state['repTerritory'])) {
                $parts = explode(',', $state['repTerritory']);
                if (count($parts) >= 2) {
                    $destCity = trim($parts[0]);
                    $destState = trim($parts[1]);
                }
            }

            // Fallback to rep's primary state/city if still empty
            if (!$destState && $repUser) {
                $assigned = $repUser->getAssignedStates();
                $destState = $repUser->state ?: ($assigned[0] ?? null);
            }
            if (!$destCity && $repUser) {
                $destCity = $repUser->city;
            }

            if (! $destState || ! $destCity) {
                throw new Exception("Quotation generation failed: Please select a valid delivery State and City destination from your assigned coverage area.");
            }

            // Validate that the chosen state is assigned to the sales rep (unless Admin)
            if ($repUser && auth()->check() && !auth()->user()->isAdmin()) {
                $allowedStates = $repUser->getAssignedStates();
                if (!empty($allowedStates) && !in_array($destState, $allowedStates)) {
                    throw new Exception("Quotation generation failed: State '{$destState}' is not within sales representative {$repUser->name}'s assigned territories (" . implode(', ', $allowedStates) . ").");
                }
            }

            $freightRule = FreightRule::where('state', $destState)
                ->where('city', $destCity)
                ->first();

            if (! $freightRule) {
                throw new Exception("Quotation generation failed: No valid freight rule found in the Freight Matrix for '{$destCity}, {$destState}'.");
            }

            $rate = $useMunicipality ? (float) $freightRule->municipality_rate : (float) $freightRule->customer_rate;
            if ($rate <= 0) {
                throw new Exception("Quotation generation failed: The freight rate for '{$destCity}, {$destState}' is $0.00 in the Freight Matrix. A valid rate is required.");
            }

            $freightAmount = $rate;
            $repTerritory = "{$freightRule->city}, {$freightRule->state}";
            $state['destState'] = $freightRule->state;
            $state['destCity'] = $freightRule->city;
        }

        // 6. Subtotal & Total
        $subtotal = round($baseExtended + $chassisExtended + $enhancementsTotal, 2);
        $total = round($subtotal + $freightAmount, 2);

        // Keep state synchronized with authoritative values
        $state['freight'] = $freightAmount;
        $state['baseShowDiscount'] = $showDiscount;
        $state['repTerritory'] = $repTerritory;

        return QuoteSnapshot::updateOrCreate(
            ['quote_version_id' => $version->id],
            [
                'customer_company' => $state['toCompany'] ?? $data['customer'] ?? 'Untitled Customer',
                'customer_contact' => $state['toContact'] ?? null,
                'customer_address_1' => $state['toAddr1'] ?? null,
                'customer_address_2' => $state['toAddr2'] ?? null,
                'model_code' => $modelCode,
                'model_description' => $data['model_description'] ?? ($model ? $model->description : ''),
                'chassis_configuration' => $chassisLabel,
                'pricing_basis' => $basisName,
                'sales_rep_name' => $repName ?: 'Sales Representative',
                'rep_territory' => $repTerritory,
                'po_number' => $state['po'] ?? null,
                'customer_account' => $state['acct'] ?? null,
                'fob_terms' => $fobTerms,
                'base_qty' => $baseQty,
                'base_msrp' => $isCoop ? $baseList : $baseMsrp,
                'base_list_price' => $baseList,
                'base_unit_price' => $baseUnitPrice,
                'show_discount_amount' => $showDiscount,
                'freight_amount' => $freightAmount,
                'subtotal' => $subtotal,
                'total' => $total,
                'quote_code' => $data['quote_code'] ?? null,
                'enhancements_snapshot' => $enhancementsSnapshot,
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
                'item_type' => $enh['item_type'] ?? 'enhancement',
                'source_enhancement_id' => $sourceEnh?->id,
                'description' => $enh['desc'],
                'category' => $enh['category'] ?? $enh['cat'] ?? ($sourceEnh?->category ?? 'ENHANCEMENT'),
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
