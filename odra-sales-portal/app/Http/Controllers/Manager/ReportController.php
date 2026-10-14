<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\PricingBasis;
use App\Models\Quote;
use App\Models\QuoteSnapshot;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    /**
     * Display the filterable Manager Quotation Reporting Console.
     */
    public function index(Request $request): View
    {
        $query = $this->buildFilteredQuery($request);

        // Calculate aggregate metrics on the filtered set
        $cloneForStats = clone $query;
        $stats = $cloneForStats->select(
            DB::raw('COUNT(DISTINCT quotes.id) as total_count'),
            DB::raw('SUM(quote_snapshots.total) as total_value'),
            DB::raw('AVG(quote_snapshots.total) as avg_value'),
            DB::raw("SUM(CASE WHEN quotes.status = 'Finalized' THEN quote_snapshots.total ELSE 0 END) as finalized_value"),
            DB::raw("SUM(CASE WHEN quotes.status = 'Draft' THEN quote_snapshots.total ELSE 0 END) as draft_value")
        )->first();

        // Paginate results
        $quotes = $query->select('quotes.*')
            ->with(['user', 'latestVersion.snapshot'])
            ->orderBy('quotes.created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Fixed-field options & aggregations for dropdown inspection
        $repsList = User::whereHas('role', fn ($q) => $q->where('slug', 'sales'))->orderBy('name')->get();

        $statesList = QuoteSnapshot::select(
            DB::raw("COALESCE(rep_territory, 'Unassigned') as state_name"),
            DB::raw('COUNT(*) as count'),
            DB::raw('SUM(total) as sum_total')
        )
            ->whereNotNull('rep_territory')
            ->groupBy(DB::raw("COALESCE(rep_territory, 'Unassigned')"))
            ->orderBy(DB::raw("COALESCE(rep_territory, 'Unassigned')"))
            ->get();

        $pricingBases = PricingBasis::active()->get();

        return view('manager.reports.index', [
            'quotes' => $quotes,
            'stats' => $stats,
            'repsList' => $repsList,
            'statesList' => $statesList,
            'pricingBases' => $pricingBases,
            'filters' => $request->all(),
        ]);
    }

    /**
     * Stream one-click CSV / Excel export of the filtered quotation dataset.
     */
    public function export(Request $request): StreamedResponse
    {
        $query = $this->buildFilteredQuery($request)
            ->select('quotes.*')
            ->with(['user', 'latestVersion.snapshot'])
            ->orderBy('quotes.created_at', 'desc');

        $filename = 'ODRA_Quotations_Export_' . now()->format('Y-m-d_His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Column Headers
            fputcsv($handle, [
                'Quote Number',
                'Date Generated',
                'Version',
                'Status',
                'Sales Representative',
                'Rep Code',
                'Customer Company',
                'Customer Contact',
                'Destination State / Territory',
                'Sweeper Model',
                'Chassis Configuration',
                'Pricing Basis',
                'Base Unit Price ($)',
                'Freight Rate ($)',
                'Discount ($)',
                'Grand Total ($)',
            ]);

            $query->chunk(100, function ($quotes) use ($handle) {
                foreach ($quotes as $q) {
                    $snap = $q->latestVersion?->snapshot;

                    fputcsv($handle, [
                        'Q-' . $q->quote_number,
                        $q->created_at->format('Y-m-d H:i'),
                        'v' . $q->current_version,
                        $q->status,
                        $q->user->name,
                        $q->user->rep_code ?? '',
                        $snap?->customer_company ?? 'N/A',
                        $snap?->customer_contact ?? '',
                        $snap?->rep_territory ?? 'Unassigned',
                        $snap?->model_code ?? 'MT4H',
                        $snap?->chassis_configuration ?? '',
                        $snap?->pricing_basis ?? 'MSRP',
                        number_format($snap?->base_unit_price ?? 0, 2, '.', ''),
                        number_format($snap?->freight_amount ?? 0, 2, '.', ''),
                        number_format($snap?->show_discount_amount ?? 0, 2, '.', ''),
                        number_format($snap?->total ?? 0, 2, '.', ''),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Build the reusable filtered query.
     */
    protected function buildFilteredQuery(Request $request)
    {
        $query = Quote::withoutGlobalScopes()
            ->join('quote_versions', function ($join) {
                $join->on('quotes.id', '=', 'quote_versions.quote_id')
                    ->whereRaw('quote_versions.version_number = quotes.current_version');
            })
            ->join('quote_snapshots', 'quote_versions.id', '=', 'quote_snapshots.quote_version_id');

        // Filter: Sales Representative
        if ($request->filled('rep_id')) {
            $query->where('quotes.user_id', $request->input('rep_id'));
        }

        // Filter: State / Territory
        if ($request->filled('state')) {
            $query->where('quote_snapshots.rep_territory', $request->input('state'));
        }

        // Filter: Status (Draft, Finalized, Revised)
        if ($request->filled('status')) {
            $query->where('quotes.status', $request->input('status'));
        }

        // Filter: Pricing Basis
        if ($request->filled('pricing_basis')) {
            $query->where('quote_snapshots.pricing_basis', $request->input('pricing_basis'));
        }

        // Filter: Customer Company Name
        if ($request->filled('customer')) {
            $query->where('quote_snapshots.customer_company', 'like', '%' . $request->input('customer') . '%');
        }

        // Filter: Date From
        if ($request->filled('date_from')) {
            $query->whereDate('quotes.created_at', '>=', $request->input('date_from'));
        }

        // Filter: Date To
        if ($request->filled('date_to')) {
            $query->whereDate('quotes.created_at', '<=', $request->input('date_to'));
        }

        // Filter: Minimum Dollar Amount
        if ($request->filled('min_amount')) {
            $query->where('quote_snapshots.total', '>=', (float) $request->input('min_amount'));
        }

        // Filter: Maximum Dollar Amount
        if ($request->filled('max_amount')) {
            $query->where('quote_snapshots.total', '<=', (float) $request->input('max_amount'));
        }

        return $query;
    }
}
