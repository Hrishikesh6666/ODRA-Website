<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\QuoteSnapshot;
use App\Models\QuoteVersion;
use App\Models\Role;
use App\Models\SweeperModel;
use App\Models\User;
use App\Services\QuoteService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuotationRevisionsAndDashboardTest extends TestCase
{
    protected User $carlos;
    protected User $manager;
    protected User $otherRep;
    protected QuoteService $quoteService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quoteService = app(QuoteService::class);

        // Fetch or create users
        $this->carlos = User::where('email', 'carlos.silva@odrasweeper.com')->first();
        if (! $this->carlos) {
            $salesRole = Role::firstOrCreate(['slug' => 'sales'], ['name' => 'Sales Representative']);
            $this->carlos = User::create([
                'name' => 'Carlos Silva',
                'email' => 'carlos.silva@odrasweeper.com',
                'role_id' => $salesRole->id,
                'rep_code' => 'CS',
                'territory' => 'South Texas',
                'state' => 'Texas',
                'city' => 'San Antonio TX 78233',
                'is_active' => true,
            ]);
        }

        $this->manager = User::where('email', 'alex.byczynski@odrasweeper.com')->first();
        if (! $this->manager) {
            $mgrRole = Role::firstOrCreate(['slug' => 'manager'], ['name' => 'Commercial Manager']);
            $this->manager = User::create([
                'name' => 'Alex Byczynski',
                'email' => 'alex.byczynski@odrasweeper.com',
                'role_id' => $mgrRole->id,
                'is_active' => true,
            ]);
        }

        $this->otherRep = User::where('email', 'john.wells@odrasweeper.com')->first();
        if (! $this->otherRep) {
            $salesRole = Role::firstOrCreate(['slug' => 'sales'], ['name' => 'Sales Representative']);
            $this->otherRep = User::create([
                'name' => 'John Wells',
                'email' => 'john.wells@odrasweeper.com',
                'role_id' => $salesRole->id,
                'rep_code' => 'JW',
                'territory' => 'Georgia',
                'state' => 'Georgia',
                'city' => 'Cartersville GA 30121',
                'is_active' => true,
            ]);
        }
    }

    /**
     * Helper to create or finalize a revision with an explicit amount and customer.
     */
    protected function createOrFinalizeRevision(Quote $quote, int $versionNumber, float $total, string $status = 'Finalized', string $company = 'XYZ Company'): QuoteVersion
    {
        $version = $quote->versions()->where('version_number', $versionNumber)->first();
        if (! $version) {
            $version = QuoteVersion::create([
                'quote_id' => $quote->id,
                'version_number' => $versionNumber,
                'status' => $status,
                'finalized_at' => $status === 'Finalized' ? now() : null,
            ]);
        } else {
            $version->update([
                'status' => $status,
                'finalized_at' => $status === 'Finalized' ? now() : null,
            ]);
        }

        $pdfPath = "quotes/{$quote->user_id}/Q-{$quote->quote_number}_v{$versionNumber}.pdf";
        if ($status === 'Finalized') {
            Storage::disk('local')->put("private/{$pdfPath}", "%PDF-1.4 mock content for Rev {$versionNumber}");
        }

        QuoteSnapshot::updateOrCreate(
            ['quote_version_id' => $version->id],
            [
                'customer_company' => $company,
                'customer_contact' => 'Purchasing Manager',
                'customer_address_1' => '100 Main St',
                'customer_address_2' => 'San Antonio, TX 78201',
                'model_code' => 'MT4H',
                'model_description' => 'MT4H Mechanical Broom Sweeper',
                'chassis_configuration' => 'Isuzu NRR cabover',
                'pricing_basis' => 'MSRP (Commercial)',
                'sales_rep_name' => $quote->user->name,
                'rep_territory' => 'Texas',
                'base_qty' => 1,
                'base_msrp' => $total - 5000,
                'base_list_price' => $total - 5000,
                'base_unit_price' => $total - 5000,
                'show_discount_amount' => 0.00,
                'freight_amount' => 5000.00,
                'subtotal' => $total - 5000,
                'total' => $total,
                'full_state' => [
                    'toCompany' => $company,
                    'salesRep' => $quote->user->name,
                    'date' => now()->format('Y-m-d'),
                ],
                'pdf_path' => $status === 'Finalized' ? $pdfPath : null,
                'pdf_generated_at' => $status === 'Finalized' ? now() : null,
            ]
        );

        $quote->update([
            'current_version' => $versionNumber,
            'status' => $status,
        ]);

        return $version;
    }

    /**
     * Test the exact sequence requested by the user:
     * Carlos prepares quotations for XYZ Company under Q-1001:
     * 1. Rev 1 finalized for $300,000 -> Dashboard amount: $300,000
     * 2. Rev 2 created as draft for $325,000 -> Dashboard amount: $300,000
     * 3. Rev 2 finalized for $325,000 -> Dashboard amount: $325,000
     * 4. Rev 3 created as draft for $340,000 -> Dashboard amount: $325,000
     * 5. Rev 3 finalized for $340,000 -> Dashboard amount: $340,000
     * All 3 finalized revisions remain available in history, but dashboard counts $340,000 once.
     */
    public function test_carlos_revision_progression_and_dashboard_calculations(): void
    {
        // Clean any existing quotes for Carlos to isolate this test sequence
        Quote::withoutGlobalScopes()->where('user_id', $this->carlos->id)->forceDelete();

        $initialSummary = $this->quoteService->getQuotationSummary();
        $baseFinalizedVal = (float) $initialSummary['finalizedValue'];
        $baseQuotesCount = (int) $initialSummary['finalizedQuotesCount'];

        $quote = Quote::create([
            'user_id' => $this->carlos->id,
            'quote_number' => '1001',
            'current_version' => 1,
            'status' => 'Draft',
        ]);

        // ---------------------------------------------------------------------
        // Step 1: Revision 1 finalized for $300,000 -> Manager dashboard amount: $300,000
        // ---------------------------------------------------------------------
        $this->createOrFinalizeRevision($quote, 1, 300000.00, 'Finalized');

        $summary = $this->quoteService->getQuotationSummary();
        $this->assertEquals($baseFinalizedVal + 300000.00, $summary['finalizedValue'], 'Step 1: Dashboard amount should reflect +$300,000');
        $this->assertEquals($baseQuotesCount + 1, $summary['finalizedQuotesCount']);

        // Verify Carlos's sales rep leaderboard amount
        $carlosFinalizedVal = (float) $this->quoteService->latestFinalizedVersionsQuery()
            ->where('quotes.user_id', $this->carlos->id)
            ->sum('quote_snapshots.total');
        $this->assertEquals(300000.00, $carlosFinalizedVal);

        // ---------------------------------------------------------------------
        // Step 2: Revision 2 created as draft for $325,000 -> Manager dashboard amount: $300,000
        // ---------------------------------------------------------------------
        $this->quoteService->createRevision($quote, $this->carlos);
        $quote->refresh();
        $this->assertEquals(2, $quote->current_version);
        $this->assertEquals('Draft', $quote->status);

        // Update draft revision 2 to $325,000
        $this->createOrFinalizeRevision($quote, 2, 325000.00, 'Draft');

        // Revision 1 MUST remain permanently marked 'Finalized'
        $rev1 = $quote->versions()->where('version_number', 1)->first();
        $this->assertEquals('Finalized', $rev1->status, 'Revision 1 status must remain Finalized');
        $this->assertEquals(300000.00, (float) $rev1->snapshot->total);

        // Manager dashboard MUST STILL count $300,000 (A newer draft must NOT replace previous finalized revision)
        $summary = $this->quoteService->getQuotationSummary();
        $this->assertEquals($baseFinalizedVal + 300000.00, $summary['finalizedValue'], 'Step 2: Dashboard amount should remain +$300,000 while Rev 2 is draft');

        $carlosFinalizedVal = (float) $this->quoteService->latestFinalizedVersionsQuery()
            ->where('quotes.user_id', $this->carlos->id)
            ->sum('quote_snapshots.total');
        $this->assertEquals(300000.00, $carlosFinalizedVal);

        // ---------------------------------------------------------------------
        // Step 3: Revision 2 finalized for $325,000 -> Manager dashboard amount: $325,000
        // ---------------------------------------------------------------------
        $this->createOrFinalizeRevision($quote, 2, 325000.00, 'Finalized');

        $summary = $this->quoteService->getQuotationSummary();
        $this->assertEquals($baseFinalizedVal + 325000.00, $summary['finalizedValue'], 'Step 3: Dashboard amount should now reflect +$325,000');
        $this->assertEquals($baseQuotesCount + 1, $summary['finalizedQuotesCount']);

        $carlosFinalizedVal = (float) $this->quoteService->latestFinalizedVersionsQuery()
            ->where('quotes.user_id', $this->carlos->id)
            ->sum('quote_snapshots.total');
        $this->assertEquals(325000.00, $carlosFinalizedVal);

        // ---------------------------------------------------------------------
        // Step 4: Revision 3 created as draft for $340,000 -> Manager dashboard amount: $325,000
        // ---------------------------------------------------------------------
        $this->quoteService->createRevision($quote, $this->carlos);
        $quote->refresh();
        $this->assertEquals(3, $quote->current_version);
        $this->assertEquals('Draft', $quote->status);

        $this->createOrFinalizeRevision($quote, 3, 340000.00, 'Draft');

        // Both Rev 1 and Rev 2 remain permanently Finalized
        $this->assertEquals('Finalized', $quote->versions()->where('version_number', 1)->first()->status);
        $this->assertEquals('Finalized', $quote->versions()->where('version_number', 2)->first()->status);

        // Dashboard remains $325,000 while Rev 3 is a draft
        $summary = $this->quoteService->getQuotationSummary();
        $this->assertEquals($baseFinalizedVal + 325000.00, $summary['finalizedValue'], 'Step 4: Dashboard amount should remain +$325,000 while Rev 3 is draft');

        $carlosFinalizedVal = (float) $this->quoteService->latestFinalizedVersionsQuery()
            ->where('quotes.user_id', $this->carlos->id)
            ->sum('quote_snapshots.total');
        $this->assertEquals(325000.00, $carlosFinalizedVal);

        // ---------------------------------------------------------------------
        // Step 5: Revision 3 finalized for $340,000 -> Manager dashboard amount: $340,000
        // ---------------------------------------------------------------------
        $this->createOrFinalizeRevision($quote, 3, 340000.00, 'Finalized');

        $summary = $this->quoteService->getQuotationSummary();
        $this->assertEquals($baseFinalizedVal + 340000.00, $summary['finalizedValue'], 'Step 5: Dashboard amount should now reflect +$340,000');
        $this->assertEquals($baseQuotesCount + 1, $summary['finalizedQuotesCount'], 'Must count Q-1001 ONCE');

        $carlosFinalizedVal = (float) $this->quoteService->latestFinalizedVersionsQuery()
            ->where('quotes.user_id', $this->carlos->id)
            ->sum('quote_snapshots.total');
        $this->assertEquals(340000.00, $carlosFinalizedVal, 'Carlos leaderboard should count $340,000 once');

        // Verify that all 3 finalized revisions remain preserved permanently
        $allVersions = $quote->versions()->orderBy('version_number')->get();
        $this->assertCount(3, $allVersions);
        $this->assertEquals('Finalized', $allVersions[0]->status);
        $this->assertEquals(300000.00, (float) $allVersions[0]->snapshot->total);
        $this->assertEquals('Finalized', $allVersions[1]->status);
        $this->assertEquals(325000.00, (float) $allVersions[1]->snapshot->total);
        $this->assertEquals('Finalized', $allVersions[2]->status);
        $this->assertEquals(340000.00, (float) $allVersions[2]->snapshot->total);

        // Test Manager Dashboard HTTP response
        $response = $this->actingAs($this->manager)->get(route('manager.dashboard'));
        $response->assertStatus(200);
        $response->assertSee('Latest Finalized Quotation Value');
        $response->assertSee('340,000');
    }

    /**
     * Test separate purchase requests from the same company count independently.
     */
    public function test_separate_purchase_requests_from_same_company_count_independently(): void
    {
        Quote::withoutGlobalScopes()->where('user_id', $this->carlos->id)->forceDelete();

        $initialSummary = $this->quoteService->getQuotationSummary();
        $baseFinalizedVal = (float) $initialSummary['finalizedValue'];
        $baseQuotesCount = (int) $initialSummary['finalizedQuotesCount'];

        // Quotation group 1 for XYZ Company (Q-1001: Rev 1 $300k, Rev 2 $340k finalized)
        $quote1 = Quote::create([
            'user_id' => $this->carlos->id,
            'quote_number' => '1001',
            'current_version' => 1,
            'status' => 'Finalized',
        ]);
        $this->createOrFinalizeRevision($quote1, 1, 300000.00, 'Finalized', 'XYZ Company');
        $this->createOrFinalizeRevision($quote1, 2, 340000.00, 'Finalized', 'XYZ Company');

        // Quotation group 2 for XYZ Company (Q-1002: Rev 1 $200k finalized)
        $quote2 = Quote::create([
            'user_id' => $this->carlos->id,
            'quote_number' => '1002',
            'current_version' => 1,
            'status' => 'Finalized',
        ]);
        $this->createOrFinalizeRevision($quote2, 1, 200000.00, 'Finalized', 'XYZ Company');

        $summary = $this->quoteService->getQuotationSummary();
        // $340,000 (from Q-1001) + $200,000 (from Q-1002) = $540,000
        $this->assertEquals($baseFinalizedVal + 540000.00, $summary['finalizedValue']);
        $this->assertEquals($baseQuotesCount + 2, $summary['finalizedQuotesCount']);
    }

    /**
     * Test finalized revisions are read-only and preserved permanently.
     */
    public function test_finalized_revisions_are_preserved_permanently_and_read_only(): void
    {
        $quote = Quote::create([
            'user_id' => $this->carlos->id,
            'quote_number' => '2001',
            'current_version' => 1,
            'status' => 'Finalized',
        ]);
        $this->createOrFinalizeRevision($quote, 1, 280000.00, 'Finalized');

        // Attempting to update a finalized quote directly via service throws exception
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Finalized quotations cannot be edited directly');

        $this->quoteService->updateDraft($quote, [
            'total' => 999999.00,
            'state' => ['toCompany' => 'Hacked Company'],
        ]);
    }

    /**
     * Test that quotes with finalized revisions can never be deleted.
     */
    public function test_quotes_with_finalized_revisions_cannot_be_deleted(): void
    {
        $quote = Quote::create([
            'user_id' => $this->carlos->id,
            'quote_number' => '2002',
            'current_version' => 1,
            'status' => 'Finalized',
        ]);
        $this->createOrFinalizeRevision($quote, 1, 250000.00, 'Finalized');

        // Carlos attempts to delete finalized quote
        $response = $this->actingAs($this->carlos)->deleteJson("/api/quotes/{$quote->id}");
        $response->assertStatus(403);

        // Manager also cannot delete finalized quotations (policy enforcement)
        $responseMgr = $this->actingAs($this->manager)->deleteJson("/api/quotes/{$quote->id}");
        $responseMgr->assertStatus(403);
    }

    /**
     * Test View Revision History endpoint and access permissions.
     */
    public function test_view_revision_history_api_and_permissions(): void
    {
        $quote = Quote::create([
            'user_id' => $this->carlos->id,
            'quote_number' => (string) rand(100000, 999999),
            'current_version' => 2,
            'status' => 'Finalized',
        ]);
        $this->createOrFinalizeRevision($quote, 1, 300000.00, 'Finalized', 'City of Austin');
        $this->createOrFinalizeRevision($quote, 2, 325000.00, 'Finalized', 'City of Austin');

        // 1. Carlos can view revision history for his own quote
        $response = $this->actingAs($this->carlos)->getJson("/api/quotes/{$quote->id}/revisions");
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'revisions');
        $response->assertJsonPath('revisions.0.version_number', 2);
        $response->assertJsonPath('revisions.0.total', 325000);
        $response->assertJsonPath('revisions.1.version_number', 1);
        $response->assertJsonPath('revisions.1.total', 300000);

        // 2. Manager can view revision history
        $responseMgr = $this->actingAs($this->manager)->getJson("/api/quotes/{$quote->id}/revisions");
        $responseMgr->assertStatus(200);

        // 3. Other sales rep CANNOT view Carlos's revision history (denied by policy or tenant scope)
        $responseOther = $this->actingAs($this->otherRep)->getJson("/api/quotes/{$quote->id}/revisions");
        $this->assertTrue(in_array($responseOther->status(), [403, 404]), 'Unauthorized sales rep must be denied access with 403 or 404');
    }

    /**
     * Test viewing a specific historical revision in the builder in read-only mode.
     */
    public function test_viewing_historical_revision_in_builder_is_read_only(): void
    {
        $quote = Quote::create([
            'user_id' => $this->carlos->id,
            'quote_number' => '4001',
            'current_version' => 2,
            'status' => 'Draft',
        ]);
        $this->createOrFinalizeRevision($quote, 1, 300000.00, 'Finalized', 'Dallas County');
        $this->createOrFinalizeRevision($quote, 2, 320000.00, 'Draft', 'Dallas County');

        // Open Revision 1 specifically
        $response = $this->actingAs($this->carlos)->get(route('sales.quotes.show', ['quote' => $quote, 'version' => 1]));
        $response->assertStatus(200);
        $response->assertSee('Viewing Finalized Quotation Revision 1 (Read-Only)');
        $response->assertSee('Create Revised Quotation');
        $response->assertViewHas('isReadonly', true);
        $response->assertViewHas('version', 1);
    }

    /**
     * Test historical PDF download per revision.
     */
    public function test_historical_pdf_download_per_revision(): void
    {
        $quote = Quote::create([
            'user_id' => $this->carlos->id,
            'quote_number' => '5001',
            'current_version' => 2,
            'status' => 'Finalized',
        ]);
        $this->createOrFinalizeRevision($quote, 1, 300000.00, 'Finalized');
        $this->createOrFinalizeRevision($quote, 2, 325000.00, 'Finalized');

        // Download Rev 1 PDF
        $res1 = $this->actingAs($this->carlos)->get(route('sales.quotes.pdf', ['quote' => $quote, 'version' => 1]));
        $res1->assertStatus(200);
        $this->assertStringContainsString('ODRA_Quotation_5001_v1.pdf', $res1->headers->get('Content-Disposition'));

        // Download Rev 2 PDF
        $res2 = $this->actingAs($this->carlos)->get(route('sales.quotes.pdf', ['quote' => $quote, 'version' => 2]));
        $res2->assertStatus(200);
        $this->assertStringContainsString('ODRA_Quotation_5001_v2.pdf', $res2->headers->get('Content-Disposition'));
    }
}
