<?php

namespace App\Services;

use App\Models\Enhancement;
use App\Models\FreightRule;
use App\Models\ModelConfiguration;
use App\Models\ModelPrice;
use App\Models\PricingBasis;
use App\Models\SweeperModel;
use App\Models\Territory;

class CatalogService
{
    /**
     * Get the complete catalog payload required by the Quote Builder.
     */
    public function getCatalogPayload(): array
    {
        $activeModels = SweeperModel::active()->with(['configurations' => function ($q) {
            $q->where('is_active', true)->orderBy('sort_order');
        }])->get();

        $items = [];
        $chassis = [];
        $chassisShort = [];

        foreach ($activeModels as $model) {
            // Find current pricing
            $price = ModelPrice::where('sweeper_model_id', $model->id)
                ->current()
                ->latest('effective_from')
                ->first();

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

            // Chassis configurations
            foreach ($model->configurations as $cfg) {
                $chassis[] = $cfg->label;
                if ($cfg->short_label) {
                    $chassisShort[$cfg->label] = $cfg->short_label;
                }
            }

            // Enhancements
            $enhancements = Enhancement::where('sweeper_model_id', $model->id)
                ->active()
                ->orderBy('sort_order')
                ->get();

            foreach ($enhancements as $enh) {
                $items[] = [
                    'id' => $enh->id,
                    'model' => $model->code,
                    'cat' => $enh->category,
                    'desc' => $enh->description,
                    'isBase' => false,
                    'msrp' => (float) $enh->msrp,
                    'swList' => (float) $enh->sw_list,
                    'swNet' => (float) $enh->sw_net,
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

        // Pricing bases
        $pricingBases = PricingBasis::active()->get()->map(function ($basis) {
            return [
                'name' => $basis->name,
                'slug' => $basis->slug,
                'price_field' => $basis->price_field,
                'subtotal_label' => $basis->subtotal_label,
                'logo_key' => $basis->logo_key,
                'show_discount' => (bool) $basis->show_discount,
                'use_municipality_freight' => (bool) $basis->use_municipality_freight,
            ];
        })->toArray();

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

        return [
            'items' => $items,
            'chassis' => $chassis,
            'chassis_short' => $chassisShort,
            'freight' => $freightRules,
            'pricing_bases' => $pricingBases,
            'territories' => $territories,
        ];
    }
}
