<?php

namespace App\Http\Controllers;

use App\Models\Enhancement;
use App\Models\ModelConfiguration;
use App\Models\ModelPrice;
use App\Models\PricingBasis;
use App\Models\SweeperModel;
use App\Services\CatalogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function __construct(
        protected CatalogService $catalogService
    ) {}

    /**
     * Display the Mercedes-Benz AMG-inspired ODRA Sweeper Models showcase.
     */
    public function index(Request $request): View
    {
        // Retrieve active sweeper models with configurations and current pricing
        $models = SweeperModel::active()
            ->with([
                'configurations' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
                'prices' => fn ($q) => $q->current()->latest('effective_from'),
                'enhancements' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
            ])
            ->orderBy('code')
            ->get();

        $pricingBases = PricingBasis::active()->get();

        // RBAC financial visibility determination
        $user = Auth::user();
        $isManager = $user ? $user->isManager() : false;
        $isAdmin = $user ? $user->isAdmin() : false;
        $isSales = $user ? $user->isSalesRep() : false;

        return view('catalog.index', compact('models', 'pricingBases', 'isManager', 'isAdmin', 'isSales'));
    }
}
