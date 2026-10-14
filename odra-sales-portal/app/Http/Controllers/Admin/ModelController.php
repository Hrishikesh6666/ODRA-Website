<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelConfiguration;
use App\Models\ModelPrice;
use App\Models\SweeperModel;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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
            'code' => ['required', 'string', 'max:24', Rule::unique('sweeper_models', 'code')->whereNull('deleted_at')],
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'msrp' => ['required', 'numeric', 'min:0'],
            'sw_list' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sw_net' => ['nullable', 'numeric', 'min:0'],
            'configurations' => ['nullable', 'array'],
            'configurations.*.label' => ['nullable', 'string'],
            'configurations.*.short_label' => ['nullable', 'string'],
            'configurations.*.price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $code = strtoupper(trim($validated['code']));
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

        $model = SweeperModel::create([
            'code' => $code,
            'description' => $validated['description'],
            'category' => $validated['category'],
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        // Create initial price
        ModelPrice::create([
            'sweeper_model_id' => $model->id,
            'msrp' => $msrp,
            'sw_list' => $swList,
            'sw_net' => $swNet,
            'effective_from' => now(),
            'updated_by' => auth()->id(),
        ]);

        // Create configurations (or default configuration if none provided)
        $order = 0;
        if (! empty($validated['configurations'])) {
            foreach ($validated['configurations'] as $cfg) {
                $label = trim($cfg['label'] ?? '');
                if ($label === '') {
                    continue;
                }
                $shortLabel = trim($cfg['short_label'] ?? '');
                ModelConfiguration::create([
                    'sweeper_model_id' => $model->id,
                    'label' => $label,
                    'short_label' => $shortLabel !== '' ? $shortLabel : $label,
                    'price' => isset($cfg['price']) ? (float) $cfg['price'] : 0.00,
                    'sort_order' => $order++,
                    'is_active' => true,
                ]);
            }
        }

        if ($order === 0) {
            ModelConfiguration::create([
                'sweeper_model_id' => $model->id,
                'label' => "{$code} - Standard Commercial Truck Chassis Configuration",
                'short_label' => "{$code} Standard Configuration",
                'price' => 0.00,
                'sort_order' => 0,
                'is_active' => true,
            ]);
        }

        AuditService::log(
            action: 'MODEL_CREATED',
            entityType: 'SweeperModel',
            entityId: $model->id,
            newValue: $validated,
            notes: "Created model {$model->code} with base MSRP \${$msrp}, List \${$swList}, Discount {$discount}%, Net \${$swNet}"
        );

        return redirect()->route('admin.models.index')->with('success', "Model {$model->code} created successfully and is now live in the Quote Builder.");
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

        $model->load(['configurations' => fn ($q) => $q->orderBy('sort_order')]);

        return view('admin.models.edit', compact('model', 'currentPrice'));
    }

    /**
     * Update model details, chassis configurations, and prices.
     */
    public function update(Request $request, SweeperModel $model): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string'],
            'category' => ['required', 'string', 'max:100'],
            'msrp' => ['required', 'numeric', 'min:0'],
            'sw_list' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'sw_net' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
            'configurations' => ['nullable', 'array'],
            'configurations.*.label' => ['nullable', 'string'],
            'configurations.*.short_label' => ['nullable', 'string'],
            'configurations.*.price' => ['nullable', 'numeric', 'min:0'],
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

        $oldModel = $model->toArray();

        $model->update([
            'description' => $validated['description'],
            'category' => $validated['category'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Check if prices changed
        $currentPrice = ModelPrice::where('sweeper_model_id', $model->id)->current()->first();
        if (! $currentPrice || $currentPrice->msrp != $msrp || $currentPrice->sw_list != $swList || $currentPrice->sw_net != $swNet) {
            if ($currentPrice) {
                $currentPrice->update(['effective_until' => now()]);
            }

            ModelPrice::create([
                'sweeper_model_id' => $model->id,
                'msrp' => $msrp,
                'sw_list' => $swList,
                'sw_net' => $swNet,
                'effective_from' => now(),
                'updated_by' => auth()->id(),
            ]);
        }

        // Sync chassis configurations if submitted
        if ($request->has('configurations')) {
            $submittedConfigs = $validated['configurations'] ?? [];
            $validConfigs = [];
            foreach ($submittedConfigs as $cfg) {
                $label = trim($cfg['label'] ?? '');
                if ($label !== '') {
                    $shortLabel = trim($cfg['short_label'] ?? '');
                    $validConfigs[] = [
                        'label' => $label,
                        'short_label' => $shortLabel !== '' ? $shortLabel : $label,
                        'price' => isset($cfg['price']) ? (float) $cfg['price'] : 0.00,
                    ];
                }
            }

            if (! empty($validConfigs)) {
                $model->configurations()->delete();
                foreach ($validConfigs as $idx => $cfg) {
                    ModelConfiguration::create([
                        'sweeper_model_id' => $model->id,
                        'label' => $cfg['label'],
                        'short_label' => $cfg['short_label'],
                        'price' => $cfg['price'] ?? 0.00,
                        'sort_order' => $idx,
                        'is_active' => true,
                    ]);
                }
            }
        }

        AuditService::log(
            action: 'MODEL_UPDATED',
            entityType: 'SweeperModel',
            entityId: $model->id,
            oldValue: $oldModel,
            newValue: $validated,
            notes: "Updated model {$model->code} details, chassis configurations & master pricing."
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
