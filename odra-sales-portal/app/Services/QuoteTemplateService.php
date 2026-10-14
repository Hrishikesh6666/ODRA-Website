<?php

namespace App\Services;

use App\Models\PricingBasis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class QuoteTemplateService
{
    protected string $storagePath = 'quote_template_settings.json';

    /**
     * Get quotation template & pricing basis configuration globally or for a specific sweeper model.
     */
    public function getSettings(?string $modelCode = null): array
    {
        $defaults = $this->getDefaultSettings();

        if (! Storage::disk('local')->exists($this->storagePath)) {
            $current = $defaults;
        } else {
            try {
                $raw = Storage::disk('local')->get($this->storagePath);
                $saved = json_decode($raw, true);
                if (! is_array($saved)) {
                    $current = $defaults;
                } else {
                    $current = [
                        'header' => array_merge($defaults['header'], $saved['header'] ?? []),
                        'pricing_bases' => ! empty($saved['pricing_bases']) && is_array($saved['pricing_bases'])
                            ? $saved['pricing_bases']
                            : $defaults['pricing_bases'],
                        'legal' => array_merge($defaults['legal'], $saved['legal'] ?? []),
                        'models' => $saved['models'] ?? $defaults['models'] ?? [],
                    ];
                }
            } catch (\Throwable $e) {
                $current = $defaults;
            }
        }

        if (empty($modelCode) || strtolower($modelCode) === 'global') {
            return $current;
        }

        $code = strtoupper(trim($modelCode));
        $modelOverride = $current['models'][$code] ?? null;
        $isCustom = ! empty($modelOverride);

        $defaultModelLegal = $current['legal'];
        if ($code === 'MS5H') {
            $defaultModelLegal['pm10_note'] = '';
        }

        $effectivePricingBases = ! empty($modelOverride['pricing_bases']) && is_array($modelOverride['pricing_bases'])
            ? $modelOverride['pricing_bases']
            : $current['pricing_bases'];

        $effectiveLegal = ! empty($modelOverride['legal']) && is_array($modelOverride['legal'])
            ? array_merge($defaultModelLegal, $modelOverride['legal'])
            : $defaultModelLegal;

        return [
            'header' => $current['header'],
            'pricing_bases' => $effectivePricingBases,
            'legal' => $effectiveLegal,
            'is_custom' => $isCustom,
            'model_code' => $code,
            'models' => $current['models'] ?? [],
        ];
    }

    /**
     * Save updated quotation template & pricing basis settings (either globally or for a specific model).
     */
    public function saveSettings(array $newSettings, string $scope = 'global'): array
    {
        $current = $this->getSettings('global');
        $scopeKey = strtoupper(trim($scope));

        if ($scopeKey === 'GLOBAL') {
            if (isset($newSettings['header']) && is_array($newSettings['header'])) {
                $current['header'] = array_merge($current['header'], $newSettings['header']);
            }

            if (isset($newSettings['legal']) && is_array($newSettings['legal'])) {
                $current['legal'] = array_merge($current['legal'], $newSettings['legal']);
            }

            if (isset($newSettings['pricing_bases']) && is_array($newSettings['pricing_bases'])) {
                $cleanedBases = $this->cleanPricingBases($newSettings['pricing_bases'], true);
                if (! empty($cleanedBases)) {
                    $current['pricing_bases'] = $cleanedBases;
                }
            }
        } else {
            // Model-specific customization (e.g. MT4H, MS5H, or any new model)
            if (! isset($current['models'])) {
                $current['models'] = [];
            }

            $modelData = $current['models'][$scopeKey] ?? [];

            if (isset($newSettings['pricing_bases']) && is_array($newSettings['pricing_bases'])) {
                $modelData['pricing_bases'] = $this->cleanPricingBases($newSettings['pricing_bases'], false);
            }

            if (isset($newSettings['legal']) && is_array($newSettings['legal'])) {
                $modelData['legal'] = array_merge($current['legal'], $newSettings['legal']);
            }

            $current['models'][$scopeKey] = $modelData;
        }

        Storage::disk('local')->put($this->storagePath, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $current;
    }

    /**
     * Revert a specific model to inherit from Global Defaults.
     */
    public function resetModelSettings(string $modelCode): array
    {
        $current = $this->getSettings('global');
        $code = strtoupper(trim($modelCode));

        if (isset($current['models'][$code])) {
            unset($current['models'][$code]);
            Storage::disk('local')->put($this->storagePath, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return $current;
    }

    /**
     * Clean and normalize raw pricing bases array.
     */
    public function cleanPricingBases(array $rawBases, bool $syncDb = false): array
    {
        $cleaned = [];
        foreach ($rawBases as $b) {
            $key = trim($b['key'] ?? $b['label'] ?? '');
            $label = trim($b['label'] ?? $key);
            if ($key === '' && $label === '') {
                continue;
            }
            if ($key === '') {
                $key = $label;
            }
            $slug = trim($b['slug'] ?? '') ?: Str::slug($key);
            $priceTier = in_array($b['price_tier'] ?? '', ['msrp', 'sw_net'], true) ? $b['price_tier'] : 'sw_net';
            $isCoop = isset($b['is_coop']) ? (bool) $b['is_coop'] : ($priceTier === 'sw_net');
            $discountPercent = isset($b['discount_percent']) ? (float) $b['discount_percent'] : ($isCoop ? 12.00 : 0.00);
            $descTemplate = trim($b['description_template'] ?? $b['caption'] ?? '');
            $useMuniFreight = isset($b['use_municipality_freight']) ? (bool) $b['use_municipality_freight'] : $isCoop;
            $isActive = isset($b['is_active']) ? (bool) $b['is_active'] : true;

            $item = [
                'key' => $key,
                'label' => $label,
                'slug' => $slug,
                'price_tier' => $priceTier,
                'discount_percent' => $discountPercent,
                'subtotal_label' => trim($b['subtotal_label'] ?? ($label . ' Subtotal')),
                'description_template' => $descTemplate,
                'disclaimer' => trim($b['disclaimer'] ?? ''),
                'logo_key' => ! empty($b['logo_key']) && $b['logo_key'] !== 'none' ? trim($b['logo_key']) : null,
                'caption' => trim($b['caption'] ?? ''),
                'is_coop' => $isCoop,
                'use_municipality_freight' => $useMuniFreight,
                'is_active' => $isActive,
            ];

            $cleaned[] = $item;

            if ($syncDb) {
                try {
                    PricingBasis::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'name' => $label,
                            'price_field' => $priceTier,
                            'discount_percent' => $discountPercent,
                            'subtotal_label' => trim($b['subtotal_label'] ?? ($label . ' Subtotal')),
                            'description_template' => $descTemplate,
                            'logo_key' => ! empty($b['logo_key']) && $b['logo_key'] !== 'none' ? trim($b['logo_key']) : null,
                            'show_discount' => $isCoop,
                            'use_municipality_freight' => $useMuniFreight,
                            'is_active' => $isActive,
                        ]
                    );
                } catch (\Throwable $e) {
                    // Ignore DB sync errors if table is locked
                }
            }
        }

        return $cleaned;
    }

    /**
     * Map pricing bases array for API / Quote Builder output.
     */
    public function formatPricingBasesForApi(array $bases): array
    {
        return array_map(function ($b) {
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
                'is_active' => (bool) ($b['is_active'] ?? true),
            ];
        }, $bases);
    }

    /**
     * Exact defaults matching ODRA_Quote_Generator_Rep_1 1.html.
     */
    public function getDefaultSettings(): array
    {
        $pricingBases = [
            [
                'key' => 'MSRP (Commercial)',
                'label' => 'MSRP (Commercial)',
                'slug' => 'msrp',
                'price_tier' => 'msrp',
                'discount_percent' => 0.00,
                'subtotal_label' => 'MSRP Subtotal',
                'description_template' => null,
                'disclaimer' => '',
                'logo_key' => null,
                'caption' => '',
                'is_coop' => false,
                'use_municipality_freight' => false,
                'is_active' => true,
            ],
            [
                'key' => 'Sourcewell',
                'label' => 'Sourcewell',
                'slug' => 'sourcewell',
                'price_tier' => 'sw_net',
                'discount_percent' => 12.00,
                'subtotal_label' => 'Sourcewell Contract Price',
                'description_template' => 'Referenced under Sourcewell Contract # 062425-ODRA',
                'disclaimer' => 'Quoted under cooperative contract: Sourcewell #062425-ODRA',
                'logo_key' => 'sourcewell',
                'caption' => 'Referenced under Sourcewell<br>Contract # 062425-ODRA',
                'is_coop' => true,
                'use_municipality_freight' => true,
                'is_active' => true,
            ],
            [
                'key' => 'HGAC',
                'label' => 'H-GAC / HGACBuy',
                'slug' => 'hgac',
                'price_tier' => 'sw_net',
                'discount_percent' => 15.00,
                'subtotal_label' => 'H-GAC Contract Price',
                'description_template' => 'Referenced under H-GAC/HGACBuy Master Agreement SW04-26',
                'disclaimer' => 'Quoted under cooperative contract: H-GAC/HGACBuy Master Agreement SW04-26',
                'logo_key' => 'hgac',
                'caption' => 'Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26',
                'is_coop' => true,
                'use_municipality_freight' => true,
                'is_active' => true,
            ],
            [
                'key' => 'Minnesota',
                'label' => 'State of Minnesota',
                'slug' => 'minnesota',
                'price_tier' => 'sw_net',
                'discount_percent' => 12.00,
                'subtotal_label' => 'Minnesota Contract Price',
                'description_template' => 'Referenced under State of Minnesota Contract 285158 (Release S-843(5))',
                'disclaimer' => 'Quoted under cooperative contract: State of Minnesota Contract 285158, Release S-843(5)',
                'logo_key' => null,
                'caption' => 'Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))',
                'is_coop' => true,
                'use_municipality_freight' => true,
                'is_active' => true,
            ],
            [
                'key' => 'Buyboard',
                'label' => 'BuyBoard',
                'slug' => 'buyboard',
                'price_tier' => 'sw_net',
                'discount_percent' => 12.00,
                'subtotal_label' => 'BuyBoard Contract Price',
                'description_template' => 'Referenced under BuyBoard ODRA Contract # 788-25',
                'disclaimer' => 'Quoted under cooperative contract: ODRA BuyBoard Contract #788-25',
                'logo_key' => 'buyboard',
                'caption' => 'Referenced under BuyBoard<br>ODRA Contract # 788-25',
                'is_coop' => true,
                'use_municipality_freight' => true,
                'is_active' => true,
            ],
        ];

        $globalLegal = [
            'standard_features_p1' => '<b>Standard Features:</b> ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.',
            'standard_features_p2' => 'Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled from inside the cab.',
            'standard_features_p3' => 'Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control.',
            'pm10_note' => 'PM-10 compliant (SCAQMD Rule 1186).',
            'legal_p1' => 'Delivery transport charge, shown as an estimate ONLY. Actual rate to be determined at time of invoicing.',
            'legal_p2' => 'Charges relating to taxes, registration, titling, and other fees are the responsibility of the customer; not included in this quotation. Customer takes responsibility for adherence to local and state laws for chassis, emissions, and registration relating to the sweeper.',
            'legal_p3' => 'A <b>$10,000 deposit and Purchase Order number</b> is requested at time of quotation acceptance. ODRA will issue a detailed sales acknowledgement for signature to confirm the order prior to assembly. Balance will be due prior to shipping, unless otherwise confirmed in writing by an authorized ODRA representative.',
            'legal_p4' => 'This quote is <b>BUDGETARY ONLY</b>, valid 21 days from date of quote. ODRA LLC may modify or cancel without notice. Prices and availability are subject to change.',
        ];

        return [
            'header' => [
                'company_name' => 'ODRA Road Sweepers',
                'address_line_1' => '705 South 48th Street, Grand Forks, ND 58201',
                'address_line_2' => 't: 844-888-6372 • e: info@odrasweeper.com • odrasweeper.com',
                'quote_title' => 'QUOTATION',
                'section_heading' => 'Including Enhancements',
                'freight_row_label' => 'Sweeper Transport / Freight',
                'total_row_label' => 'TOTAL (USD)',
                'max_show_discount' => 20000,
                'default_fob' => 'Transport, estimate only noted in quote',
                'exw_fob' => 'EXW, Factory pick-up',
            ],
            'pricing_bases' => $pricingBases,
            'legal' => $globalLegal,
            'models' => [
                'MT4H' => [
                    'pricing_bases' => $pricingBases,
                    'legal' => $globalLegal,
                ],
                'MS5H' => [
                    'pricing_bases' => $pricingBases,
                    'legal' => array_merge($globalLegal, ['pm10_note' => '']),
                ],
            ],
        ];
    }
}
