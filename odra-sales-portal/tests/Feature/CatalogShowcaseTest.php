<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class CatalogShowcaseTest extends TestCase
{
    public function test_models_catalog_page_is_accessible_to_guests(): void
    {
        $response = $this->get(route('models.catalog'));

        $response->assertStatus(200);
        $response->assertSee('DISCOVER ODRA MECHANICAL SWEEPERS');
        $response->assertSee('ODRA MT4H');
        $response->assertSee('ODRA MS5H');
        $response->assertSee('SIDE-BY-SIDE ENGINEERING COMPARISON');
        $response->assertSee('Isuzu NRR Cabover');
        $response->assertSee('Freightliner M2 Plus');
        $response->assertSee('Sourcewell');
    }

    public function test_sales_rep_can_access_models_showcase(): void
    {
        $rep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $response = $this->actingAs($rep)->get(route('sales.models'));

        $response->assertStatus(200);
        $response->assertSee('ODRA MT4H');
        $response->assertSee('ODRA MS5H');
        $response->assertSee('$257,493');
        $response->assertSee('$349,149');
        $response->assertSee('Configure in Quote Builder');
    }

    public function test_manager_sees_full_commercial_pricing_on_showcase(): void
    {
        $manager = User::where('email', 'alex.byczynski@odrasweeper.com')->first();

        $response = $this->actingAs($manager)->get(route('manager.models'));

        $response->assertStatus(200);
        $response->assertSee('ODRA MT4H');
        $response->assertSee('$257,493');
        $response->assertSee('Sourcewell List: $307,378');
        $response->assertSee('Net: $270,493');
    }

    public function test_technical_admin_has_financials_restricted_on_showcase(): void
    {
        $admin = User::where('email', 'hrishikesh.s@odrasweeper.com')->first();

        $response = $this->actingAs($admin)->get(route('models.catalog'));

        $response->assertStatus(200);
        $response->assertSee('ODRA MT4H');
        $response->assertSee('Technical Admin Master');
        $response->assertSee('Financials Restricted');
        $response->assertDontSee('$257,493');
    }

    public function test_quote_builder_handles_preselected_model_query_param(): void
    {
        $rep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $response = $this->actingAs($rep)->get(route('sales.quotes.create', ['model' => 'MS5H']));

        $response->assertStatus(200);
        $response->assertSee('ODRA Sales Portal');
        $response->assertSee('urlParams.has(\'model\')', false);
    }

    public function test_showcase_renders_interactive_configurators_and_upgrades(): void
    {
        $rep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $response = $this->actingAs($rep)->get(route('sales.models'));

        $response->assertStatus(200);
        // Interactive chassis selection
        $response->assertSee('selectChassis(\'MT4H\'', false);
        $response->assertSee('selectChassis(\'MS5H\'', false);
        $response->assertSee('Isuzu NRR Cabover 19,500 GVWR');
        $response->assertSee('Conventional Class 6');
        
        // Interactive procurement basis
        $response->assertSee('selectPricingBasis(\'MT4H\'', false);
        $response->assertSee('MSRP Commercial');
        $response->assertSee('Sourcewell (#093021)');
        $response->assertSee('BuyBoard');

        // Interactive factory upgrades
        $response->assertSee('toggleOption(\'MT4H\'', false);
        $response->assertSee('toggleOption(\'MS5H\'', false);
        $response->assertSee('Stainless Steel 304 Hopper Body');
        $response->assertSee('Dualized Steering Conversion');
        $response->assertSee('Elevator System High-Volume Flush');

        // Dynamic pricing & CTA buttons
        $response->assertSee('id="mt4hPriceAmount"', false);
        $response->assertSee('id="ms5hPriceAmount"', false);
        $response->assertSee('id="mt4hConfigureBtn"', false);
        $response->assertSee('id="ms5hConfigureBtn"', false);
        $response->assertSee('CARD_CONFIGS', false);
        $response->assertSee('recalculateCardPrice', false);
    }

    public function test_quote_builder_handles_full_configured_query_params(): void
    {
        $rep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $response = $this->actingAs($rep)->get(route('sales.quotes.create', [
            'model' => 'MT4H',
            'chassis' => 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper',
            'basis' => 'Sourcewell',
            'enhancements' => 'Stainless Steel Hopper ILO Standard||Rear Mounted LED Light Bar'
        ]));

        $response->assertStatus(200);
        $response->assertSee('ODRA Sales Portal');
        $response->assertSee('urlParams.has(\'chassis\')', false);
        $response->assertSee('urlParams.has(\'basis\')', false);
        $response->assertSee('urlParams.has(\'enhancements\')', false);
    }
}
