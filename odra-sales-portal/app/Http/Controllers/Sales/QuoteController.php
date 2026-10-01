<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\CatalogService;
use App\Services\QuoteService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QuoteController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected QuoteService $quoteService,
        protected CatalogService $catalogService
    ) {
    }

    /**
     * Display the Sales Representative's quotes list.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Quote::class);

        $query = Quote::with(['latestVersion.snapshot', 'user'])
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

        $quotes = $query->paginate(15)->withQueryString();

        return view('sales.quotes.index', [
            'quotes' => $quotes,
            'user' => Auth::user(),
        ]);
    }

    /**
     * Open the Quote Builder to create a new quotation.
     */
    public function create(): View
    {
        $this->authorize('create', Quote::class);

        $nextQuoteNumber = $this->quoteService->generateNextQuoteNumber();
        $catalog = $this->catalogService->getCatalogPayload();
        $user = Auth::user();

        return view('sales.quotes.builder', [
            'quote' => null,
            'quoteNumber' => $nextQuoteNumber,
            'version' => 1,
            'isReadonly' => false,
            'catalog' => $catalog,
            'user' => $user,
            'initialState' => [
                'quoteSeq' => $nextQuoteNumber,
                'salesRep' => $user->name,
                'date' => now()->format('Y-m-d'),
                'model' => $catalog['items'][0]['model'] ?? 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'baseQty' => 1,
                'freight' => 0,
                'baseShowDiscount' => 0,
                'enh' => array_fill(0, 13, ['desc' => '', 'qty' => 1]),
            ],
        ]);
    }

    /**
     * Open the Quote Builder for an existing quotation.
     */
    public function show(Quote $quote): View
    {
        $this->authorize('view', $quote);

        $version = $quote->latestVersion;
        $snapshot = $version?->snapshot;
        $isReadonly = $version?->isFinalized() ?? false;
        $catalog = $this->catalogService->getCatalogPayload();
        $user = Auth::user();

        $state = $snapshot?->full_state ?? [
            'quoteSeq' => $quote->quote_number,
            'salesRep' => $quote->user->name,
            'date' => $quote->created_at->format('Y-m-d'),
            'model' => $snapshot?->model_code ?? 'MT4H',
            'basis' => $snapshot?->pricing_basis ?? 'MSRP (Commercial)',
            'chassis' => $snapshot?->chassis_configuration ?? '',
            'toCompany' => $snapshot?->customer_company ?? '',
            'toContact' => $snapshot?->customer_contact ?? '',
            'toAddr1' => $snapshot?->customer_address_1 ?? '',
            'toAddr2' => $snapshot?->customer_address_2 ?? '',
            'po' => $snapshot?->po_number ?? '',
            'acct' => $snapshot?->customer_account ?? '',
            'fob' => $snapshot?->fob_terms ?? 'Transport, estimate only noted in quote',
            'baseQty' => $snapshot?->base_qty ?? 1,
            'freight' => $snapshot?->freight_amount ?? 0,
            'baseShowDiscount' => $snapshot?->show_discount_amount ?? 0,
            'repTerritory' => $snapshot?->rep_territory ?? '',
            'enh' => $snapshot?->enhancements_snapshot ?? array_fill(0, 13, ['desc' => '', 'qty' => 1]),
        ];

        return view('sales.quotes.builder', [
            'quote' => $quote,
            'quoteNumber' => $quote->quote_number,
            'version' => $quote->current_version,
            'isReadonly' => $isReadonly,
            'catalog' => $catalog,
            'user' => $user,
            'initialState' => $state,
        ]);
    }
}
