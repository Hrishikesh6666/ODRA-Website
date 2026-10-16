<?php

namespace App\Services;

use App\Models\Enhancement;
use App\Models\FreightRule;
use App\Models\ModelPrice;
use App\Models\PricingBasis;
use App\Models\SweeperModel;
use App\Models\Territory;
use App\Models\User;

class CatalogService
{
    public function __construct(
        protected ?QuoteTemplateService $quoteTemplateService = null
    ) {
        $this->quoteTemplateService = $quoteTemplateService ?? new QuoteTemplateService();
    }

    /**
     * Get the complete catalog payload required by the Quote Builder.
     */
    public function getCatalogPayload(): array
    {
        $activeModels = SweeperModel::active()->with(['configurations' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        }])->orderBy('id')->get();

        $modelsList = [];
        $items = [];
        $chassis = [];
        $chassisByModel = [];
        $chassisShort = [];
        $chassisPrices = [];

        foreach ($activeModels as $model) {
            // Find current pricing
            $price = ModelPrice::where('sweeper_model_id', $model->id)
                ->current()
                ->latest('effective_from')
                ->first();

            if (! $price) {
                $price = ModelPrice::where('sweeper_model_id', $model->id)
                    ->latest('id')
                    ->first();
            }

            $msrp = $price ? (float) $price->msrp : 0;
            $swList = $price ? (float) $price->sw_list : 0;
            $swNet = $price ? (float) $price->sw_net : 0;

            // Base unit item
            $items[] = [
                'model' => $model->code,
                'cat' => 'BASE UNIT',
                'desc' => $model->description,
                'isBase' => true,
                'msrp' => $msrp,
                'swList' => $swList,
                'swNet' => $swNet,
            ];

            // Chassis configurations for this model
            $modelConfigs = [];
            foreach ($model->configurations as $cfg) {
                $chassis[] = $cfg->label;
                $modelConfigs[] = $cfg->label;
                $chassisPrices[$cfg->label] = (float) $cfg->price;
                if ($cfg->short_label) {
                    $chassisShort[$cfg->label] = $cfg->short_label;
                }
            }

            // Guarantee at least one default chassis config if none exists in DB
            if (empty($modelConfigs)) {
                $defaultLabel = "{$model->code} - Standard Commercial Truck Chassis Configuration";
                $defaultShort = "{$model->code} Standard Configuration";
                $chassis[] = $defaultLabel;
                $modelConfigs[] = $defaultLabel;
                $chassisShort[$defaultLabel] = $defaultShort;
                $chassisPrices[$defaultLabel] = 0.00;
            }

            $chassisByModel[$model->code] = $modelConfigs;

            $modelsList[] = [
                'id' => $model->id,
                'code' => $model->code,
                'category' => $model->category,
                'description' => $model->description,
                'msrp' => $msrp,
                'swList' => $swList,
                'swNet' => $swNet,
                'configurations' => $modelConfigs,
            ];

            // Enhancements
            $enhancements = Enhancement::where('sweeper_model_id', $model->id)
                ->active()
                ->orderBy('sort_order')
                ->get();

            foreach ($enhancements as $enh) {
                $swList = (float) $enh->sw_list;
                $swNet = (float) $enh->sw_net;
                $discPct = $enh->discount_percent !== null
                    ? (float) $enh->discount_percent
                    : (($swList > 0 && $swNet >= 0 && $swList >= $swNet) ? round((1 - ($swNet / $swList)) * 100, 1) : 0.0);

                $items[] = [
                    'id' => $enh->id,
                    'model' => $model->code,
                    'cat' => $enh->category,
                    'desc' => $enh->description,
                    'isBase' => false,
                    'msrp' => (float) $enh->msrp,
                    'swList' => $swList,
                    'swNet' => $swNet,
                    'discount_percent' => $discPct,
                ];
            }
        }

        // Freight estimates
        $freightRules = FreightRule::orderBy('state')->orderBy('city')->get()->map(function ($rule) {
            return [
                'state' => $rule->state,
                'city' => $rule->city,
                'customer' => (float) $rule->customer_rate,
                'municipality' => (float) $rule->municipality_rate,
            ];
        })->toArray();

        // Template & Pricing Bases settings
        $templateSettings = $this->quoteTemplateService->getSettings();
        $templateBases = $templateSettings['pricing_bases'] ?? [];

        // Merge with any additional active PricingBasis rows from DB
        $dbBases = PricingBasis::active()->get();
        $knownSlugs = collect($templateBases)->pluck('slug')->filter()->map(fn ($s) => strtolower($s))->toArray();
        $knownLabels = collect($templateBases)->pluck('label')->filter()->map(fn ($s) => strtolower($s))->toArray();

