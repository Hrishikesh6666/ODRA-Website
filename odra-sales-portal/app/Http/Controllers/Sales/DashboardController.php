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

        $allQuotes = (clone $quotesQuery)->latest('updated_at')->take(20)->get();

        $totalQuotes = (clone $quotesQuery)->count();
        $draftQuotes = (clone $quotesQuery)->where('status', 'Draft')->count();
        $finalizedQuotes = (clone $quotesQuery)->where('status', 'Finalized')->count();

        $totalQuotedAmount = $allQuotes->sum(function ($q) {
            return (float) ($q->latestVersion?->snapshot?->total ?? 0);
        });

        $conversionRate = $totalQuotes > 0 ? round(($finalizedQuotes / $totalQuotes) * 100) : 0;

        $mt4hCount = $allQuotes->filter(fn ($q) => ($q->latestVersion?->snapshot?->model_code ?? '') === 'MT4H')->count();
        $ms5hCount = $allQuotes->filter(fn ($q) => ($q->latestVersion?->snapshot?->model_code ?? '') === 'MS5H')->count();

        $recentQuotes = $allQuotes->take(10);

        // Territory details for current rep
        $repTerritories = Territory::where('assigned_rep_id', $user->id)->get();
        $models = SweeperModel::active()->with('configurations')->get();

        return view('sales.dashboard', compact(
            'user',
            'totalQuotes',
            'draftQuotes',
            'finalizedQuotes',
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
