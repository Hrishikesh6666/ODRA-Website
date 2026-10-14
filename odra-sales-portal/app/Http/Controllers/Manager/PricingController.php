<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Enhancement;
use App\Models\FreightRule;
use App\Models\ModelPrice;
use App\Models\SweeperModel;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends Controller
{
    /**
     * Display the Master Model, Option & Freight Pricing console for Managers and Admins.
     */
    public function index(Request $request): View
    {
        $models = SweeperModel::with([
            'prices' => fn ($q) => $q->latest('effective_from'),
            'configurations' => fn ($q) => $q->orderBy('sort_order'),
        ])->orderBy('code')->get();

        $enhQuery = Enhancement::with('sweeperModel')->orderBy('sweeper_model_id')->orderBy('sort_order');
        if ($request->filled('enh_model')) {
            $enhQuery->whereHas('sweeperModel', fn ($q) => $q->where('code', $request->input('enh_model')));
        }
        if ($request->filled('enh_category')) {
            $enhQuery->where('category', $request->input('enh_category'));
        }
        if ($request->filled('enh_search')) {
            $search = $request->input('enh_search');
            $enhQuery->where('description', 'like', "%{$search}%");
        }
        $enhancements = $enhQuery->get();

        $freightQuery = FreightRule::orderBy('state')->orderBy('city');
        if ($request->filled('freight_search')) {
            $fSearch = $request->input('freight_search');
            $freightQuery->where(function ($q) use ($fSearch) {
                $q->where('state', 'like', "%{$fSearch}%")
                    ->orWhere('city', 'like', "%{$fSearch}%");
            });
        }
        $freightRules = $freightQuery->paginate(20)->withQueryString();

        $categories = Enhancement::distinct()->pluck('category')->filter()->values();

        return view('manager.pricing.index', [
            'models' => $models,
            'enhancements' => $enhancements,
            'freightRules' => $freightRules,
            'categories' => $categories,
            'selectedModel' => $request->input('enh_model', ''),
            'selectedCategory' => $request->input('enh_category', ''),
            'enhSearch' => $request->input('enh_search', ''),
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
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sw_net' => ['nullable', 'numeric', 'min:0'],
        ]);

        $msrp = (float) $validated['msrp'];
        $swList = (float) $validated['sw_list'];
        if (isset($validated['discount_percent']) && $validated['discount_percent'] !== '') {
            $discount = (float) $validated['discount_percent'];
            $swNet = round($swList * (1 - ($discount / 100)), 2);
        } elseif (! empty($validated['sw_net'])) {
            $swNet = (float) $validated['sw_net'];
            $discount = ($swList > 0 && $swList >= $swNet) ? round((1 - ($swNet / $swList)) * 100, 2) : 0;
        } else {
            $swNet = $swList;
            $discount = 0;
        }

        $currentPrice = ModelPrice::where('sweeper_model_id', $model->id)
            ->current()
            ->latest('effective_from')
            ->first();

        // Expire previous price record
        if ($currentPrice) {
            $currentPrice->update(['effective_until' => now()]);
        }

        // Insert new price record
        ModelPrice::create([
            'sweeper_model_id' => $model->id,
            'msrp' => $msrp,
            'sw_list' => $swList,
            'sw_net' => $swNet,
            'effective_from' => now(),
            'updated_by' => auth()->id(),
        ]);

        AuditService::log(
            action: 'MANAGER_PRICE_UPDATED',
            entityType: 'sweeper_model',
            entityId: $model->id,
            oldValue: $currentPrice ? ['msrp' => $currentPrice->msrp, 'sw_list' => $currentPrice->sw_list, 'sw_net' => $currentPrice->sw_net] : null,
            newValue: ['msrp' => $msrp, 'sw_list' => $swList, 'discount_percent' => $discount, 'sw_net' => $swNet],
            notes: "Updated master pricing for model {$model->code}: MSRP \${$msrp}, List \${$swList}, Discount {$discount}%, Net \${$swNet}"
        );

        return redirect()->route('manager.pricing.index')
            ->with('success', "Base equipment pricing for model {$model->code} updated successfully. All customer quotes will now reflect these prices.");
    }

    /**
     * Update enhancement / option pricing.
     */
    public function updateEnhancementPrice(Request $request, Enhancement $enhancement): RedirectResponse
    {
        $validated = $request->validate([
            'msrp' => ['required', 'numeric', 'min:0'],
            'sw_list' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sw_net' => ['nullable', 'numeric', 'min:0'],
        ]);

        $msrp = (float) $validated['msrp'];
        $swList = (float) $validated['sw_list'];
        if (isset($validated['discount_percent']) && $validated['discount_percent'] !== '') {
            $discount = (float) $validated['discount_percent'];
            $swNet = round($swList * (1 - ($discount / 100)), 2);
        } elseif (! empty($validated['sw_net'])) {
            $swNet = (float) $validated['sw_net'];
            $discount = ($swList > 0 && $swList >= $swNet) ? round((1 - ($swNet / $swList)) * 100, 2) : 0;
        } else {
            $swNet = $swList;
            $discount = 0;
        }

        $oldValue = [
            'msrp' => $enhancement->msrp,
            'sw_list' => $enhancement->sw_list,
            'discount_percent' => $enhancement->discount_percent,
            'sw_net' => $enhancement->sw_net,
        ];

        $enhancement->update([
            'msrp' => $msrp,
            'sw_list' => $swList,
            'discount_percent' => $discount,
            'sw_net' => $swNet,
        ]);

        AuditService::log(
            action: 'MANAGER_OPTION_PRICE_UPDATED',
            entityType: 'enhancement',
            entityId: $enhancement->id,
            oldValue: $oldValue,
            newValue: [
                'msrp' => $msrp,
                'sw_list' => $swList,
                'discount_percent' => $discount,
                'sw_net' => $swNet,
            ],
            notes: "Updated option pricing for '{$enhancement->description}': MSRP \${$msrp}, List \${$swList}, Discount {$discount}%, Net \${$swNet}"
        );

        return redirect()->route('manager.pricing.index')
            ->with('success', "Pricing for '{$enhancement->description}' updated successfully.");
    }

    /**
     * Update freight rule rates.
     */
    public function updateFreightPrice(Request $request, FreightRule $freight): RedirectResponse
    {
        $validated = $request->validate([
            'customer_rate' => ['required', 'numeric', 'min:0'],
            'municipality_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $old = [
            'customer_rate' => $freight->customer_rate,
            'municipality_rate' => $freight->municipality_rate,
        ];

        $freight->update([
            ...$validated,
            'updated_by' => auth()->id(),
        ]);

        AuditService::log(
            action: 'FREIGHT_RULE_UPDATED',
            entityType: 'FreightRule',
            entityId: $freight->id,
            oldValue: $old,
            newValue: $validated,
            notes: "Updated freight rates for {$freight->city}: Customer \${$validated['customer_rate']}, Municipality \${$validated['municipality_rate']}"
        );

        return redirect()->route('manager.pricing.index')
            ->with('success', "Freight delivery rates for {$freight->city} updated successfully.");
    }
}
