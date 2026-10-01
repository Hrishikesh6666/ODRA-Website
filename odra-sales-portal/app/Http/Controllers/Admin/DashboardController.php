<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Enhancement;
use App\Models\FreightRule;
use App\Models\Quote;
use App\Models\SweeperModel;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin HQ overview dashboard.
     */
    public function index(): View
    {
        $activeModelsCount = SweeperModel::active()->count();
        $enhancementsCount = Enhancement::active()->count();
        $salesRepsCount = User::whereHas('role', fn ($q) => $q->where('slug', 'sales'))->where('is_active', true)->count();
        $totalQuotesCount = Quote::withoutGlobalScopes()->count();
        $recentAuditLogs = AuditLog::with('user')->latest('created_at')->take(8)->get();
        $recentQuotes = Quote::withoutGlobalScopes()->with(['user', 'latestVersion.snapshot'])->latest('updated_at')->take(6)->get();

        return view('admin.dashboard', [
            'activeModelsCount' => $activeModelsCount,
            'enhancementsCount' => $enhancementsCount,
            'salesRepsCount' => $salesRepsCount,
            'totalQuotesCount' => $totalQuotesCount,
            'recentAuditLogs' => $recentAuditLogs,
            'recentQuotes' => $recentQuotes,
            'user' => auth()->user(),
        ]);
    }
}
