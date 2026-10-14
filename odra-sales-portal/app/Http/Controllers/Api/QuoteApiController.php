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

        return response()->json([
            'id' => $quote->id,
            'quote_number' => $quote->quote_number,
            'version' => $quote->current_version,
            'status' => $quote->status,
            'is_readonly' => $version?->isFinalized() ?? false,
            'owner_name' => $quote->user->name,
            'state' => $snapshot?->full_state ?? [
                'quoteSeq' => $quote->quote_number,
                'salesRep' => $quote->user->name,
            ],
            'snapshot' => $snapshot,
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

        $revision = $this->quoteService->createRevision($quote, $request->user());

        return response()->json([
            'message' => "New draft revision v{$revision->current_version} created for Q-{$quote->quote_number}.",
            'quote' => $revision,
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
