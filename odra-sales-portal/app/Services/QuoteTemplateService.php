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
                        'custom_logos' => $saved['custom_logos'] ?? [],
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
            'configurations' => $modelOverride['configurations'] ?? [],
            'custom_logos' => $current['custom_logos'] ?? [],
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

            // Hierarchical configurations payload
            if (isset($newSettings['configurations']) && is_array($newSettings['configurations'])) {
                $modelData['configurations'] = $this->normalizeConfigurationsPayload($scopeKey, $newSettings['configurations']);
            }

            $current['models'][$scopeKey] = $modelData;
        }

        if (isset($newSettings['custom_logos']) && is_array($newSettings['custom_logos'])) {
            $current['custom_logos'] = array_merge($current['custom_logos'] ?? [], $newSettings['custom_logos']);
        }

        Storage::disk('local')->put($this->storagePath, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $current;
    }

    /**
     * Save hierarchical configuration settings for a model and all or specific configurations.
     */
    public function saveHierarchicalSettings(string $modelCode, array $configurationsPayload): array
    {
        $code = strtoupper(trim($modelCode));
        $current = $this->getSettings('global');

        if (! isset($current['models'])) {
            $current['models'] = [];
        }

        $modelData = $current['models'][$code] ?? [];
        $existingConfigs = $modelData['configurations'] ?? [];

        $normalized = $this->normalizeConfigurationsPayload($code, $configurationsPayload);
        $mergedConfigs = array_merge($existingConfigs, $normalized);

        $modelData['configurations'] = $mergedConfigs;

        // Synchronize top-level model pricing bases using the first configuration as fallback
        if (! empty($mergedConfigs)) {
            $firstCfg = reset($mergedConfigs);
            if (! empty($firstCfg['pricing_bases_list']) && is_array($firstCfg['pricing_bases_list'])) {
                $modelData['pricing_bases'] = $firstCfg['pricing_bases_list'];
            }
        }

        $current['models'][$code] = $modelData;

        Storage::disk('local')->put($this->storagePath, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $current;
    }

    /**
     * Normalize configurations payload.
     */
    public function normalizeConfigurationsPayload(string $modelCode, array $configs): array
    {
        $code = strtoupper(trim($modelCode));
        $normalized = [];

        foreach ($configs as $chassisLabel => $cfg) {
            $label = trim($chassisLabel);
            if ($label === '') {
                continue;
            }

            $desc = trim($cfg['description'] ?? '');
            if ($desc === '') {
                $desc = $this->getDefaultDescription($code, $label);
            }

            $rawBases = $cfg['pricing_bases'] ?? $cfg['pricing_bases_list'] ?? [];
            $basesMap = [];
            $basesList = [];

            if (is_array($rawBases)) {
                $defaultFeatures = $this->getDefaultStandardFeatures($code);

                foreach ($rawBases as $bKey => $bData) {
                    $name = trim($bData['label'] ?? $bData['name'] ?? $bData['key'] ?? (is_string($bKey) ? $bKey : ''));
                    if ($name === '') {
                        continue;
                    }

                    $key = trim($bData['key'] ?? $name);
                    $priceTier = in_array($bData['price_tier'] ?? '', ['msrp', 'sw_net'], true)
                        ? $bData['price_tier']
                        : (str_contains(strtolower($name), 'msrp') ? 'msrp' : 'sw_net');
                    $isCoop = $priceTier === 'sw_net';

                    $msrp = isset($bData['msrp']) && $bData['msrp'] !== '' ? (float) $bData['msrp'] : ($code === 'MS5H' ? 349149.00 : 257493.00);
                    $disc = isset($bData['discount_percent']) && $bData['discount_percent'] !== '' ? (float) $bData['discount_percent'] : ($isCoop ? 12.00 : 0.00);

                    if (isset($bData['calculated_price']) && $bData['calculated_price'] !== '' && (float) $bData['calculated_price'] > 0) {
                        $calcPrice = (float) $bData['calculated_price'];
                    } else {
                        $calcPrice = round($msrp * (1 - ($disc / 100)), 2);
                    }

                    $features = ! empty($bData['standard_features']) && is_array($bData['standard_features'])
                        ? array_values(array_filter(array_map('trim', $bData['standard_features'])))
                        : $defaultFeatures;

                    $item = [
                        'key' => $key,
                        'name' => $name,
                        'label' => $name,
                        'slug' => Str::slug($key),
                        'price_tier' => $priceTier,
                        'msrp' => $msrp,
                        'discount_percent' => $disc,
                        'calculated_price' => $calcPrice,
                        'subtotal_label' => trim($bData['subtotal_label'] ?? ($isCoop ? "{$name} Contract Price" : 'MSRP Subtotal')),
                        'description_template' => trim($bData['description_template'] ?? $bData['caption'] ?? ''),
                        'caption' => trim($bData['caption'] ?? ''),
                        'disclaimer' => trim($bData['disclaimer'] ?? ($isCoop ? "Quoted under cooperative contract: {$name}" : '')),
                        'logo_key' => ! empty($bData['logo_key']) && $bData['logo_key'] !== 'none' ? trim($bData['logo_key']) : null,
                        'is_coop' => $isCoop,
                        'use_municipality_freight' => ! empty($bData['use_municipality_freight']),
                        'is_active' => ! isset($bData['is_active']) || ! empty($bData['is_active']),
                        'standard_features' => $features,
                    ];

                    $basesMap[$name] = $item;
                    $basesMap[$key] = $item;
                    $basesList[] = $item;
                }
            }

            if (empty($basesList)) {
                $defaultBases = $this->getDefaultPricingBasesForConfig($code);
                foreach ($defaultBases as $db) {
                    $basesMap[$db['name']] = $db;
                    $basesMap[$db['key']] = $db;
                    $basesList[] = $db;
                }
            }

            $normalized[$label] = [
                'label' => $label,
                'description' => $desc,
                'pricing_bases' => $basesMap,
                'pricing_bases_list' => $basesList,
            ];
        }

        return $normalized;
    }

    /**
     * Get available logos (default system logos + custom uploaded logos).
     */
    public function getAvailableLogos(): array
    {
        $defaults = [
            'none' => ['key' => 'none', 'label' => 'No Logo', 'url' => ''],
            'sourcewell' => ['key' => 'sourcewell', 'label' => 'Sourcewell Logo', 'url' => '/images/logo-sourcewell.png'],
            'hgac' => ['key' => 'hgac', 'label' => 'H-GAC Logo', 'url' => '/images/logo-hgac.png'],
            'buyboard' => ['key' => 'buyboard', 'label' => 'BuyBoard Logo', 'url' => '/images/logo-buyboard.png'],
        ];

        $settings = $this->getSettings('global');
        $custom = $settings['custom_logos'] ?? [];

        return array_merge($defaults, $custom);
    }

    /**
     * Save a custom uploaded logo.
     */
    public function saveCustomLogo(string $key, string $label, string $url): array
    {
        $settings = $this->getSettings('global');
        if (! isset($settings['custom_logos'])) {
            $settings['custom_logos'] = [];
        }
        $settings['custom_logos'][$key] = [
            'key' => $key,
            'label' => $label,
            'url' => $url,
        ];
        Storage::disk('local')->put($this->storagePath, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return $settings['custom_logos'];
    }

    /**
     * Get dedicated default product description for a chassis configuration.
     */
    public function getDefaultDescription(string $modelCode, string $chassisLabel): string
    {
        $code = strtoupper(trim($modelCode));
        if ($code === 'MT4H') {
            $brand = str_contains($chassisLabel, 'Ford F-550XL') ? 'Ford F-550XL' : 'Isuzu NRR';
            $gvwr = str_contains($chassisLabel, 'Ford F-550XL') ? '19,000' : '19,500';
            $side = str_contains(strtoupper($chassisLabel), 'RIGHT') ? 'Right' : 'Left';
            return "MT4H Mechanical Broom Sweeper is a dual engine Tier 4-Final, 65HP auxiliary engine sweeper with a 4-cubic yard hopper, mounted on a non-CDL, 109\" WB Class 6 cabover commercial truck chassis with 4-wheel disc brakes, and full LED chassis lighting. The water spray system features a 220-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white with ODRA branding and fully built with standard features noted below. {$side}-side dump sweeper. Chassis is a brand new 2027MY {$brand}, {$gvwr} GVWR.";
        }

        if ($code === 'MS5H') {
            $isCdl = str_contains(strtoupper($chassisLabel), 'CLASS 7') || str_contains(strtoupper($chassisLabel), 'CDL-REQUIRED');
            $cdlText = $isCdl ? 'CDL-required, 148" WB Class 7' : 'non-CDL, 148" WB Class 6';
            return "MS5H Mechanical Broom Sweeper is a single engine sweeper with a 5-cubic yard hopper, mounted on a {$cdlText} commercial truck chassis with a 300HP Cummins engine with air brakes and full LED chassis lighting. The water spray system features a 355-gallon tank and 4 spray bars. The hopper features a variable height discharge up to 10-ft. Standard paint color is white and red with ODRA branding and fully built with standard features noted below. Left-side dump sweeper. Chassis is a brand new 2027MY Freightliner M2 Plus, 26,000 GVWR.";
        }

        $model = \App\Models\SweeperModel::where('code', $code)->first();
        return $model?->description ?? "{$code} Mechanical Broom Sweeper built with standard features and high performance chassis.";
    }

    /**
     * Get default standard features array for a model.
     */
    public function getDefaultStandardFeatures(string $modelCode): array
    {
        $code = strtoupper(trim($modelCode));
        $features = [
            '<b>Standard Features:</b> ODRA Sweepers are fully loaded with standard features including rear air ride suspension, squeegee elevator design, variable displacement load-sensing hydraulic pump, dual hydraulically driven side brooms, and electric backup alarm.',
            'Sweeper has single, one button sweeping operation, greaseless composite bushings with chromed shafts. The elevator raises automatically when chassis is put into reverse. The elevator is reversible when stall alarm is activated. Heavy duty, 8-flight squeegee style elevator. Sweeper features dual side brooms that feature tilt adjustment and down pressure adjustment from inside cab. Water system includes our polyethylene water tank, an electrical diaphragm water pump, and 4 spray bars for dust control suppression that can be controlled from inside the cab.',
            'Inside cab controls also include safety infrared, night-vision 3-camera system with color monitor. Safety LED lighting includes complete chassis lighting, rear mounted strobe light, and per broom control.'
        ];

        if ($code !== 'MS5H') {
            $features[] = 'PM-10 compliant (SCAQMD Rule 1186).';
        }

        return $features;
    }

    /**
     * Get default pricing bases table for a configuration.
     */
    public function getDefaultPricingBasesForConfig(string $modelCode): array
    {
        $code = strtoupper(trim($modelCode));
        $isMS5H = ($code === 'MS5H');
        $msrp = $isMS5H ? 349149.00 : 257493.00;
        $swList = $isMS5H ? 411533.00 : 307378.00;
        $features = $this->getDefaultStandardFeatures($code);

        return [
            [
                'key' => 'MSRP (Commercial)',
                'name' => 'MSRP (Commercial)',
                'label' => 'MSRP (Commercial)',
                'slug' => 'msrp',
                'price_tier' => 'msrp',
                'msrp' => $msrp,
                'discount_percent' => 0.0,
                'calculated_price' => $msrp,
                'subtotal_label' => 'MSRP Subtotal',
                'caption' => '',
                'disclaimer' => '',
                'description_template' => '',
                'logo_key' => 'none',
                'use_municipality_freight' => false,
                'is_active' => true,
                'is_coop' => false,
                'standard_features' => $features,
            ],
            [
                'key' => 'Sourcewell',
                'name' => 'Sourcewell',
                'label' => 'Sourcewell',
                'slug' => 'sourcewell',
                'price_tier' => 'sw_net',
                'msrp' => $swList,
                'discount_percent' => 12.0,
                'calculated_price' => round($swList * (1 - 0.12), 2),
                'subtotal_label' => 'Sourcewell Contract Price',
                'caption' => 'Referenced under Sourcewell<br>Contract # 062425-ODRA',
                'disclaimer' => 'Quoted under cooperative contract: Sourcewell #062425-ODRA',
                'description_template' => 'Referenced under Sourcewell Contract # 062425-ODRA',
                'logo_key' => 'sourcewell',
                'use_municipality_freight' => true,
                'is_active' => true,
                'is_coop' => true,
                'standard_features' => $features,
            ],
            [
                'key' => 'HGAC',
                'name' => 'H-GAC / HGACBuy',
                'label' => 'H-GAC / HGACBuy',
                'slug' => 'hgac',
                'price_tier' => 'sw_net',
                'msrp' => $swList,
                'discount_percent' => 15.0,
                'calculated_price' => round($swList * (1 - 0.15), 2),
                'subtotal_label' => 'H-GAC Contract Price',
                'caption' => 'Referenced under H-GAC/HGACBuy<br>Master Agreement SW04-26',
                'disclaimer' => 'Quoted under cooperative contract: H-GAC/HGACBuy Master Agreement SW04-26',
                'description_template' => 'Referenced under H-GAC/HGACBuy Master Agreement SW04-26',
                'logo_key' => 'hgac',
                'use_municipality_freight' => true,
                'is_active' => true,
                'is_coop' => true,
                'standard_features' => $features,
            ],
            [
                'key' => 'Minnesota',
                'name' => 'State of Minnesota',
                'label' => 'State of Minnesota',
                'slug' => 'minnesota',
                'price_tier' => 'sw_net',
                'msrp' => $swList,
                'discount_percent' => 12.0,
                'calculated_price' => round($swList * (1 - 0.12), 2),
                'subtotal_label' => 'Minnesota Contract Price',
                'caption' => 'Referenced under State of Minnesota<br>Contract 285158 (Release S-843(5))',
                'disclaimer' => 'Quoted under cooperative contract: State of Minnesota Contract 285158, Release S-843(5)',
                'description_template' => 'Referenced under State of Minnesota Contract 285158 (Release S-843(5))',
                'logo_key' => 'none',
                'use_municipality_freight' => true,
                'is_active' => true,
                'is_coop' => true,
                'standard_features' => $features,
            ],
            [
                'key' => 'Buyboard',
                'name' => 'BuyBoard',
                'label' => 'BuyBoard',
                'slug' => 'buyboard',
                'price_tier' => 'sw_net',
                'msrp' => $swList,
                'discount_percent' => 12.0,
                'calculated_price' => round($swList * (1 - 0.12), 2),
                'subtotal_label' => 'BuyBoard Contract Price',
                'caption' => 'Referenced under BuyBoard<br>ODRA Contract # 788-25',
                'disclaimer' => 'Quoted under cooperative contract: ODRA BuyBoard Contract #788-25',
                'description_template' => 'Referenced under BuyBoard ODRA Contract # 788-25',
                'logo_key' => 'buyboard',
                'use_municipality_freight' => true,
                'is_active' => true,
                'is_coop' => true,
                'standard_features' => $features,
            ],
        ];
    }

    /**
     * Get complete configurations data for a model, ensuring every chassis configuration
     * has its dedicated product description, pricing bases table, and standard features.
     */
    public function getConfigurationsDataForModel(string $modelCode, array $chassisList, ?string $baseDescription = null): array
    {
        $code = strtoupper(trim($modelCode));
        $settings = $this->getSettings($code);
        $savedConfigs = $settings['configurations'] ?? [];

        $result = [];

        foreach ($chassisList as $chassisLabel) {
            $label = trim($chassisLabel);
            if ($label === '') {
                continue;
            }

            if (isset($savedConfigs[$label])) {
                $saved = $savedConfigs[$label];
                $result[$label] = [
                    'label' => $label,
                    'description' => $saved['description'] ?? $this->getDefaultDescription($code, $label),
                    'pricing_bases' => $saved['pricing_bases'] ?? [],
                    'pricing_bases_list' => $saved['pricing_bases_list'] ?? array_values($saved['pricing_bases'] ?? []),
                ];
            } else {
                // Initialize defaults for this configuration
                $defaultBases = $this->getDefaultPricingBasesForConfig($code);
                $basesMap = [];
                foreach ($defaultBases as $b) {
                    $basesMap[$b['name']] = $b;
                    $basesMap[$b['key']] = $b;
                }

                $result[$label] = [
                    'label' => $label,
                    'description' => $this->getDefaultDescription($code, $label),
                    'pricing_bases' => $basesMap,
                    'pricing_bases_list' => $defaultBases,
                ];
            }
        }

        return $result;
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
                'name' => $label,
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
                'standard_features' => $b['standard_features'] ?? [],
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
            'custom_logos' => [],
            'models' => [
                'MT4H' => [
                    'pricing_bases' => $pricingBases,
                    'legal' => $globalLegal,
                    'configurations' => [],
                ],
                'MS5H' => [
                    'pricing_bases' => $pricingBases,
                    'legal' => array_merge($globalLegal, ['pm10_note' => '']),
                    'configurations' => [],
                ],
            ],
        ];
    }
}
