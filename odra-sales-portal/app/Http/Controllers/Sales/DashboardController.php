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

        $totalQuotes = (clone $quotesQuery)->count();
        $draftQuotes = (clone $quotesQuery)->where('status', 'Draft')->count();
        $finalizedQuotes = (clone $quotesQuery)->where('status', 'Finalized')->count();

        $recentQuotes = (clone $quotesQuery)->latest('updated_at')->take(5)->get();

        // Territory details for current rep
        $repTerritories = Territory::where('assigned_rep_id', $user->id)->get();
        $models = SweeperModel::active()->get();

        return view('sales.dashboard', compact(
            'user',
            'totalQuotes',
            'draftQuotes',
            'finalizedQuotes',
            'recentQuotes',
            'repTerritories',
            'models'
        ));
    }
}
