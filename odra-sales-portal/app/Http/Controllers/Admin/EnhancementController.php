<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enhancement;
use App\Models\SweeperModel;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnhancementController extends Controller
{
    /**
     * Display a listing of enhancements and parts.
     */
    public function index(Request $request): View
    {
        $query = Enhancement::with('model')->orderBy('sweeper_model_id')->orderBy('sort_order');

        if ($request->filled('model')) {
            $query->whereHas('model', fn ($q) => $q->where('code', $request->input('model')));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        $enhancements = $query->paginate(20)->withQueryString();
        $models = SweeperModel::all();
        $categories = Enhancement::distinct()->pluck('category');

        return view('admin.enhancements.index', compact('enhancements', 'models', 'categories'));
    }

    /**
     * Store a newly created enhancement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sweeper_model_id' => ['required', 'exists:sweeper_models,id'],
            'category' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string'],
            'msrp' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sw_list' => ['nullable', 'numeric', 'min:0'],
            'sw_net' => ['nullable', 'numeric', 'min:0'],
        ]);

        $msrp = (float) $validated['msrp'];
        $discount = isset($validated['discount_percent']) && $validated['discount_percent'] !== '' ? (float) $validated['discount_percent'] : null;
        $swList = ! empty($validated['sw_list']) ? (float) $validated['sw_list'] : $msrp;

        if ($discount !== null) {
            $swNet = round($swList * (1 - ($discount / 100)), 2);
        } elseif (! empty($validated['sw_net'])) {
            $swNet = (float) $validated['sw_net'];
            $discount = ($swList > 0 && $swList >= $swNet) ? round((1 - ($swNet / $swList)) * 100, 2) : 0.00;
        } else {
            $swNet = $swList;
            $discount = 0.00;
        }

        $enh = Enhancement::create([
            'sweeper_model_id' => $validated['sweeper_model_id'],
            'category' => $validated['category'],
            'description' => $validated['description'],
            'msrp' => $msrp,
            'discount_percent' => $discount,
            'sw_list' => $swList,
            'sw_net' => $swNet,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        AuditService::log(
            action: 'ENHANCEMENT_CREATED',
            entityType: 'Enhancement',
            entityId: $enh->id,
            newValue: $enh->toArray(),
            notes: "Added enhancement {$enh->description} (MSRP \${$enh->msrp}, Discount {$discount}%, Unit Price \${$enh->sw_net})"
        );

        return redirect()->route('admin.enhancements.index')->with('success', 'Enhancement created successfully.');
    }

    /**
     * Update an enhancement.
     */
    public function update(Request $request, Enhancement $enhancement): RedirectResponse
    {
        $validated = $request->validate([
            'category' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string'],
            'msrp' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sw_list' => ['nullable', 'numeric', 'min:0'],
            'sw_net' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $msrp = (float) $validated['msrp'];
        $discount = isset($validated['discount_percent']) && $validated['discount_percent'] !== '' ? (float) $validated['discount_percent'] : null;
        $swList = ! empty($validated['sw_list']) ? (float) $validated['sw_list'] : $msrp;

        if ($discount !== null) {
            $swNet = round($swList * (1 - ($discount / 100)), 2);
        } elseif (! empty($validated['sw_net'])) {
            $swNet = (float) $validated['sw_net'];
            $discount = ($swList > 0 && $swList >= $swNet) ? round((1 - ($swNet / $swList)) * 100, 2) : 0.00;
        } else {
            $swNet = $swList;
            $discount = 0.00;
        }

        $old = $enhancement->toArray();
        $enhancement->update([
            'category' => $validated['category'],
            'description' => $validated['description'],
            'msrp' => $msrp,
            'discount_percent' => $discount,
            'sw_list' => $swList,
            'sw_net' => $swNet,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditService::log(
            action: 'ENHANCEMENT_UPDATED',
            entityType: 'Enhancement',
            entityId: $enhancement->id,
            oldValue: $old,
            newValue: $enhancement->toArray(),
            notes: "Updated enhancement {$enhancement->description}"
        );

        return redirect()->route('admin.enhancements.index')->with('success', 'Enhancement updated successfully.');
    }

    /**
     * Delete / remove an enhancement.
     */
    public function destroy(Enhancement $enhancement): RedirectResponse
    {
        $desc = $enhancement->description;
        $enhancement->delete();

        AuditService::log(
            action: 'ENHANCEMENT_DELETED',
            entityType: 'Enhancement',
            entityId: $enhancement->id,
            notes: "Deleted enhancement {$desc}"
        );

        return redirect()->route('admin.enhancements.index')->with('success', "Enhancement '{$desc}' removed successfully.");
    }

    /**
     * Toggle enhancement active status.
     */
    public function toggle(Enhancement $enhancement): RedirectResponse
    {
        $enhancement->update(['is_active' => ! $enhancement->is_active]);

        AuditService::log(
            action: 'ENHANCEMENT_TOGGLED',
            entityType: 'Enhancement',
            entityId: $enhancement->id,
            notes: "Enhancement {$enhancement->description} " . ($enhancement->is_active ? 'activated' : 'deactivated')
        );

        return redirect()->route('admin.enhancements.index')->with('success', 'Enhancement status changed.');
    }
}
