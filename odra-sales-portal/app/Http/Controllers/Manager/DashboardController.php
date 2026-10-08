<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Models\QuoteSnapshot;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Executive Manager Commercial Dashboard with complete financial visibility.
     */
    public function index(): View
    {
        // 1. Core KPIs
        $totalQuotesCount = Quote::withoutGlobalScopes()->count();
        $finalizedQuotesCount = Quote::withoutGlobalScopes()->where('status', 'Finalized')->count();
        $draftQuotesCount = Quote::withoutGlobalScopes()->where('status', 'Draft')->count();

        // Calculate pipeline financial sums from snapshots
        $totalQuotedValue = (float) QuoteSnapshot::sum('total');
        $finalizedValue = (float) QuoteSnapshot::whereHas('quoteVersion.quote', function ($q) {
            $q->withoutGlobalScopes()->where('status', 'Finalized');
        })->sum('total');
        $draftValue = (float) QuoteSnapshot::whereHas('quoteVersion.quote', function ($q) {
            $q->withoutGlobalScopes()->where('status', 'Draft');
        })->sum('total');
        $averageQuoteValue = $totalQuotesCount > 0 ? ($totalQuotedValue / $totalQuotesCount) : 0;

        // 2. Sales Representative Leaderboard (Volume + Value)
        $salesReps = User::whereHas('role', fn ($q) => $q->where('slug', 'sales'))
            ->where('is_active', true)
            ->with(['quotes' => fn ($q) => $q->withoutGlobalScopes()->with('latestVersion.snapshot')])
            ->get()
            ->map(function ($rep) {
                $quotes = $rep->quotes;
                $totalVal = 0;
                $finalizedVal = 0;

                foreach ($quotes as $q) {
                    $snap = $q->latestVersion?->snapshot;
                    $val = (float) ($snap?->total ?? 0);
                    $totalVal += $val;
                    if ($q->status === 'Finalized') {
                        $finalizedVal += $val;
                    }
                }

                return [
                    'id' => $rep->id,
                    'name' => $rep->name,
                    'rep_code' => $rep->rep_code,
                    'territory' => $rep->territory,
                    'total_quotes' => $quotes->count(),
                    'finalized_quotes' => $quotes->where('status', 'Finalized')->count(),
                    'total_value' => $totalVal,
                    'finalized_value' => $finalizedVal,
                ];
            })
            ->sortByDesc('total_value')
            ->values();

        // 3. Top Quoting States / Territories
        $stateBreakdown = QuoteSnapshot::select(
            DB::raw("COALESCE(rep_territory, 'Unassigned') as state_name"),
            DB::raw('COUNT(*) as quotes_count'),
            DB::raw('SUM(total) as total_amount')
        )
            ->groupBy('state_name')
            ->orderByDesc('total_amount')
            ->take(6)
            ->get();

        // 4. Monthly Trend (Last 6 Months)
        $monthlyTrend = Quote::withoutGlobalScopes()
            ->join('quote_versions', 'quotes.id', '=', 'quote_versions.quote_id')
            ->join('quote_snapshots', 'quote_versions.id', '=', 'quote_snapshots.quote_version_id')
            ->select(
                DB::raw("DATE_FORMAT(quotes.created_at, '%Y-%m') as ym"),
                DB::raw("DATE_FORMAT(quotes.created_at, '%M %Y') as month_label"),
                DB::raw('COUNT(DISTINCT quotes.id) as quote_count'),
                DB::raw('SUM(quote_snapshots.total) as total_value')
            )
            ->groupBy('ym', 'month_label')
            ->orderBy('ym', 'desc')
            ->take(6)
            ->get()
            ->reverse()
            ->values();

        // 5. Top Customers / Contractors
        $topCustomers = QuoteSnapshot::select(
            'customer_company',
            DB::raw('COUNT(*) as quotes_count'),
            DB::raw('SUM(total) as total_value'),
            DB::raw('MAX(created_at) as last_quote_at')
        )
            ->whereNotNull('customer_company')
            ->where('customer_company', '!=', '')
            ->groupBy('customer_company')
            ->orderByDesc('total_value')
            ->take(5)
            ->get();

        // 6. Recent Quotations across the entire team
        $recentQuotes = Quote::withoutGlobalScopes()
            ->with(['user', 'latestVersion.snapshot'])
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
