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
}
