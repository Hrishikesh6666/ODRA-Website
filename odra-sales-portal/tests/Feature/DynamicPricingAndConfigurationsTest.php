<?php

namespace Tests\Feature;

use App\Models\Enhancement;
use App\Models\ModelConfiguration;
use App\Models\ModelPrice;
use App\Models\PricingBasis;
use App\Models\Quote;
use App\Models\SweeperModel;
use App\Models\User;
use App\Services\CatalogService;
use App\Services\QuoteService;
use Tests\TestCase;

class DynamicPricingAndConfigurationsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Quote::withoutGlobalScopes()->forceDelete();
    }

    /**
     * Requirement 1: Dynamic Pricing Table Calculation Logic
     * Unit Price = MSRP * (1 - Discount / 100)
     * Extended Price = Unit Price * Quantity
     * Example: MSRP = $307,378, Discount = 12%, Unit Price = $270,492.64, Quantity = 2, Extended = $540,985.28
     */
    public function test_dynamic_pricing_calculations_match_exact_specification(): void
    {
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();
        $this->assertNotNull($salesRep);

        $msrp = 307378.00;
        $discountPercent = 12.00;
        $quantity = 2;

        $calculatedUnitPrice = round($msrp * (1 - ($discountPercent / 100)), 2);
        $calculatedExtendedPrice = round($calculatedUnitPrice * $quantity, 2);

        $this->assertEquals(270492.64, $calculatedUnitPrice);
        $this->assertEquals(540985.28, $calculatedExtendedPrice);

        // Verify quote save and finalize with these exact figures
        $payload = [
            'quote_number' => '3088',
            'customer' => 'City of Minneapolis Public Works',
            'model_description' => "MT4H Mechanical Broom Sweeper\n[Referenced under Sourcewell Contract # 062425-ODRA]",
            'base_msrp' => $msrp,
            'base_list_price' => $msrp,
            'base_unit_price' => $calculatedUnitPrice,
            'subtotal' => $calculatedExtendedPrice,
            'total' => $calculatedExtendedPrice + 1770.00, // + MN municipality freight
            'quote_code' => 'JW011020263088',
            'state' => [
                'quoteSeq' => '3088',
                'date' => '2026-10-13',
                'model' => 'MT4H',
                'basis' => 'Sourcewell',
                'chassis' => 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper',
                'toCompany' => 'City of Minneapolis Public Works',
                'salesRep' => 'John Wells',
                'baseQty' => $quantity,
                'freight' => 1770.00,
                'enh' => [],
            ],
        ];

        $response = $this->actingAs($salesRep)->postJson('/api/quotes', $payload);
        $response->assertStatus(201);

        $quote = Quote::where('quote_number', '3088')->first();
        $this->assertNotNull($quote);
        $snapshot = $quote->latestVersion->snapshot;

        $this->assertEquals(2, $snapshot->base_qty);
        $this->assertEquals(307378.00, (float) $snapshot->base_msrp);
        $this->assertEquals(270492.64, (float) $snapshot->base_unit_price);
        $this->assertEquals(540985.28, (float) $snapshot->subtotal);

        // Finalize quote and verify normalized items
        $finalizeResp = $this->actingAs($salesRep)->postJson("/api/quotes/{$quote->id}/finalize", $payload);
        $finalizeResp->assertStatus(200);

        $baseItem = $quote->latestVersion->fresh()->items()->where('item_type', 'base_unit')->first();
        $this->assertNotNull($baseItem);
        $this->assertEquals(2, $baseItem->qty);
        $this->assertEquals(270492.64, (float) $baseItem->unit_price_at_time);
        $this->assertEquals(540985.28, (float) $baseItem->extended_price);
    }

    /**
     * Requirement 1 & 2: Commercial Pricing Basis has 0% Discount (Hidden)
     * QTY = 1, MSRP = 257,493, Discount = Hidden, Unit Price = 257,493.00, Extended = 257,493.00
     */
    public function test_commercial_pricing_basis_has_zero_discount(): void
    {
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();
        $mt4h = SweeperModel::where('code', 'MT4H')->first();
        $currentPrice = $mt4h->prices()->latest('id')->first();
        $msrp = (float) $currentPrice->msrp;

        $calculatedUnitPrice = round($msrp * (1 - (0 / 100)), 2);
        $calculatedExtendedPrice = round($calculatedUnitPrice * 1, 2);

        $this->assertEquals($msrp, $calculatedUnitPrice);
        $this->assertEquals($msrp, $calculatedExtendedPrice);

        $payload = [
            'quote_number' => '3089',
            'customer' => 'Apex Paving & Construction',
            'model_description' => $mt4h->description,
            'base_msrp' => $msrp,
            'base_list_price' => $msrp,
            'base_unit_price' => $calculatedUnitPrice,
            'subtotal' => $calculatedExtendedPrice,
            'total' => $calculatedExtendedPrice + 6015.00,
            'state' => [
                'quoteSeq' => '3089',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'chassis' => 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper',
                'toCompany' => 'Apex Paving & Construction',
                'salesRep' => 'John Wells',
                'baseQty' => 1,
                'freight' => 6015.00,
                'enh' => [],
            ],
        ];

        $response = $this->actingAs($salesRep)->postJson('/api/quotes', $payload);
        $response->assertStatus(201);

        $quote = Quote::where('quote_number', '3089')->first();
        $this->assertEquals(1, $quote->latestVersion->snapshot->base_qty);
        $this->assertEquals($msrp, (float) $quote->latestVersion->snapshot->base_unit_price);
        $this->assertEquals($msrp, (float) $quote->latestVersion->snapshot->subtotal);
    }

    /**
     * Requirement 2: Dynamic Pricing Basis Selection & Administrator Custom Categories
     */
    public function test_admin_can_add_custom_pricing_basis_with_discount_and_template(): void
    {
        $admin = User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))->first();
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();

        // 1. Admin adds a custom pricing basis
        $templateService = app(\App\Services\QuoteTemplateService::class);
        $settings = $templateService->getSettings();
        $settings['pricing_bases'][] = [
            'key' => 'Texas DIR',
            'label' => 'Texas DIR Contract',
            'slug' => 'texas_dir',
            'price_tier' => 'sw_net',
            'discount_percent' => 15.00,
            'subtotal_label' => 'Texas DIR Contract Subtotal',
            'description_template' => 'Referenced under Texas DIR Contract DIR-TSO-4299',
            'disclaimer' => 'Quoted under Texas DIR Master Agreement',
            'logo_key' => null,
            'caption' => 'Texas DIR Contract DIR-TSO-4299',
            'is_coop' => true,
            'use_municipality_freight' => true,
        ];

        $updateResp = $this->actingAs($admin)->put('/admin/quotation-settings', $settings);
        $updateResp->assertRedirect('/admin/quotation-settings');

        // 2. Verify Catalog API returns Texas DIR with 15% discount and description template
        $catalogResp = $this->actingAs($salesRep)->getJson('/api/catalog');
        $catalogResp->assertStatus(200);

        $bases = collect($catalogResp->json('pricing_bases'));
        $dirBasis = $bases->firstWhere('slug', 'texas_dir');

        $this->assertNotNull($dirBasis);
        $this->assertEquals(15.00, (float) $dirBasis['discount_percent']);
        $this->assertEquals('Referenced under Texas DIR Contract DIR-TSO-4299', $dirBasis['description_template']);

        // 3. Verify Quote Builder view renders Texas DIR option
        $builderResp = $this->actingAs($salesRep)->get('/sales/quotes/create');
        $builderResp->assertStatus(200);
        $builderResp->assertSee('Texas DIR Contract');
        $builderResp->assertSee('DIR-TSO-4299');

        // Reset settings
        $this->actingAs($admin)->post('/admin/quotation-settings/reset');
    }

    /**
     * Requirement 3: Flexible Chassis and Pickup Configurations with Price Adjustments
     */
    public function test_chassis_configuration_supports_price_adjustment_and_updates_quote(): void
    {
        $admin = User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))->first();
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();
        $mt4h = SweeperModel::where('code', 'MT4H')->first();

        // 1. Add a priced chassis configuration: Ford F-550XL with +$7,500.00 price
        $config = ModelConfiguration::updateOrCreate(
            [
                'sweeper_model_id' => $mt4h->id,
                'label' => 'MT4H - Ford F-550XL Premium Heavy-Duty Package, 19,500 GVWR',
            ],
            [
                'short_label' => 'MT4H Ford F-550XL Premium HD',
                'price' => 7500.00,
                'sort_order' => 10,
                'is_active' => true,
            ]
        );

        // 2. Catalog Service returns chassis price
        $catalogService = app(CatalogService::class);
        $payload = $catalogService->getCatalogPayload();
        $this->assertArrayHasKey('chassis_prices', $payload);
        $this->assertEquals(7500.00, $payload['chassis_prices'][$config->label]);

        // 3. Sales rep quotes with this priced chassis option
        $quotePayload = [
            'quote_number' => '3090',
            'customer' => 'Harris County Highway Dept',
            'base_msrp' => 257493.00,
            'base_list_price' => 257493.00,
            'base_unit_price' => 257493.00,
            'subtotal' => 257493.00 + 7500.00, // includes chassis upgrade
            'total' => 257493.00 + 7500.00 + 5590.00,
            'enhancements' => [
                [
                    'desc' => "Chassis Option: {$config->short_label}",
                    'category' => 'CHASSIS CONFIGURATION',
                    'item_type' => 'configuration',
                    'qty' => 1,
                    'msrp' => 7500.00,
                    'list' => 7500.00,
                    'unit' => 7500.00,
                    'extended' => 7500.00,
                ],
            ],
            'state' => [
                'quoteSeq' => '3090',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'chassis' => $config->label,
                'toCompany' => 'Harris County Highway Dept',
                'salesRep' => 'John Wells',
                'baseQty' => 1,
                'freight' => 5590.00,
                'enh' => [],
            ],
        ];

        $saveResp = $this->actingAs($salesRep)->postJson('/api/quotes', $quotePayload);
        $saveResp->assertStatus(201);

        $quote = Quote::where('quote_number', '3090')->first();
        $this->assertEquals(264993.00, (float) $quote->latestVersion->snapshot->subtotal);

        // Finalize quote and verify QuoteItem for chassis configuration
        $finalizeResp = $this->actingAs($salesRep)->postJson("/api/quotes/{$quote->id}/finalize", $quotePayload);
        $finalizeResp->assertStatus(200);

        $cfgItem = $quote->latestVersion->fresh()->items()->where('category', 'CHASSIS CONFIGURATION')->first();
        $this->assertNotNull($cfgItem);
        $this->assertEquals(7500.00, (float) $cfgItem->unit_price_at_time);
        $this->assertEquals(7500.00, (float) $cfgItem->extended_price);

        // Clean up
        $config->delete();
    }

    /**
     * Requirement 4: Editable Enhancements with Custom Discount Percent & CRUD
     */
    public function test_enhancements_support_custom_discount_percentages_and_admin_crud(): void
    {
        $admin = User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))->first();
        $mt4h = SweeperModel::where('code', 'MT4H')->first();

        // 1. Admin adds enhancement with explicit 15% discount
        $createResp = $this->actingAs($admin)->post('/admin/enhancements', [
            'sweeper_model_id' => $mt4h->id,
            'category' => 'CAMERA',
            'description' => 'Dynamic 360 AI Collision Camera System',
            'msrp' => 4000.00,
            'discount_percent' => 15.00,
            'sort_order' => 1,
        ]);
        $createResp->assertRedirect();

        $enh = Enhancement::where('description', 'Dynamic 360 AI Collision Camera System')->first();
        $this->assertNotNull($enh);
        $this->assertEquals(4000.00, (float) $enh->msrp);
        $this->assertEquals(15.00, (float) $enh->discount_percent);
        // sw_net should automatically calculate as 4000 * (1 - 0.15) = 3400.00
        $this->assertEquals(3400.00, (float) $enh->sw_net);

        // 2. Admin edits enhancement to 20% discount
        $updateResp = $this->actingAs($admin)->put("/admin/enhancements/{$enh->id}", [
            'sweeper_model_id' => $mt4h->id,
            'category' => 'CAMERA',
            'description' => 'Dynamic 360 AI Collision Camera System - Pro',
            'msrp' => 4500.00,
            'discount_percent' => 20.00,
            'sort_order' => 1,
        ]);
        $updateResp->assertRedirect();

        $enh->refresh();
        $this->assertEquals(4500.00, (float) $enh->msrp);
        $this->assertEquals(20.00, (float) $enh->discount_percent);
        // sw_net should automatically calculate as 4500 * (1 - 0.20) = 3600.00
        $this->assertEquals(3600.00, (float) $enh->sw_net);

        // 3. Admin deletes enhancement
        $deleteResp = $this->actingAs($admin)->delete("/admin/enhancements/{$enh->id}");
        $deleteResp->assertRedirect();
        $this->assertNull(Enhancement::find($enh->id));
    }

    /**
     * Requirement 5: Administration and Access Control Restrictions
     */
    public function test_sales_rep_cannot_modify_master_pricing_or_admin_catalog(): void
    {
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();
        $mt4h = SweeperModel::where('code', 'MT4H')->first();

        // Sales Rep cannot edit model master prices
        $modelResp = $this->actingAs($salesRep)->put("/admin/models/{$mt4h->id}", [
            'code' => 'MT4H',
            'category' => 'MECHANICAL BROOM SWEEPER',
            'description' => 'Hacked',
            'msrp' => 100,
            'sw_list' => 100,
            'sw_net' => 100,
        ]);
        $modelResp->assertStatus(403);

        // Sales Rep cannot create enhancements
        $enhResp = $this->actingAs($salesRep)->post('/admin/enhancements', [
            'sweeper_model_id' => $mt4h->id,
            'category' => 'LIGHTS',
            'description' => 'Unauthorized Item',
            'msrp' => 100,
        ]);
        $enhResp->assertStatus(403);

        // Sales Rep cannot edit quotation settings
        $settingsResp = $this->actingAs($salesRep)->put('/admin/quotation-settings', []);
        $settingsResp->assertStatus(403);
    }

    public function test_enhancements_preserve_individual_contract_discounts_and_do_not_inherit_machine_discount(): void
    {
        $salesRep = User::where('email', 'john.wells@odrasweeper.com')->first();
        $manager = User::where('email', 'cc@odrasweeper.com')->first();
        $mt4h = SweeperModel::where('code', 'MT4H')->first();

        // 1. Fetch catalog payload
        $catalogService = app(\App\Services\CatalogService::class);
        $payload = $catalogService->getCatalogPayload();

        // Find Rear Mounted LED Light Bar (swList: 2504, swNet: 2454, disc: 2.0%)
        $lightBar = collect($payload['items'])->firstWhere('desc', 'Rear Mounted LED Light Bar');
        $this->assertNotNull($lightBar);
        $this->assertEquals(2504.0, (float) $lightBar['swList']);
        $this->assertEquals(2454.0, (float) $lightBar['swNet']);
        $this->assertEquals(2.0, (float) $lightBar['discount_percent']);

        // Find Rear LED Strobe Light (swList: 567, swNet: 556, disc: 1.9%)
        $strobe = collect($payload['items'])->firstWhere('desc', "Rear LED Strobe Light, curbside (add'l to std street-side)");
        $this->assertNotNull($strobe);
        $this->assertEquals(567.0, (float) $strobe['swList']);
        $this->assertEquals(556.0, (float) $strobe['swNet']);
        $this->assertEquals(1.9, (float) $strobe['discount_percent']);

        // 2. Manager can update enhancement discount to 5% and net price recalculates
        $enhancement = Enhancement::where('description', 'Rear Mounted LED Light Bar')->first();
        $this->actingAs($manager)->put(route('manager.pricing.enhancements.update', $enhancement), [
            'msrp' => 2293,
            'sw_list' => 2504,
            'discount_percent' => 5.0,
        ]);

        $enhancement->refresh();
        $this->assertEquals(5.0, (float) $enhancement->discount_percent);
        $this->assertEquals(2378.80, (float) $enhancement->sw_net); // 2504 * 0.95 = 2378.80

        // Reset back to 2.0% for catalog consistency
        $enhancement->update([
            'discount_percent' => 2.0,
            'sw_net' => 2454.00,
        ]);
    }
}

