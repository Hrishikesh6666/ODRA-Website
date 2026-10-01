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
            'sw_list' => ['required', 'numeric', 'min:0'],
            'sw_net' => ['required', 'numeric', 'min:0'],
        ]);

        $enh = Enhancement::create([
            ...$validated,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        AuditService::log(
            action: 'ENHANCEMENT_CREATED',
            entityType: 'Enhancement',
            entityId: $enh->id,
            newValue: $validated,
            notes: "Added enhancement {$enh->description} (\${$enh->msrp})"
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
            'sw_list' => ['required', 'numeric', 'min:0'],
            'sw_net' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $old = $enhancement->toArray();
        $enhancement->update([
            ...$validated,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditService::log(
            action: 'ENHANCEMENT_UPDATED',
            entityType: 'Enhancement',
            entityId: $enhancement->id,
            oldValue: $old,
            newValue: $validated,
            notes: "Updated enhancement {$enhancement->description}"
        );

        return redirect()->route('admin.enhancements.index')->with('success', 'Enhancement updated successfully.');
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
