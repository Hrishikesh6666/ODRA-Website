<?php

namespace Tests\Feature;

use App\Models\SweeperModel;
use App\Models\User;
use App\Services\CatalogService;
use App\Services\QuoteTemplateService;
use Tests\TestCase;

class ModelQuotationSettingsTest extends TestCase
{
    protected function getAdmin(): User
    {
        return User::where('email', 'it.support@challengermfg.net')->first()
            ?? User::where('role_id', 1)->first();
    }

    protected function getSalesRep(): User
    {
        return User::where('email', 'john.wells@odrasweeper.com')->first()
            ?? User::where('role_id', 2)->first();
    }

    public function test_admin_can_view_quotation_settings_for_global_and_specific_models(): void
    {
        $admin = $this->getAdmin();

        $responseGlobal = $this->actingAs($admin)->get('/admin/quotation-settings?scope=GLOBAL');
        $responseGlobal->assertStatus(200);
        $responseGlobal->assertSee('Global Default Template');
        $responseGlobal->assertSee('Quotation Configuration Scope');
        $responseGlobal->assertSee('1. Quotation Header, Address &amp; Table Labels', false);

        $responseMT4H = $this->actingAs($admin)->get('/admin/quotation-settings?scope=MT4H');
        $responseMT4H->assertStatus(200);
        $responseMT4H->assertSee('Model Override:');
        $responseMT4H->assertSee('MT4H');
        $responseMT4H->assertSee('Standard Features Section for MT4H');

        $responseMS5H = $this->actingAs($admin)->get('/admin/quotation-settings?scope=MS5H');
        $responseMS5H->assertStatus(200);
        $responseMS5H->assertSee('Model Override:');
        $responseMS5H->assertSee('MS5H');
        $responseMS5H->assertSee('Standard Features Section for MS5H');
    }

    public function test_admin_can_customize_standard_features_legal_terms_and_pricing_bases_per_model(): void
    {
        $admin = $this->getAdmin();
        $templateService = app(QuoteTemplateService::class);
        $globalDefaults = $templateService->getDefaultSettings();

        // 1. Customize MS5H specific settings
        $ms5hPricingBases = $globalDefaults['pricing_bases'];
        foreach ($ms5hPricingBases as $k => $b) {
            if ($b['key'] === 'Sourcewell') {
                $ms5hPricingBases[$k]['discount_percent'] = 15.5;
                $ms5hPricingBases[$k]['subtotal_label'] = 'MS5H Sourcewell Contract Price';
            }
        }

        $payload = [
            'scope' => 'MS5H',
            'pricing_bases' => $ms5hPricingBases,
            'legal' => [
                'standard_features_p1' => '<b>MS5H Heavy Spec:</b> Single engine Cummins 300HP with 5 cu yd hopper and air brakes.',
                'standard_features_p2' => 'Full one-button automation and heavy duty 8-flight squeegee elevator.',
                'standard_features_p3' => 'Inside cab controls with color monitor and LED safety beacons.',
                'pm10_note' => '', // MS5H omits PM-10 per manufacturer design
                'legal_p1' => 'MS5H freight estimate based on Class 6 Freightliner transport.',
                'legal_p2' => 'Taxes, registration and titling are customer responsibility.',
                'legal_p3' => 'A <b>$15,000 commercial deposit</b> is required for MS5H orders.',
                'legal_p4' => 'Valid 30 days from date of issue.',
            ],
        ];

        $updateResponse = $this->actingAs($admin)->put('/admin/quotation-settings', $payload);
        $updateResponse->assertRedirect('/admin/quotation-settings?scope=MS5H');

        // Verify effective settings in QuoteTemplateService
        $ms5hSettings = $templateService->getSettings('MS5H');
        $this->assertTrue($ms5hSettings['is_custom']);
        $this->assertStringContainsString('MS5H Heavy Spec', $ms5hSettings['legal']['standard_features_p1']);
        $this->assertStringContainsString('$15,000 commercial deposit', $ms5hSettings['legal']['legal_p3']);

        $sourcewellBasis = collect($ms5hSettings['pricing_bases'])->firstWhere('key', 'Sourcewell');
        $this->assertEquals(15.5, (float) $sourcewellBasis['discount_percent']);
        $this->assertEquals('MS5H Sourcewell Contract Price', $sourcewellBasis['subtotal_label']);

        // Verify MT4H still retains its original standard features and is not affected
        $mt4hSettings = $templateService->getSettings('MT4H');
        $this->assertStringNotContainsString('MS5H Heavy Spec', $mt4hSettings['legal']['standard_features_p1']);

        // Verify CatalogService delivers model-specific templates to API & Quote Builder
        $catalogPayload = app(CatalogService::class)->getCatalogPayload();
        $this->assertArrayHasKey('model_templates', $catalogPayload);
        $this->assertArrayHasKey('MS5H', $catalogPayload['model_templates']);
        $this->assertArrayHasKey('MT4H', $catalogPayload['model_templates']);

        $ms5hTemplate = $catalogPayload['model_templates']['MS5H'];
        $this->assertTrue($ms5hTemplate['is_custom']);
        $this->assertStringContainsString('MS5H Heavy Spec', $ms5hTemplate['legal']['standard_features_p1']);
        $this->assertStringContainsString('$15,000 commercial deposit', $ms5hTemplate['legal']['legal_p3']);

        // Clean up: Reset MS5H back to factory default
        $templateService->resetModelSettings('MS5H');
    }

