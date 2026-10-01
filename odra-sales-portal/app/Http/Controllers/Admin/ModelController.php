<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelConfiguration;
use App\Models\ModelPrice;
use App\Models\SweeperModel;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModelController extends Controller
{
    /**
     * Display a listing of sweeper models and current pricing.
     */
    public function index(): View
    {
        $models = SweeperModel::with(['configurations', 'prices' => fn ($q) => $q->current()])
            ->orderBy('code')
            ->get();

        return view('admin.models.index', compact('models'));
    }

    /**
     * Show the form for creating a new model.
     */
    public function create(): View
    {
        return view('admin.models.create');
    }

    /**
     * Store a newly created model and initial prices.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:24', 'unique:sweeper_models,code'],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'msrp' => ['required', 'numeric', 'min:0'],
            'sw_list' => ['required', 'numeric', 'min:0'],
            'sw_net' => ['required', 'numeric', 'min:0'],
            'configurations' => ['nullable', 'array'],
            'configurations.*.label' => ['required', 'string'],
            'configurations.*.short_label' => ['nullable', 'string'],
        ]);

        $model = SweeperModel::create([
            'code' => strtoupper($validated['code']),
            'description' => $validated['description'],
            'category' => $validated['category'],
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        // Create initial price
        ModelPrice::create([
            'sweeper_model_id' => $model->id,
            'msrp' => $validated['msrp'],
            'sw_list' => $validated['sw_list'],
            'sw_net' => $validated['sw_net'],
            'effective_from' => now(),
            'updated_by' => auth()->id(),
        ]);

        // Create configurations
        if (! empty($validated['configurations'])) {
            $order = 0;
            foreach ($validated['configurations'] as $cfg) {
                if (empty($cfg['label'])) continue;
                ModelConfiguration::create([
                    'sweeper_model_id' => $model->id,
                    'label' => $cfg['label'],
                    'short_label' => $cfg['short_label'] ?? null,
                    'sort_order' => $order++,
                    'is_active' => true,
                ]);
            }
        }

        AuditService::log(
            action: 'MODEL_CREATED',
            entityType: 'SweeperModel',
            entityId: $model->id,
            newValue: $validated,
            notes: "Created model {$model->code} with base MSRP \${$validated['msrp']}"
        );

        return redirect()->route('admin.models.index')->with('success', "Model {$model->code} created successfully.");
    }

    /**
     * Show the form for editing the model and updating prices.
     */
    public function edit(SweeperModel $model): View
    {
        $currentPrice = ModelPrice::where('sweeper_model_id', $model->id)
            ->current()
            ->latest('effective_from')
            ->first();

        $model->load('configurations');

        return view('admin.models.edit', compact('model', 'currentPrice'));
    }

    /**
     * Update model details and prices.
     */
    public function update(Request $request, SweeperModel $model): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'msrp' => ['required', 'numeric', 'min:0'],
            'sw_list' => ['required', 'numeric', 'min:0'],
            'sw_net' => ['required', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'configurations' => ['nullable', 'array'],
        ]);

        $oldModel = $model->toArray();

        $model->update([
            'description' => $validated['description'],
            'category' => $validated['category'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Check if prices changed
        $currentPrice = ModelPrice::where('sweeper_model_id', $model->id)->current()->first();
        if (! $currentPrice || $currentPrice->msrp != $validated['msrp'] || $currentPrice->sw_list != $validated['sw_list'] || $currentPrice->sw_net != $validated['sw_net']) {
            if ($currentPrice) {
                $currentPrice->update(['effective_until' => now()]);
            }

            ModelPrice::create([
                'sweeper_model_id' => $model->id,
                'msrp' => $validated['msrp'],
                'sw_list' => $validated['sw_list'],
                'sw_net' => $validated['sw_net'],
                'effective_from' => now(),
                'updated_by' => auth()->id(),
            ]);
        }

        AuditService::log(
            action: 'MODEL_UPDATED',
            entityType: 'SweeperModel',
            entityId: $model->id,
            oldValue: $oldModel,
            newValue: $validated,
            notes: "Updated model {$model->code} details & master pricing."
        );

        return redirect()->route('admin.models.index')->with('success', "Model {$model->code} updated successfully.");
    }

    /**
     * Toggle active status.
     */
    public function toggle(SweeperModel $model): RedirectResponse
    {
        $model->update(['is_active' => ! $model->is_active]);

        AuditService::log(
            action: 'MODEL_TOGGLED',
            entityType: 'SweeperModel',
            entityId: $model->id,
            notes: "Model {$model->code} " . ($model->is_active ? 'activated' : 'deactivated')
        );

        return redirect()->route('admin.models.index')->with('success', "Model {$model->code} status changed.");
    }
}
