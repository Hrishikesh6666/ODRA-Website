<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PricingBasis;

class PricingBasisSeeder extends Seeder
{
    public function run(): void
    {
        $bases = [
            ['name' => 'MSRP (Commercial)', 'slug' => 'msrp', 'price_field' => 'msrp', 'subtotal_label' => 'MSRP Subtotal', 'logo_key' => null, 'show_discount' => false, 'use_municipality_freight' => false],
            ['name' => 'Sourcewell', 'slug' => 'sourcewell', 'price_field' => 'sw_net', 'subtotal_label' => 'Sourcewell Subtotal', 'logo_key' => 'sourcewell', 'show_discount' => true, 'use_municipality_freight' => true],
            ['name' => 'H-GAC / HGACBuy', 'slug' => 'hgac', 'price_field' => 'sw_net', 'subtotal_label' => 'H-GAC Subtotal', 'logo_key' => 'hgac', 'show_discount' => true, 'use_municipality_freight' => true],
            ['name' => 'State of Minnesota', 'slug' => 'minnesota', 'price_field' => 'sw_net', 'subtotal_label' => 'State Contract Subtotal', 'logo_key' => 'minnesota', 'show_discount' => true, 'use_municipality_freight' => true],
            ['name' => 'BuyBoard', 'slug' => 'buyboard', 'price_field' => 'sw_net', 'subtotal_label' => 'BuyBoard Subtotal', 'logo_key' => 'buyboard', 'show_discount' => true, 'use_municipality_freight' => true],
        ];

        foreach ($bases as $basis) {
            PricingBasis::updateOrCreate(['slug' => $basis['slug']], $basis);
        }
    }
}
