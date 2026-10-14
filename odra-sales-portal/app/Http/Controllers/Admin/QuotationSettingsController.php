<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SweeperModel;
use App\Services\AuditService;
use App\Services\QuoteTemplateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotationSettingsController extends Controller
{
    public function __construct(
        protected QuoteTemplateService $quoteTemplateService
    ) {
    }

    /**
     * Display the Quotation Page & Pricing Basis master editor (globally or per model).
     */
    public function index(Request $request): View
    {
        $models = SweeperModel::with(['configurations', 'prices' => fn ($q) => $q->current()])
            ->orderBy('code')
            ->get();

        $rawScope = $request->query('scope', 'global');
        $scope = strtoupper(trim($rawScope));

        if ($scope !== 'GLOBAL' && ! $models->contains('code', $scope)) {
            $scope = 'GLOBAL';
        }

        $settings = $this->quoteTemplateService->getSettings($scope);

        return view('admin.quotation_settings.index', compact('settings', 'models', 'scope'));
    }

    /**
     * Update Quotation Page sections and Pricing Bases (globally or for a specific model).
     */
    public function update(Request $request): RedirectResponse
    {
        $rawScope = $request->input('scope', 'global');
        $scope = strtoupper(trim($rawScope));

        $rules = [
            'scope' => ['nullable', 'string'],
            'pricing_bases' => ['required', 'array', 'min:1'],
            'pricing_bases.*.key' => ['required', 'string', 'max:100'],
            'pricing_bases.*.label' => ['required', 'string', 'max:100'],
            'pricing_bases.*.slug' => ['nullable', 'string', 'max:100'],
            'pricing_bases.*.price_tier' => ['required', 'in:msrp,sw_net'],
            'pricing_bases.*.discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'pricing_bases.*.subtotal_label' => ['required', 'string', 'max:120'],
            'pricing_bases.*.description_template' => ['nullable', 'string', 'max:500'],
            'pricing_bases.*.disclaimer' => ['nullable', 'string', 'max:500'],
            'pricing_bases.*.logo_key' => ['nullable', 'string', 'max:50'],
            'pricing_bases.*.caption' => ['nullable', 'string', 'max:500'],
            'pricing_bases.*.use_municipality_freight' => ['nullable'],
            'pricing_bases.*.is_active' => ['nullable'],
            'legal' => ['required', 'array'],
            'legal.standard_features_p1' => ['required', 'string'],
            'legal.standard_features_p2' => ['required', 'string'],
            'legal.standard_features_p3' => ['required', 'string'],
            'legal.pm10_note' => ['nullable', 'string'],
            'legal.legal_p1' => ['required', 'string'],
            'legal.legal_p2' => ['required', 'string'],
            'legal.legal_p3' => ['required', 'string'],
            'legal.legal_p4' => ['required', 'string'],
        ];

        if ($scope === 'GLOBAL') {
            $rules['header'] = ['required', 'array'];
            $rules['header.company_name'] = ['required', 'string', 'max:255'];
            $rules['header.address_line_1'] = ['required', 'string', 'max:255'];
            $rules['header.address_line_2'] = ['required', 'string', 'max:255'];
            $rules['header.quote_title'] = ['required', 'string', 'max:100'];
            $rules['header.section_heading'] = ['required', 'string', 'max:150'];
            $rules['header.freight_row_label'] = ['required', 'string', 'max:150'];
            $rules['header.total_row_label'] = ['required', 'string', 'max:100'];
            $rules['header.max_show_discount'] = ['required', 'numeric', 'min:0'];
            $rules['header.default_fob'] = ['required', 'string', 'max:255'];
            $rules['header.exw_fob'] = ['required', 'string', 'max:255'];
        }

        $validated = $request->validate($rules);

        // Normalize boolean flags on pricing_bases
        $bases = [];
        foreach ($validated['pricing_bases'] as $b) {
            $isCoop = ($b['price_tier'] ?? 'msrp') === 'sw_net';
            $disc = isset($b['discount_percent']) && $b['discount_percent'] !== ''
                ? (float) $b['discount_percent']
                : ($isCoop ? 12.00 : 0.00);

            $bases[] = [
                'key' => $b['key'],
                'label' => $b['label'],
                'slug' => $b['slug'] ?? '',
                'price_tier' => $b['price_tier'],
                'discount_percent' => $disc,
                'subtotal_label' => $b['subtotal_label'],
                'description_template' => $b['description_template'] ?? $b['caption'] ?? '',
                'disclaimer' => $b['disclaimer'] ?? '',
                'logo_key' => $b['logo_key'] ?? null,
                'caption' => $b['caption'] ?? '',
                'is_coop' => $isCoop,
                'use_municipality_freight' => ! empty($b['use_municipality_freight']),
                'is_active' => ! isset($b['is_active']) || ! empty($b['is_active']),
            ];
        }

        $payload = [
            'pricing_bases' => $bases,
            'legal' => $validated['legal'],
        ];

        if ($scope === 'GLOBAL') {
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
            notes: "Updated Quotation Page sections & Pricing Basis rules for scope: {$scope}"
        );

        $msg = $scope === 'GLOBAL'
            ? 'Quotation Page global default template updated successfully. All customer quotes will now reflect these settings.'
            : "Quotation Page settings for model {$scope} updated successfully. All customer quotes for {$scope} will now reflect these custom features, terms, and contract rules.";

        return redirect()
            ->route('admin.quotation-settings.index', $scope !== 'GLOBAL' ? ['scope' => $scope] : [])
            ->with('success', $msg);
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

        return redirect()
            ->route('admin.quotation-settings.index')
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

        return redirect()
            ->route('admin.quotation-settings.index', ['scope' => $code])
            ->with('success', "Model {$code} quotation settings have been reset to inherit from Global Defaults.");
    }
}
