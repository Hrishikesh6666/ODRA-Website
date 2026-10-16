<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\FreightRule;
use App\Models\Quote;
use App\Models\SweeperModel;
use App\Models\Territory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Sales Representative dashboard.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // Representative's personal quote statistics (using global scope 'owned')
        $quotesQuery = Quote::with('latestVersion.snapshot');

        $allUserQuotes = (clone $quotesQuery)->latest('updated_at')->get();

        $draftQuotesList = $allUserQuotes->where('status', 'Draft');
        $draftQuotesCount = $draftQuotesList->count();
        $draftQuotedValue = (float) $draftQuotesList->sum(fn ($q) => (float) ($q->latestVersion?->snapshot?->total ?? 0));

        $finalizedQuotesList = $allUserQuotes->where('status', 'Finalized');
        $finalizedQuotesCount = $finalizedQuotesList->count();
        $finalizedQuotedValue = (float) $finalizedQuotesList->sum(fn ($q) => (float) ($q->latestVersion?->snapshot?->total ?? 0));

        $totalQuotedValue = $draftQuotedValue + $finalizedQuotedValue;
        $totalQuotedCount = $draftQuotesCount + $finalizedQuotesCount;

        $totalQuotes = $allUserQuotes->count();
        $draftQuotes = $draftQuotesCount;
        $finalizedQuotes = $finalizedQuotesCount;
        $totalQuotedAmount = $totalQuotedValue;

        $conversionRate = $totalQuotedCount > 0 ? round(($finalizedQuotesCount / $totalQuotedCount) * 100) : 0;

        $mt4hCount = $allUserQuotes->filter(fn ($q) => ($q->latestVersion?->snapshot?->model_code ?? '') === 'MT4H')->count();
        $ms5hCount = $allUserQuotes->filter(fn ($q) => ($q->latestVersion?->snapshot?->model_code ?? '') === 'MS5H')->count();

        $recentQuotes = $allUserQuotes->take(15);

        // Territory details for current rep
        $repTerritories = Territory::where('assigned_rep_id', $user->id)->get();
        $models = SweeperModel::active()->with('configurations')->get();

        return view('sales.dashboard', compact(
            'user',
            'totalQuotes',
            'draftQuotes',
            'finalizedQuotes',
            'draftQuotesCount',
            'finalizedQuotesCount',
            'totalQuotedCount',
            'draftQuotedValue',
            'finalizedQuotedValue',
            'totalQuotedValue',
            'totalQuotedAmount',
            'conversionRate',
            'mt4hCount',
            'ms5hCount',
            'recentQuotes',
            'repTerritories',
            'models'
        ));
    }
}