    public function test_admin_can_customize_settings_for_newly_added_sweeper_model(): void
    {
        $admin = $this->getAdmin();
        $salesRep = $this->getSalesRep();

        // Add a brand new sweeper model
        SweeperModel::where('code', 'MX6H')->forceDelete();
        $newModel = SweeperModel::create([
            'code' => 'MX6H',
            'description' => 'Brand New Next-Gen 6-Cubic Yard Sweeper',
            'category' => 'MECHANICAL BROOM SWEEPER',
            'is_active' => true,
        ]);

        // Visit quotation settings for the new model
        $response = $this->actingAs($admin)->get('/admin/quotation-settings?scope=MX6H');
        $response->assertStatus(200);
        $response->assertSee('Model Override:');
        $response->assertSee('MX6H');
        $response->assertSee('Currently Inheriting Global Defaults');

        // Save customized features for MX6H
        $templateService = app(QuoteTemplateService::class);
        $defaults = $templateService->getDefaultSettings();

        $payload = [
            'scope' => 'MX6H',
            'pricing_bases' => $defaults['pricing_bases'],
            'legal' => [
                'standard_features_p1' => '<b>MX6H High-Volume Sweeper:</b> 6-cubic yard hopper with electric-over-hydraulic controls.',
                'standard_features_p2' => 'Features high capacity variable height dump and dual gutter brooms.',
                'standard_features_p3' => 'Touchscreen smart cab control console with 360-degree cameras.',
                'pm10_note' => 'PM-10 Certified EPA Tier 4F.',
                'legal_p1' => 'MX6H freight terms EXW Grand Forks, ND.',
                'legal_p2' => 'Local emissions certifications are buyer responsibility.',
                'legal_p3' => 'A 20% non-refundable deposit is required at signing.',
                'legal_p4' => 'MX6H quote valid 15 calendar days.',
            ],
        ];

        $updateResp = $this->actingAs($admin)->put('/admin/quotation-settings', $payload);
        $updateResp->assertRedirect('/admin/quotation-settings?scope=MX6H');

        $mx6hSettings = $templateService->getSettings('MX6H');
        $this->assertTrue($mx6hSettings['is_custom']);
        $this->assertStringContainsString('MX6H High-Volume Sweeper', $mx6hSettings['legal']['standard_features_p1']);

        // Check that Quote Builder has MX6H in its model templates
        $builderResp = $this->actingAs($salesRep)->get('/sales/quotes/create?model=MX6H');
        $builderResp->assertStatus(200);
        $builderResp->assertSee('MX6H');
        $builderResp->assertSee('MX6H High-Volume Sweeper');

        // Clean up
        $templateService->resetModelSettings('MX6H');
        $newModel->delete();
    }

    public function test_admin_can_revert_model_overrides_to_global_defaults(): void
    {
        $admin = $this->getAdmin();
        $templateService = app(QuoteTemplateService::class);
        $defaults = $templateService->getDefaultSettings();

        // First set a custom override on MT4H
        $templateService->saveSettings([
            'pricing_bases' => $defaults['pricing_bases'],
            'legal' => [
                'standard_features_p1' => 'Custom temporary MT4H feature string',
                'standard_features_p2' => 'Custom temporary p2',
                'standard_features_p3' => 'Custom temporary p3',
                'pm10_note' => 'Custom PM10',
                'legal_p1' => 'l1',
                'legal_p2' => 'l2',
                'legal_p3' => 'l3',
                'legal_p4' => 'l4',
            ],
        ], 'MT4H');

        $this->assertTrue($templateService->getSettings('MT4H')['is_custom']);

        // Revert MT4H to global defaults
        $revertResp = $this->actingAs($admin)->post('/admin/quotation-settings/reset-model/MT4H');
        $revertResp->assertRedirect('/admin/quotation-settings?scope=MT4H');

        $refreshed = $templateService->getSettings('MT4H');
        $this->assertFalse($refreshed['is_custom']);
        $this->assertStringNotContainsString('Custom temporary MT4H feature string', $refreshed['legal']['standard_features_p1']);
    }

    public function test_sales_representative_cannot_access_or_update_quotation_settings(): void
    {
        $salesRep = $this->getSalesRep();

        $getResponse = $this->actingAs($salesRep)->get('/admin/quotation-settings');
        $getResponse->assertStatus(403);

        $putResponse = $this->actingAs($salesRep)->put('/admin/quotation-settings', [
            'scope' => 'GLOBAL',
            'legal' => ['standard_features_p1' => 'Hacked'],
        ]);
        $putResponse->assertStatus(403);
    }
}
