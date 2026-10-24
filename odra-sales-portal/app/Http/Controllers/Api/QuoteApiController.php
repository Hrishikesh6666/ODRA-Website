<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\QuoteService;
use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuoteApiController extends Controller
{
    use AuthorizesRequests;

    public function __construct(protected QuoteService $quoteService)
    {
    }

    /**
     * List quotations for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Quote::class);

        $query = Quote::with(['user', 'latestVersion.snapshot'])
            ->latest('updated_at');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                    ->orWhereHas('latestVersion.snapshot', function ($sq) use ($search) {
                        $sq->where('customer_company', 'like', "%{$search}%")
                            ->orWhere('model_code', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $quotes = $query->paginate(20)->through(function ($quote) {
            $snapshot = $quote->latestVersion?->snapshot;

            return [
                'id' => $quote->id,
                'quote_number' => $quote->quote_number,
                'version' => $quote->current_version,
                'status' => $quote->status,
                'customer' => $snapshot?->customer_company ?? 'Untitled Customer',
                'model' => $snapshot?->model_code ?? 'MT4H',
                'total' => $snapshot?->total ?? 0,
                'date' => $snapshot?->created_at?->format('Y-m-d') ?? $quote->created_at->format('Y-m-d'),
                'has_pdf' => ! empty($snapshot?->pdf_path),
                'owner' => $quote->user->name,
                'created_at' => $quote->created_at->toIso8601String(),
                'updated_at' => $quote->updated_at->toIso8601String(),
            ];
        });

        return response()->json($quotes);
    }

    /**
     * Create a new draft quotation.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Quote::class);

        $validated = $request->validate([
            'quote_number' => ['nullable', 'string', 'max:20'],
            'customer' => ['nullable', 'string', 'max:255'],
            'model_description' => ['nullable', 'string'],
            'base_msrp' => ['nullable', 'numeric'],
            'base_list_price' => ['nullable', 'numeric'],
            'base_unit_price' => ['nullable', 'numeric'],
            'subtotal' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
            'quote_code' => ['nullable', 'string'],
            'enhancement_pricing_mode' => ['nullable', 'string', 'in:itemized,bundled'],
            'enhancements' => ['nullable', 'array'],
            'state' => ['required', 'array'],
        ]);

        $quote = $this->quoteService->createDraft($request->user(), $validated);

        return response()->json([
            'message' => "Quotation Q-{$quote->quote_number} draft created.",
            'quote' => $quote,
        ], 201);
    }

    /**
     * Retrieve quote details and snapshot for the Quote Builder.
     */
    public function show(Quote $quote): JsonResponse
    {
        $this->authorize('view', $quote);

        $version = $quote->latestVersion;
        $snapshot = $version?->snapshot;
        $user = auth()->user();
        $isOwner = ($user && $quote->user_id === $user->id);
        $isManagerViewingOther = ($user && $user->isManager() && ! $isOwner);
        $isReadonly = ($version?->isFinalized() ?? false) || $isManagerViewingOther;
        $canEdit = $user ? $user->can('update', $quote) : false;
        $canRevise = $user ? $user->can('revise', $quote) : false;
        $quoteOwnerName = $quote->user?->name ?: ($snapshot?->sales_rep_name ?: 'Sales Representative');

        $stateData = $snapshot?->full_state ?? [];
        $stateData['quoteSeq'] = $quote->quote_number;
        $stateData['salesRep'] = $quoteOwnerName;

        return response()->json([
            'id' => $quote->id,
            'quote_number' => $quote->quote_number,
            'version' => $quote->current_version,
            'status' => $quote->status,
            'is_readonly' => $isReadonly,
            'can_edit' => $canEdit,
            'can_revise' => $canRevise,
            'owner_name' => $quoteOwnerName,
            'created_at' => $quote->created_at ? $quote->created_at->format('M d, Y h:i A') : null,
            'updated_at' => $quote->updated_at ? $quote->updated_at->format('M d, Y h:i A') : null,
            'finalized_at' => $version?->finalized_at ? $version->finalized_at->format('M d, Y h:i A') : null,
            'state' => $stateData,
            'snapshot' => $snapshot,
            'view_url' => route('sales.quotes.show', $quote),
            'print_url' => route('sales.quotes.show', $quote) . '?print=1',
            'pdf_url' => route('sales.quotes.pdf', $quote),
            'can_download' => ($quote->status === 'Finalized'),
        ]);
    }

    /**
     * Update an existing draft quotation.
     */
    public function update(Request $request, Quote $quote): JsonResponse
    {
        $this->authorize('update', $quote);

        $validated = $request->validate([
            'customer' => ['nullable', 'string', 'max:255'],
            'model_description' => ['nullable', 'string'],
            'base_msrp' => ['nullable', 'numeric'],
            'base_list_price' => ['nullable', 'numeric'],
            'base_unit_price' => ['nullable', 'numeric'],
            'subtotal' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
            'quote_code' => ['nullable', 'string'],
            'enhancement_pricing_mode' => ['nullable', 'string', 'in:itemized,bundled'],
            'enhancements' => ['nullable', 'array'],
            'state' => ['required', 'array'],
        ]);

        try {
            $updated = $this->quoteService->updateDraft($quote, $validated);

            return response()->json([
                'message' => "Draft Q-{$quote->quote_number} v{$quote->current_version} saved successfully.",
                'quote' => $updated,
            ]);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Finalize a quotation.
     */
    public function finalize(Request $request, Quote $quote): JsonResponse
    {
        $this->authorize('finalize', $quote);

        $validated = $request->validate([
            'customer' => ['nullable', 'string', 'max:255'],
            'model_description' => ['nullable', 'string'],
            'base_msrp' => ['nullable', 'numeric'],
            'base_list_price' => ['nullable', 'numeric'],
            'base_unit_price' => ['nullable', 'numeric'],
            'subtotal' => ['nullable', 'numeric'],
            'total' => ['nullable', 'numeric'],
            'quote_code' => ['nullable', 'string'],
            'enhancement_pricing_mode' => ['nullable', 'string', 'in:itemized,bundled'],
            'enhancements' => ['nullable', 'array'],
            'state' => ['required', 'array'],
        ]);

        $finalized = $this->quoteService->finalizeQuote($quote, $validated);

        return response()->json([
            'message' => "Quotation Q-{$quote->quote_number} finalized successfully. Snapshot preserved.",
            'quote' => $finalized,
        ]);
    }

    /**
     * Create a new revision of a quotation.
     */
    public function revise(Request $request, Quote $quote): JsonResponse
    {
        $this->authorize('revise', $quote);

        $sourceVersion = $request->input('version');
        $revision = $this->quoteService->createRevision($quote, $request->user(), $sourceVersion ? (int) $sourceVersion : null);

        return response()->json([
            'message' => "New draft revision v{$revision->current_version} created for Q-{$quote->quote_number}.",
            'quote' => $revision,
        ]);
    }

    /**
     * Retrieve complete revision history for a quotation.
     */
    public function revisions(Quote $quote): JsonResponse
    {
        $this->authorize('view', $quote);

        $revisions = $quote->versions()
            ->with('snapshot')
            ->orderBy('version_number', 'desc')
            ->get()
            ->map(function ($v) use ($quote) {
                $snap = $v->snapshot;
                return [
                    'version_number' => $v->version_number,
                    'status' => $v->status,
                    'is_finalized' => $v->isFinalized(),
                    'is_current' => ($v->version_number == $quote->current_version),
                    'total' => (float) ($snap?->total ?? 0),
                    'customer' => $snap?->customer_company ?? '',
                    'model' => $snap?->model_code ?? '',
                    'pricing_basis' => $snap?->pricing_basis ?? '',
                    'date' => $v->finalized_at
                        ? $v->finalized_at->format('M d, Y h:i A')
                        : ($v->created_at ? $v->created_at->format('M d, Y h:i A') : ''),
                    'has_pdf' => ! empty($snap?->pdf_path),
                    'view_url' => route('sales.quotes.show', ['quote' => $quote, 'version' => $v->version_number]),
                    'print_url' => route('sales.quotes.show', ['quote' => $quote, 'version' => $v->version_number, 'print' => 1]),
                    'pdf_url' => route('sales.quotes.pdf', ['quote' => $quote, 'version' => $v->version_number]),
                ];
            });

        $user = auth()->user();
        $canRevise = $user ? $user->can('revise', $quote) : false;
        $canEdit = $user ? $user->can('update', $quote) : false;

        return response()->json([
            'quote_id' => $quote->id,
            'quote_number' => $quote->quote_number,
            'current_version' => $quote->current_version,
            'status' => $quote->status,
            'has_finalized' => $quote->hasFinalizedVersion(),
            'can_revise' => $canRevise,
            'can_edit' => $canEdit,
            'revisions' => $revisions,
        ]);
    }

    /**
     * Upload and store the generated PDF for a quotation.
     */
    public function uploadPdf(Request $request, Quote $quote): JsonResponse
    {
        $this->authorize('update', $quote);

        $request->validate([
            'pdf' => ['required', 'string'], // base64 data URI
            'version' => ['nullable', 'integer'],
        ]);

        $versionNumber = $request->input('version', $quote->current_version);
        $path = $this->quoteService->storePdf($quote, $versionNumber, $request->input('pdf'));

        return response()->json([
            'message' => 'PDF successfully stored in secure repository.',
            'path' => $path,
        ]);
    }

    /**
     * Delete a draft quotation.
     */
    public function destroy(Quote $quote): JsonResponse
    {
        $this->authorize('delete', $quote);

        $quoteNumber = $quote->quote_number;
        $quote->delete();

        return response()->json([
            'message' => "Draft Q-{$quoteNumber} deleted.",
        ]);
    }
}
