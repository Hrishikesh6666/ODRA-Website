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

        $salesReps = \App\Models\User::whereHas('role', function ($q) {
            $q->whereIn('slug', ['sales', 'manager']);
        })->where('is_active', true)->orderBy('name')->get()->map(function ($rep) {
            $rule = $rep->assignedFreightRule();
            return [
                'id' => $rep->id,
                'name' => $rep->name,
                'rep_code' => $rep->rep_code,
                'state' => $rep->state,
                'city' => $rep->city,
                'has_freight' => $rule !== null,
                'customer_rate' => $rule ? (float) $rule->customer_rate : null,
                'municipality_rate' => $rule ? (float) $rule->municipality_rate : null,
            ];
        });

        $defaultRep = $user->isSalesRep() ? $user->name : '';
        $userRule = $user->assignedFreightRule();
        $initialFreight = $userRule ? (float) $userRule->customer_rate : 0;
        $initialTerritory = ($user->state && $user->city) ? ($user->city . ', ' . $user->state) : '';

        return view('sales.quotes.builder', [
            'quote' => null,
            'quoteNumber' => $nextQuoteNumber,
            'version' => 1,
            'isReadonly' => false,
            'catalog' => $catalog,
            'user' => $user,
            'salesReps' => $salesReps,
            'initialState' => [
                'quoteSeq' => (string) $nextQuoteNumber,
                'salesRep' => $defaultRep,
                'date' => now()->format('Y-m-d'),
                'model' => $catalog['items'][0]['model'] ?? 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'chassis' => $catalog['chassis'][0] ?? '',
                'toCompany' => '',
                'toContact' => '',
                'toAddr1' => '',
                'toAddr2' => '',
                'po' => '',
                'acct' => '',
                'fob' => 'Transport, estimate only noted in quote',
                'baseQty' => 1,
                'freight' => $initialFreight,
                'baseShowDiscount' => 0,
                'repTerritory' => $initialTerritory,
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

        $salesReps = \App\Models\User::whereHas('role', function ($q) {
            $q->whereIn('slug', ['sales', 'manager']);
        })->where('is_active', true)->orderBy('name')->get()->map(function ($rep) {
            $rule = $rep->assignedFreightRule();
            return [
                'id' => $rep->id,
                'name' => $rep->name,
                'rep_code' => $rep->rep_code,
                'state' => $rep->state,
                'city' => $rep->city,
                'has_freight' => $rule !== null,
                'customer_rate' => $rule ? (float) $rule->customer_rate : null,
                'municipality_rate' => $rule ? (float) $rule->municipality_rate : null,
            ];
        });

        $savedState = is_array($snapshot?->full_state) ? $snapshot->full_state : [];

        $defaultState = [
            'quoteSeq' => (string) $quote->quote_number,
            'salesRep' => $snapshot?->sales_rep_name ?: $quote->user->name,
            'date' => $snapshot?->created_at ? $snapshot->created_at->format('Y-m-d') : $quote->created_at->format('Y-m-d'),
            'model' => $snapshot?->model_code ?: 'MT4H',
            'basis' => $snapshot?->pricing_basis ?: 'MSRP (Commercial)',
            'chassis' => $snapshot?->chassis_configuration ?: ($catalog['chassis'][0] ?? ''),
            'toCompany' => $snapshot?->customer_company ?: '',
            'toContact' => $snapshot?->customer_contact ?: '',
            'toAddr1' => $snapshot?->customer_address_1 ?: '',
            'toAddr2' => $snapshot?->customer_address_2 ?: '',
            'po' => $snapshot?->po_number ?: '',
            'acct' => $snapshot?->customer_account ?: '',
            'fob' => $snapshot?->fob_terms ?: 'Transport, estimate only noted in quote',
            'baseQty' => $snapshot?->base_qty ?: 1,
            'freight' => $snapshot?->freight_amount ? (float) $snapshot->freight_amount : 0,
            'baseShowDiscount' => $snapshot?->show_discount_amount ? (float) $snapshot->show_discount_amount : 0,
            'repTerritory' => $snapshot?->rep_territory ?: '',
            'enh' => !empty($snapshot?->enhancements_snapshot) && is_array($snapshot->enhancements_snapshot)
                ? $snapshot->enhancements_snapshot
                : array_fill(0, 13, ['desc' => '', 'qty' => 1]),
        ];

        $state = array_merge($defaultState, $savedState);

        // Guarantee enh is always an array of 13 rows
        if (empty($state['enh']) || !is_array($state['enh'])) {
            $state['enh'] = array_fill(0, 13, ['desc' => '', 'qty' => 1]);
        } else {
            $enhList = [];
            foreach ($state['enh'] as $row) {
                if (is_array($row)) {
                    $enhList[] = [
                        'desc' => $row['desc'] ?? '',
                        'qty' => isset($row['qty']) ? (int) $row['qty'] : 1,
                    ];
                }
            }
            while (count($enhList) < 13) {
                $enhList[] = ['desc' => '', 'qty' => 1];
            }
            $state['enh'] = array_slice($enhList, 0, 13);
        }

        return view('sales.quotes.builder', [
            'quote' => $quote,
            'quoteNumber' => $quote->quote_number,
            'version' => $quote->current_version,
            'isReadonly' => $isReadonly,
            'catalog' => $catalog,
            'user' => $user,
            'salesReps' => $salesReps,
            'initialState' => $state,
        ]);
    }
}

