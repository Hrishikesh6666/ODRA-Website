<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModelConfiguration;
use App\Models\SweeperModel;
use App\Services\AuditService;
use App\Services\QuoteTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuotationSettingsController extends Controller
{
    public function __construct(
        protected QuoteTemplateService $quoteTemplateService
    ) {
    }

    /**
     * Display the simplified, model-based Quotation Page & Pricing Basis master editor.
     */
    public function index(Request $request): View
    {
        $models = SweeperModel::with(['configurations' => fn ($q) => $q->orderBy('sort_order'), 'prices' => fn ($q) => $q->current()])
            ->orderBy('code')
            ->get();

        $selectedModelCode = strtoupper(trim($request->query('model', $models->first()?->code ?? 'MT4H')));
        if (! $models->contains('code', $selectedModelCode)) {
            $selectedModelCode = $models->first()?->code ?? 'MT4H';
        }

        $rawScope = $request->query('scope', $selectedModelCode);
        $scope = strtoupper(trim($rawScope));

        if ($scope !== 'GLOBAL' && ! $models->contains('code', $scope)) {
            $scope = $selectedModelCode;
        }

        $settings = $this->quoteTemplateService->getSettings($scope);
        $availableLogos = $this->quoteTemplateService->getAvailableLogos();

        // Build complete hierarchical configuration tree for all models
        $allConfigurationsData = [];
        foreach ($models as $m) {
            $chassisList = $m->configurations->pluck('label')->toArray();
            if (empty($chassisList)) {
                $chassisList = ["{$m->code} - Standard Commercial Truck Chassis Configuration"];
            }
            $allConfigurationsData[$m->code] = $this->quoteTemplateService->getConfigurationsDataForModel(
                $m->code,
                $chassisList,
                $m->description
            );
        }

        $modelsList = $models->map(fn ($m) => [
            'id' => $m->id,
            'code' => $m->code,
            'category' => $m->category,
            'description' => $m->description,
            'configurations' => $m->configurations->pluck('label')->toArray(),
        ])->values()->toArray();

        return view('admin.quotation_settings.index', compact(
            'settings',
            'models',
            'modelsList',
            'scope',
            'selectedModelCode',
            'availableLogos',
            'allConfigurationsData'
        ));
    }

    /**
     * Update Quotation Page sections, Pricing Bases, Dedicated Chassis Descriptions, and Standard Features.
     */
    public function update(Request $request): RedirectResponse
    {
        $rawScope = $request->input('scope', 'global');
        $scope = strtoupper(trim($rawScope));

        // If a full client-side hierarchical JSON payload was sent
        $configsJson = $request->input('configurations_json');
        if (! empty($configsJson)) {
            $decodedConfigs = json_decode($configsJson, true);
            if (is_array($decodedConfigs)) {
                $modelToUpdate = strtoupper(trim($request->input('selected_model', $scope)));
                if ($modelToUpdate !== 'GLOBAL') {
                    $this->quoteTemplateService->saveHierarchicalSettings($modelToUpdate, $decodedConfigs);
                }
            }
        }

        // Validate standard fields
        $rules = [
            'scope' => ['nullable', 'string'],
            'selected_model' => ['nullable', 'string'],
            'selected_chassis' => ['nullable', 'string'],
            'chassis_description' => ['nullable', 'string'],
            'pricing_bases' => ['nullable', 'array'],
            'pricing_bases.*.key' => ['nullable', 'string', 'max:100'],
            'pricing_bases.*.label' => ['nullable', 'string', 'max:100'],
            'pricing_bases.*.price_tier' => ['nullable', 'in:msrp,sw_net'],
            'pricing_bases.*.msrp' => ['nullable', 'numeric', 'min:0'],
            'pricing_bases.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pricing_bases.*.calculated_price' => ['nullable', 'numeric', 'min:0'],
            'pricing_bases.*.subtotal_label' => ['nullable', 'string', 'max:150'],
            'pricing_bases.*.caption' => ['nullable', 'string', 'max:500'],
            'pricing_bases.*.description_template' => ['nullable', 'string', 'max:500'],
            'pricing_bases.*.disclaimer' => ['nullable', 'string', 'max:500'],
            'pricing_bases.*.logo_key' => ['nullable', 'string', 'max:100'],
            'pricing_bases.*.use_municipality_freight' => ['nullable'],
            'pricing_bases.*.is_active' => ['nullable'],
            'legal' => ['nullable', 'array'],
            'legal.standard_features_p1' => ['nullable', 'string'],
            'legal.standard_features_p2' => ['nullable', 'string'],
            'legal.standard_features_p3' => ['nullable', 'string'],
            'legal.pm10_note' => ['nullable', 'string'],
            'legal.legal_p1' => ['nullable', 'string'],
            'legal.legal_p2' => ['nullable', 'string'],
            'legal.legal_p3' => ['nullable', 'string'],
            'legal.legal_p4' => ['nullable', 'string'],
        ];

        if ($scope === 'GLOBAL') {
            $rules['header'] = ['nullable', 'array'];
            $rules['header.company_name'] = ['nullable', 'string', 'max:255'];
            $rules['header.address_line_1'] = ['nullable', 'string', 'max:255'];
            $rules['header.address_line_2'] = ['nullable', 'string', 'max:255'];
            $rules['header.quote_title'] = ['nullable', 'string', 'max:100'];
            $rules['header.section_heading'] = ['nullable', 'string', 'max:150'];
            $rules['header.freight_row_label'] = ['nullable', 'string', 'max:150'];
            $rules['header.total_row_label'] = ['nullable', 'string', 'max:100'];
            $rules['header.max_show_discount'] = ['nullable', 'numeric', 'min:0'];
            $rules['header.default_fob'] = ['nullable', 'string', 'max:255'];
            $rules['header.exw_fob'] = ['nullable', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        // Normalize pricing bases from table inputs
        $targetModel = strtoupper(trim($request->input('selected_model', $scope)));
        $targetChassis = trim($request->input('selected_chassis', ''));
        $targetDesc = trim($request->input('chassis_description', ''));

        $bases = [];
        $rawPricingBases = $request->input('pricing_bases', []);

        if (is_array($rawPricingBases)) {
            foreach ($rawPricingBases as $b) {
                $name = trim($b['label'] ?? $b['key'] ?? '');
                if ($name === '') {
                    continue;
                }
                $key = trim($b['key'] ?? $name);
                $tier = $b['price_tier'] ?? (str_contains(strtolower($name), 'msrp') ? 'msrp' : 'sw_net');
                $isCoop = $tier === 'sw_net';
                $msrp = isset($b['msrp']) && $b['msrp'] !== '' ? (float) $b['msrp'] : 0.0;
                $disc = isset($b['discount_percent']) && $b['discount_percent'] !== '' ? (float) $b['discount_percent'] : ($isCoop ? 12.00 : 0.00);

                if (isset($b['calculated_price']) && $b['calculated_price'] !== '' && (float) $b['calculated_price'] > 0) {
                    $calcPrice = (float) $b['calculated_price'];
                } else {
                    $calcPrice = round($msrp * (1 - ($disc / 100)), 2);
                }

                $features = [];
                if (isset($b['standard_features'])) {
                    if (is_array($b['standard_features'])) {
                        $features = array_values(array_filter(array_map('trim', $b['standard_features'])));
                    } elseif (is_string($b['standard_features'])) {
                        $decoded = json_decode($b['standard_features'], true);
                        if (is_array($decoded)) {
                            $features = array_values(array_filter(array_map('trim', $decoded)));
                        }
                    }
                }

                if (empty($features)) {
                    $features = $this->quoteTemplateService->getDefaultStandardFeatures($targetModel);
                }

                $bases[] = [
                    'key' => $key,
                    'name' => $name,
                    'label' => $name,
                    'slug' => ! empty($b['slug']) ? trim($b['slug']) : Str::slug($key),
                    'price_tier' => $tier,
                    'msrp' => $msrp,
                    'discount_percent' => $disc,
                    'calculated_price' => $calcPrice,
                    'subtotal_label' => trim($b['subtotal_label'] ?? ($isCoop ? "{$name} Contract Price" : 'MSRP Subtotal')),
                    'description_template' => trim($b['description_template'] ?? $b['caption'] ?? ''),
                    'caption' => trim($b['caption'] ?? ''),
                    'disclaimer' => trim($b['disclaimer'] ?? ''),
                    'logo_key' => ! empty($b['logo_key']) && $b['logo_key'] !== 'none' ? trim($b['logo_key']) : null,
                    'is_coop' => $isCoop,
                    'use_municipality_freight' => ! empty($b['use_municipality_freight']),
                    'is_active' => ! isset($b['is_active']) || ! empty($b['is_active']),
                    'standard_features' => $features,
                ];
            }
        }

        // If targetModel and targetChassis are present, save under that configuration
        if ($targetModel !== 'GLOBAL' && $targetChassis !== '' && ! empty($bases)) {
            $this->quoteTemplateService->saveHierarchicalSettings($targetModel, [
                $targetChassis => [
                    'label' => $targetChassis,
                    'description' => $targetDesc ?: $this->quoteTemplateService->getDefaultDescription($targetModel, $targetChassis),
                    'pricing_bases' => $bases,
                    'pricing_bases_list' => $bases,
                ]
            ]);
        }

        $payload = [];
        if (! empty($bases)) {
            $payload['pricing_bases'] = $bases;
        }
        if (isset($validated['legal']) && is_array($validated['legal'])) {
            $payload['legal'] = $validated['legal'];
        }
        if ($scope === 'GLOBAL' && isset($validated['header']) && is_array($validated['header'])) {
            $payload['header'] = $validated['header'];
        }

        $old = $this->quoteTemplateService->getSettings($scope);
        $saved = $this->quoteTemplateService->saveSettings($payload, $scope);

        AuditService::log(
            action: $scope === 'GLOBAL' ? 'QUOTATION_SETTINGS_UPDATED' : 'MODEL_QUOTATION_SETTINGS_UPDATED',
            entityType: 'QuoteTemplate',
            entityId: 1,
            oldValue: $old,
            newValue: $saved,
            notes: "Updated Quotation configuration for scope: {$scope}"
        );

        $msg = $scope === 'GLOBAL'
            ? 'Quotation Page global configuration updated successfully. All customer quotes will now reflect these settings.'
            : "Quotation Page settings for model {$scope} updated successfully. All customer quotes for {$scope} will now reflect these custom features, terms, and contract rules.";

        $redirectParams = [];
        if ($request->has('scope')) {
            $redirectParams['scope'] = $request->input('scope');
        } elseif ($request->has('selected_model') || $request->has('model')) {
            $redirectParams['model'] = $targetModel !== 'GLOBAL' ? $targetModel : 'MT4H';
        }

        $redirectRoute = (auth()->check() && !auth()->user()->isAdmin()) ? 'manager.quotation-settings.index' : 'admin.quotation-settings.index';

        return redirect()
            ->route($redirectRoute, $redirectParams)
            ->with('success', $msg);
    }

    /**
     * Upload a new cooperative contract logo with live preview.
     */
    public function uploadLogo(Request $request): JsonResponse
    {
        $request->validate([
            'logo' => ['required', 'file', 'image', 'mimes:png,jpg,jpeg,svg,webp', 'max:5120'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $file = $request->file('logo');
        $rawName = trim($request->input('name') ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $name = $rawName ?: 'Custom Logo';
        $key = Str::slug($name) ?: ('logo_' . time());

        $filename = $key . '_' . time() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('images/logos'), $filename);
        $url = asset('images/logos/' . $filename);

        $this->quoteTemplateService->saveCustomLogo($key, $name, $url);

        AuditService::log(
            action: 'LOGO_UPLOADED',
            entityType: 'QuoteTemplate',
            entityId: 1,
            newValue: ['key' => $key, 'label' => $name, 'url' => $url],
            notes: "Uploaded custom logo: {$name} ({$key})"
        );

        return response()->json([
            'success' => true,
            'key' => $key,
            'label' => $name,
            'url' => $url,
        ]);
    }

    /**
     * Add a new chassis configuration under a model.
     */
    public function addConfiguration(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'model_code' => ['required', 'string'],
            'label' => ['required', 'string', 'max:500'],
            'short_label' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $model = SweeperModel::where('code', $validated['model_code'])->firstOrFail();

        $cfg = ModelConfiguration::create([
            'sweeper_model_id' => $model->id,
            'label' => trim($validated['label']),
            'short_label' => trim($validated['short_label'] ?? $validated['label']),
            'price' => (float) ($validated['price'] ?? 0.0),
            'is_active' => true,
            'sort_order' => $model->configurations()->count() + 1,
        ]);

        $desc = trim($validated['description'] ?? '');
        if ($desc === '') {
            $desc = $this->quoteTemplateService->getDefaultDescription($model->code, $cfg->label);
        }

        $defaultBases = $this->quoteTemplateService->getDefaultPricingBasesForConfig($model->code);
        $this->quoteTemplateService->saveHierarchicalSettings($model->code, [
            $cfg->label => [
                'label' => $cfg->label,
                'description' => $desc,
                'pricing_bases' => $defaultBases,
                'pricing_bases_list' => $defaultBases,
            ]
        ]);

        AuditService::log(
            action: 'CONFIGURATION_CREATED',
            entityType: 'ModelConfiguration',
            entityId: $cfg->id,
            newValue: $cfg->toArray(),
            notes: "Added chassis configuration '{$cfg->label}' to model {$model->code}"
        );

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'configuration' => $cfg,
                'description' => $desc,
                'pricing_bases' => $defaultBases,
            ]);
        }

        $redirectRoute = (auth()->check() && !auth()->user()->isAdmin()) ? 'manager.quotation-settings.index' : 'admin.quotation-settings.index';

        return redirect()
            ->route($redirectRoute, ['model' => $model->code])
            ->with('success', "New chassis configuration '{$cfg->label}' added successfully.");
    }

    /**
     * Reset Quotation Page settings to factory defaults.
     */
    public function reset(): RedirectResponse
    {
        $defaults = $this->quoteTemplateService->getDefaultSettings();
        $this->quoteTemplateService->saveSettings($defaults, 'GLOBAL');

        AuditService::log(
            action: 'QUOTATION_SETTINGS_RESET',
            entityType: 'QuoteTemplate',
            entityId: 1,
            newValue: $defaults,
            notes: 'Reset Quotation Page template sections & Pricing Basis rules to defaults'
        );

        $redirectRoute = (auth()->check() && !auth()->user()->isAdmin()) ? 'manager.quotation-settings.index' : 'admin.quotation-settings.index';

        return redirect()
            ->route($redirectRoute)
            ->with('success', 'Quotation Page global settings have been reset to factory defaults.');
    }

    /**
     * Revert a specific model to inherit from Global Defaults.
     */
    public function resetModel(Request $request, string $model): RedirectResponse
    {
        $code = strtoupper(trim($model));
        $this->quoteTemplateService->resetModelSettings($code);

        AuditService::log(
            action: 'MODEL_QUOTATION_SETTINGS_RESET',
            entityType: 'QuoteTemplate',
            entityId: 1,
            notes: "Reverted model {$code} quotation settings to inherit from Global Defaults"
        );

        $redirectRoute = (auth()->check() && !auth()->user()->isAdmin()) ? 'manager.quotation-settings.index' : 'admin.quotation-settings.index';

        return redirect()
            ->route($redirectRoute, ['scope' => $code])
            ->with('success', "Model {$code} quotation settings have been reset to inherit from Global Defaults.");
    }
}
