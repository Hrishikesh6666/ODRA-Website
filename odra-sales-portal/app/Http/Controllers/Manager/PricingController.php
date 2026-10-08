<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Enhancement;
use App\Models\ModelPrice;
use App\Models\SweeperModel;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    /**
     * Display the Master Model & Option Pricing management view for Managers.
     */
    public function index(): View
    {
        $models = SweeperModel::with(['prices' => fn ($q) => $q->latest('effective_from'), 'configurations'])->get();
        $enhancements = Enhancement::with('sweeperModel')->orderBy('category')->orderBy('sort_order')->get();

        return view('manager.pricing.index', [
            'models' => $models,
            'enhancements' => $enhancements,
        ]);
    }

    /**
     * Update base model pricing with effective timestamps.
     */
    public function updateModelPrice(Request $request, SweeperModel $model): RedirectResponse
    {
        $validated = $request->validate([
            'msrp' => ['required', 'numeric', 'min:0'],
            'sw_list' => ['required', 'numeric', 'min:0'],
            'sw_net' => ['required', 'numeric', 'min:0'],
        ]);

        $currentPrice = $model->currentPrice;

        // Expire previous price record
        if ($currentPrice) {
            $currentPrice->update(['effective_until' => now()]);
        }

        // Insert new price record
        ModelPrice::create([
            'sweeper_model_id' => $model->id,
            'msrp' => $validated['msrp'],
            'sw_list' => $validated['sw_list'],
            'sw_net' => $validated['sw_net'],
            'effective_from' => now(),
            'updated_by' => auth()->id(),
        ]);

        AuditService::log(
            action: 'MANAGER_PRICE_UPDATED',
            entityType: 'sweeper_model',
            entityId: $model->id,
            oldValue: $currentPrice ? ['msrp' => $currentPrice->msrp, 'sw_list' => $currentPrice->sw_list, 'sw_net' => $currentPrice->sw_net] : null,
            newValue: $validated,
            notes: "Manager updated pricing for model {$model->code}"
        );

        return redirect()->route('manager.pricing.index')
            ->with('success', "Base pricing for model {$model->code} has been updated successfully.");
    }

    /**
     * Update enhancement / option pricing.
     */
    public function updateEnhancementPrice(Request $request, Enhancement $enhancement): RedirectResponse
    {
        $validated = $request->validate([
            'msrp' => ['required', 'numeric', 'min:0'],
            'sw_list' => ['required', 'numeric', 'min:0'],
            'sw_net' => ['required', 'numeric', 'min:0'],
        ]);

        $oldValue = [
            'msrp' => $enhancement->msrp,
            'sw_list' => $enhancement->sw_list,
            'sw_net' => $enhancement->sw_net,
        ];

        $enhancement->update($validated);

        AuditService::log(
            action: 'MANAGER_OPTION_PRICE_UPDATED',
            entityType: 'enhancement',
            entityId: $enhancement->id,
            oldValue: $oldValue,
            newValue: $validated,
            notes: "Manager updated pricing for option: {$enhancement->description}"
        );

        return redirect()->route('manager.pricing.index')
            ->with('success', "Pricing for '{$enhancement->description}' updated successfully.");
    }
}
