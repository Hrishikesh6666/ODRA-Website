<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PricingBasis;

class PricingBasisSeeder extends Seeder
{
    public function run(): void
    {
        $bases = [
            [
                'name' => 'MSRP (Commercial)',
                'slug' => 'msrp',
                'price_field' => 'msrp',
                'discount_percent' => 0.00,
                'subtotal_label' => 'MSRP Subtotal',
                'description_template' => null,
                'logo_key' => null,
                'show_discount' => false,
                'use_municipality_freight' => false,
            ],
            [
                'name' => 'Sourcewell',
                'slug' => 'sourcewell',
                'price_field' => 'sw_net',
                'discount_percent' => 12.00,
                'subtotal_label' => 'Sourcewell Contract Price',
                'description_template' => 'Referenced under Sourcewell Contract # 062425-ODRA',
                'logo_key' => 'sourcewell',
                'show_discount' => true,
                'use_municipality_freight' => true,
            ],
            [
                'name' => 'H-GAC / HGACBuy',
                'slug' => 'hgac',
                'price_field' => 'sw_net',
                'discount_percent' => 12.00,
                'subtotal_label' => 'H-GAC Contract Price',
                'description_template' => 'Referenced under H-GAC/HGACBuy Master Agreement SW04-26',
                'logo_key' => 'hgac',
                'show_discount' => true,
                'use_municipality_freight' => true,
            ],
            [
                'name' => 'State of Minnesota',
                'slug' => 'minnesota',
                'price_field' => 'sw_net',
                'discount_percent' => 12.00,
                'subtotal_label' => 'Minnesota Contract Price',
                'description_template' => 'Referenced under State of Minnesota Contract 285158 (Release S-843(5))',
                'logo_key' => null,
                'show_discount' => true,
                'use_municipality_freight' => true,
            ],
            [
                'name' => 'BuyBoard',
                'slug' => 'buyboard',
                'price_field' => 'sw_net',
                'discount_percent' => 12.00,
                'subtotal_label' => 'BuyBoard Contract Price',
                'description_template' => 'Referenced under BuyBoard ODRA Contract # 788-25',
                'logo_key' => 'buyboard',
                'show_discount' => true,
                'use_municipality_freight' => true,
            ],
        ];

        foreach ($bases as $basis) {
            PricingBasis::updateOrCreate(['slug' => $basis['slug']], $basis);
        }
    }
}
