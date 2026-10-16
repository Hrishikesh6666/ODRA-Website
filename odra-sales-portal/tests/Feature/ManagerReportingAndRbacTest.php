<?php

namespace Tests\Feature;

use App\Models\ModelPrice;
use App\Models\Quote;
use App\Models\QuoteSnapshot;
use App\Models\QuoteVersion;
use App\Models\SweeperModel;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ManagerReportingAndRbacTest extends TestCase
{
    protected function createSampleQuote(User $rep): Quote
    {
        $sweeper = SweeperModel::where('code', 'MT4H')->first();

        $quote = Quote::create([
            'user_id' => $rep->id,
            'quote_number' => 'ODRA-' . rand(100000, 999999),
            'current_version' => 1,
            'status' => 'Finalized',
        ]);

        $version = QuoteVersion::create([
            'quote_id' => $quote->id,
            'version_number' => 1,
            'status' => 'Finalized',
            'finalized_at' => now(),
        ]);

        // Place a mock PDF in local private storage
        $pdfRelativePath = 'quotes/test_' . $quote->id . '.pdf';
        Storage::disk('local')->put("private/{$pdfRelativePath}", '%PDF-1.4 test file content');

        QuoteSnapshot::create([
            'quote_version_id' => $version->id,
            'customer_company' => 'City of Miami Public Works',
            'customer_contact' => 'John Director',
            'customer_address_1' => '100 NW 1st Ave',
            'customer_address_2' => 'Miami, FL 33128',
            'model_code' => 'MT4H',
            'model_description' => 'MT4H Mechanical Broom Sweeper',
            'chassis_configuration' => 'Isuzu NRR cabover',
            'pricing_basis' => 'Sourcewell',
            'sales_rep_name' => $rep->name,
            'rep_territory' => 'Florida',
            'base_qty' => 1,
            'base_msrp' => 257493.00,
            'base_list_price' => 307378.00,
            'base_unit_price' => 270493.00,
            'show_discount_amount' => 0.00,
            'freight_amount' => 8725.00,
            'subtotal' => 270493.00,
            'total' => 279218.00,
            'pdf_path' => $pdfRelativePath,
            'pdf_generated_at' => now(),
        ]);

        return $quote;
    }

    public function test_manager_login_redirects_to_manager_dashboard(): void
    {
        $response = $this->post('/login', [
            'login' => 'alex.byczynski@odrasweeper.com',
            'password' => 'OdraManager2026!',
        ]);

        $response->assertRedirect(route('manager.dashboard'));
    }

    public function test_manager_can_access_executive_dashboard_with_financials(): void
    {
        $manager = User::where('email', 'alex.byczynski@odrasweeper.com')->first();

        $response = $this->actingAs($manager)->get(route('manager.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Commercial Overview');
        $response->assertSee('TOTAL QUOTATIONS');
        $response->assertSee('Open Draft Pipeline');
    }

    public function test_manager_can_access_reports_and_export_excel(): void
    {
        $manager = User::where('email', 'alex.byczynski@odrasweeper.com')->first();
        $rep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $this->createSampleQuote($rep);

        // Access reports screen
        $response = $this->actingAs($manager)->get(route('manager.reports.index'));
        $response->assertStatus(200);
        $response->assertSee('Commercial Reporting');
        $response->assertSee('Download Excel / CSV');

        // Download CSV export
        $exportResponse = $this->actingAs($manager)->get(route('manager.reports.export'));
        $exportResponse->assertStatus(200);
        $exportResponse->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Quote Number', $exportResponse->streamedContent());
        $this->assertStringContainsString('City of Miami Public Works', $exportResponse->streamedContent());
    }

    public function test_financial_shielding_prevents_admin_and_rep_from_exporting_reports(): void
    {
        $admin = User::where('email', 'cc@odrasweeper.com')->first();
        $rep = User::where('email', 'john.wells@odrasweeper.com')->first();

        // Technical admin is denied
        $this->actingAs($admin)->get(route('manager.reports.index'))->assertStatus(403);
        $this->actingAs($admin)->get(route('manager.reports.export'))->assertStatus(403);

        // Sales rep is denied
        $this->actingAs($rep)->get(route('manager.reports.index'))->assertStatus(403);
        $this->actingAs($rep)->get(route('manager.reports.export'))->assertStatus(403);
    }

    public function test_financial_shielding_prevents_technical_admin_from_downloading_quote_pdf(): void
    {
        $admin = User::where('email', 'cc@odrasweeper.com')->first();
        $rep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $quote = $this->createSampleQuote($rep);

        // Technical Admin cannot download PDF because it contains financial totals
        $response = $this->actingAs($admin)->get(route('sales.quotes.pdf', $quote));
        $response->assertStatus(403);
    }

    public function test_manager_and_owning_rep_can_download_quote_pdf(): void
    {
        $manager = User::where('email', 'alex.byczynski@odrasweeper.com')->first();
        $rep = User::where('email', 'john.wells@odrasweeper.com')->first();

        $quote = $this->createSampleQuote($rep);

        // Owning rep can download
        $repResponse = $this->actingAs($rep)->get(route('sales.quotes.pdf', $quote));
        $repResponse->assertStatus(200);

        // Manager can download across entire team
        $managerResponse = $this->actingAs($manager)->get(route('sales.quotes.pdf', $quote));
        $managerResponse->assertStatus(200);
    }

    public function test_rep_cannot_download_another_reps_quote_pdf(): void
    {
        $rep1 = User::where('email', 'john.wells@odrasweeper.com')->first();
        $rep2 = User::where('email', 'carlos.silva@odrasweeper.com')->first();

        $quote = $this->createSampleQuote($rep1);

        // Rep 2 tries to download Rep 1's quote - blocked by global scope (404)
        $response = $this->actingAs($rep2)->get(route('sales.quotes.pdf', $quote));
        $response->assertStatus(404);
    }

    public function test_manager_can_access_and_update_pricing_master(): void
    {
        $manager = User::where('email', 'alex.byczynski@odrasweeper.com')->first();
        $sweeper = SweeperModel::where('code', 'MT4H')->first();

        $response = $this->actingAs($manager)->get(route('manager.pricing.index'));
        $response->assertStatus(200);
        $response->assertSee('Master Pricing');

        // Test updating sweeper model pricing
        $postResponse = $this->actingAs($manager)->put(route('manager.pricing.models.update', $sweeper), [
            'msrp' => 259000.00,
            'sw_list' => 309000.00,
            'sw_net' => 272000.00,
        ]);

        $postResponse->assertRedirect(route('manager.pricing.index'));
        $this->assertDatabaseHas('model_prices', [
            'sweeper_model_id' => $sweeper->id,
            'msrp' => 259000.00,
        ]);
    }

    public function test_manager_can_access_and_update_quotation_settings_master(): void
    {
        $manager = User::where('email', 'alex.byczynski@odrasweeper.com')->first();

        // 1. Manager can access Quotation Configuration & Pricing Basis Master via manager route
        $response = $this->actingAs($manager)->get(route('manager.quotation-settings.index'));
        $response->assertStatus(200);
        $response->assertSee('Quotation Configuration & Pricing Basis Master');

        // 2. Manager sidebar contains the link
        $response->assertSee(route('manager.quotation-settings.index'));

        // 3. Manager can save an update and is redirected back to manager.quotation-settings.index
        $updateResponse = $this->actingAs($manager)->put(route('admin.quotation-settings.update'), [
            'scope' => 'MT4H',
            'model' => 'MT4H',
            'chassis_configuration' => 'MT4H - Standard Commercial Truck Chassis Configuration',
            'pricing_bases' => [
                [
                    'key' => 'msrp',
                    'label' => 'MSRP (Commercial)',
                    'msrp' => 257493.00,
                    'discount_percent' => 0.00,
                    'calculated_price' => 257493.00,
                ],
            ],
        ]);

        $updateResponse->assertRedirect(route('manager.quotation-settings.index', ['scope' => 'MT4H']));
    }

    public function test_admin_sidebar_and_pricing_console_reflect_user_specifications(): void
    {
        $admin = User::where('email', 'it.support@challengermfg.net')->first();
        $manager = User::where('email', 'alex.byczynski@odrasweeper.com')->first();

        // 1. Admin sidebar has removed Models & Master Setup and updated quotation settings title
        $adminDash = $this->actingAs($admin)->get(route('admin.dashboard'));
        $adminDash->assertStatus(200);
        $adminDash->assertDontSee('Models & Master Setup');
        $adminDash->assertSee('Quotation Configuration & Pricing Basis Master');

        // 2. Pricing console does not contain Base Equipment Master Pricing section
        $pricingConsole = $this->actingAs($manager)->get(route('manager.pricing.index'));
        $pricingConsole->assertStatus(200);
        $pricingConsole->assertDontSee('Base Equipment Master Pricing (Sweeper Units)');
        $pricingConsole->assertSee('1. Options & Enhancements Master Pricing');
        $pricingConsole->assertSee('2. Delivery & Transport Freight Pricing Matrix');
    }
}
