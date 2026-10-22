<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\User;
use App\Services\QuoteService;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected QuoteService $quoteService)
    {
    }

    /**
     * Display the Executive Manager Commercial Dashboard with complete financial visibility.
     */
    public function index(): View
    {
        // 1. Core KPIs calculated with revision deduplication
        $summary = $this->quoteService->getQuotationSummary();
        $totalQuotesCount = $summary['totalQuotesCount'];
        $finalizedQuotesCount = $summary['finalizedQuotesCount'];
        $finalizedValue = $summary['finalizedValue'];
        $draftQuotesCount = $summary['draftQuotesCount'];
        $draftValue = $summary['draftValue'];
        $averageQuoteValue = $summary['averageQuoteValue'];
        $totalQuotedValue = $finalizedValue + $draftValue;

        // 2. Sales Representative Leaderboard (Volume + Value)
        $salesReps = User::whereHas('role', fn ($q) => $q->where('slug', 'sales'))
            ->where('is_active', true)
            ->with(['quotes' => fn ($q) => $q->withoutGlobalScopes()->with(['latestFinalizedVersion.snapshot', 'latestVersion.snapshot', 'versions'])])
            ->get()
            ->map(function ($rep) {
                $quotes = $rep->quotes;
                $totalVal = 0;
                $finalizedVal = 0;
                $finalizedQuotesCount = 0;

                foreach ($quotes as $q) {
                    $finalizedVer = $q->latestFinalizedVersion;
                    if ($finalizedVer) {
                        $finalizedQuotesCount++;
                        $finalizedVal += (float) ($finalizedVer->snapshot?->total ?? 0);
                    }

                    // Display version for total pipeline tracking
                    $dispVer = $q->displayVersion();
                    $totalVal += (float) ($dispVer?->snapshot?->total ?? 0);
                }

                return [
                    'id' => $rep->id,
                    'name' => $rep->name,
                    'rep_code' => $rep->rep_code,
                    'territory' => $rep->territory,
                    'total_quotes' => $quotes->count(),
                    'finalized_quotes' => $finalizedQuotesCount,
                    'total_value' => $totalVal,
                    'finalized_value' => $finalizedVal,
                ];
            })
            ->sortByDesc('finalized_value')
            ->values();

        // 3. Top Quoting States / Territories (deduplicated by quotation group)
        $stateBreakdown = $this->quoteService->latestFinalizedVersionsQuery()
            ->select(
                DB::raw("COALESCE(quote_snapshots.rep_territory, 'Unassigned') as state_name"),
                DB::raw('COUNT(DISTINCT quotes.id) as quotes_count'),
                DB::raw('SUM(quote_snapshots.total) as total_amount')
            )
            ->groupBy(DB::raw("COALESCE(quote_snapshots.rep_territory, 'Unassigned')"))
            ->orderByDesc('total_amount')
            ->take(6)
            ->get();

        // 4. Monthly Trend (Last 6 Months - Cross-DB compatible with pgsql, mysql, sqlite)
        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            $ymExpr = "TO_CHAR(quotes.created_at, 'YYYY-MM')";
            $labelExpr = "TO_CHAR(quotes.created_at, 'Mon YYYY')";
        } elseif ($driver === 'sqlite') {
            $ymExpr = "strftime('%Y-%m', quotes.created_at)";
            $labelExpr = "strftime('%m/%Y', quotes.created_at)";
        } else {
            $ymExpr = "DATE_FORMAT(quotes.created_at, '%Y-%m')";
            $labelExpr = "DATE_FORMAT(quotes.created_at, '%M %Y')";
        }

        $monthlyTrend = $this->quoteService->latestFinalizedVersionsQuery()
            ->select(
                DB::raw("{$ymExpr} as ym"),
                DB::raw("{$labelExpr} as month_label"),
                DB::raw('COUNT(DISTINCT quotes.id) as quote_count'),
                DB::raw('SUM(quote_snapshots.total) as total_value')
            )
            ->groupBy(DB::raw($ymExpr), DB::raw($labelExpr))
            ->orderBy(DB::raw($ymExpr), 'desc')
            ->take(6)
            ->get()
            ->reverse()
            ->values();

        // 5. Top Customers / Contractors (deduplicated by quotation group)
        $topCustomers = $this->quoteService->latestFinalizedVersionsQuery()
            ->select(
                'quote_snapshots.customer_company',
                DB::raw('COUNT(DISTINCT quotes.id) as quotes_count'),
                DB::raw('SUM(quote_snapshots.total) as total_value'),
                DB::raw('MAX(quotes.created_at) as last_quote_at')
            )
            ->whereNotNull('quote_snapshots.customer_company')
            ->where('quote_snapshots.customer_company', '!=', '')
            ->groupBy('quote_snapshots.customer_company')
            ->orderByDesc('total_value')
            ->take(5)
            ->get();

        // 6. Recent Quotations across the entire team (displaying default revision per quotation group)
        $recentQuotes = Quote::withoutGlobalScopes()
            ->with(['user', 'latestFinalizedVersion.snapshot', 'latestVersion.snapshot', 'versions.snapshot'])
            ->latest('updated_at')
            ->take(8)
            ->get();

        return view('manager.dashboard', [
            'totalQuotesCount' => $totalQuotesCount,
            'finalizedQuotesCount' => $finalizedQuotesCount,
            'draftQuotesCount' => $draftQuotesCount,
            'totalQuotedValue' => $totalQuotedValue,
            'finalizedValue' => $finalizedValue,
            'draftValue' => $draftValue,
            'averageQuoteValue' => $averageQuoteValue,
            'salesReps' => $salesReps,
            'stateBreakdown' => $stateBreakdown,
            'monthlyTrend' => $monthlyTrend,
            'topCustomers' => $topCustomers,
            'recentQuotes' => $recentQuotes,
            'user' => auth()->user(),
        ]);
    }
}