        foreach ($dbBases as $basis) {
            if (! in_array(strtolower($basis->slug), $knownSlugs, true) && ! in_array(strtolower($basis->name), $knownLabels, true)) {
                $isCoop = $basis->price_field === 'sw_net' || (bool) $basis->show_discount;
                $templateBases[] = [
                    'key' => $basis->name,
                    'label' => $basis->name,
                    'slug' => $basis->slug,
                    'price_tier' => $basis->price_field ?: ($isCoop ? 'sw_net' : 'msrp'),
                    'discount_percent' => (float) $basis->discount_percent,
                    'subtotal_label' => $basis->subtotal_label ?: ($basis->name . ' Subtotal'),
                    'description_template' => $basis->description_template ?: ($isCoop ? "Referenced under {$basis->name}" : ''),
                    'disclaimer' => $isCoop ? "Quoted under cooperative contract: {$basis->name}" : '',
                    'logo_key' => $basis->logo_key,
                    'caption' => $isCoop ? "Referenced under {$basis->name}" : '',
                    'is_coop' => $isCoop,
                    'use_municipality_freight' => (bool) $basis->use_municipality_freight,
                ];
            }
        }

        $pricingBases = array_map(function ($b) {
            $isCoop = (bool) ($b['is_coop'] ?? ($b['price_tier'] ?? '') === 'sw_net');
            return [
                'key' => $b['key'] ?? $b['label'],
                'name' => $b['label'] ?? $b['key'],
                'label' => $b['label'] ?? $b['key'],
                'slug' => $b['slug'] ?? strtolower($b['key'] ?? ''),
                'price_field' => $b['price_tier'] ?? 'msrp',
                'price_tier' => $b['price_tier'] ?? 'msrp',
                'discount_percent' => isset($b['discount_percent']) ? (float) $b['discount_percent'] : ($isCoop ? 12.00 : 0.00),
                'subtotal_label' => $b['subtotal_label'] ?? 'Subtotal',
                'description_template' => $b['description_template'] ?? $b['caption'] ?? '',
                'disclaimer' => $b['disclaimer'] ?? '',
                'logo_key' => $b['logo_key'] ?? null,
                'caption' => $b['caption'] ?? '',
                'is_coop' => $isCoop,
                'show_discount' => $isCoop,
                'use_municipality_freight' => (bool) ($b['use_municipality_freight'] ?? false),
            ];
        }, $templateBases);

        $templateSettings['pricing_bases'] = $pricingBases;

        // Territories
        $territories = Territory::all()->map(function ($t) {
            return [
                'code' => $t->code,
                'label' => $t->label,
                'rep' => $t->assignedRep ? $t->assignedRep->name : 'Unassigned',
                'rep_id' => $t->assigned_rep_id,
                'states' => $t->states ?? [],
                'cities' => $t->cities ?? [],
            ];
        })->toArray();

        // Sales Rep 2-letter codes
        $srCodes = [];
        try {
            $reps = User::whereNotNull('rep_code')->get(['name', 'rep_code']);
            foreach ($reps as $rep) {
                if ($rep->name && $rep->rep_code) {
                    $srCodes[$rep->name] = strtoupper($rep->rep_code);
                }
            }
        } catch (\Throwable $e) {
            // Fallback handled in JS
        }

        // Model-specific quote templates (Pricing bases & Legal / Standard features per model)
        $modelTemplates = [];
        foreach ($modelsList as $m) {
            $mCode = $m['code'];
            $mSettings = $this->quoteTemplateService->getSettings($mCode);
            $modelTemplates[$mCode] = [
                'pricing_bases' => $this->quoteTemplateService->formatPricingBasesForApi($mSettings['pricing_bases'] ?? $pricingBases),
                'legal' => $mSettings['legal'] ?? $templateSettings['legal'],
                'is_custom' => ! empty($mSettings['is_custom']),
            ];
        }

        // Hierarchical configurations data (chassis descriptions, pricing tables, standard features per config)
        $configurationsData = [];
        foreach ($modelsList as $m) {
            $mCode = $m['code'];
            $configurationsData[$mCode] = $this->quoteTemplateService->getConfigurationsDataForModel(
                $mCode,
                $chassisByModel[$mCode] ?? [],
                $m['description']
            );
        }

        $availableLogos = $this->quoteTemplateService->getAvailableLogos();

        return [
            'models' => $modelsList,
            'items' => $items,
            'chassis' => $chassis,
            'chassis_by_model' => $chassisByModel,
            'chassis_short' => $chassisShort,
            'chassis_prices' => $chassisPrices,
            'freight' => $freightRules,
            'pricing_bases' => $pricingBases,
            'model_templates' => $modelTemplates,
            'configurations_data' => $configurationsData,
            'available_logos' => $availableLogos,
            'quote_template' => $templateSettings,
            'territories' => $territories,
            'sr_codes' => $srCodes,
        ];
    }
}
