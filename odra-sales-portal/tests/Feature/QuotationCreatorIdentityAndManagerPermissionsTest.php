<?php

namespace Tests\Feature;

use App\Models\FreightRule;
use App\Models\ModelPrice;
use App\Models\Quote;
use App\Models\QuoteSnapshot;
use App\Models\QuoteVersion;
use App\Models\Role;
use App\Models\SweeperModel;
use App\Models\User;
use App\Services\QuoteService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class QuotationCreatorIdentityAndManagerPermissionsTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseTransactions;

    protected User $carlos;
    protected User $john;
    protected User $manager;
    protected User $admin;
    protected QuoteService $quoteService;

    protected function setUp(): void
    {
        parent::setUp();

        Quote::withoutGlobalScopes()->whereIn('quote_number', ['3001', '3002', '3003', '3004', '3005', '3006', '3008', '3009', '7001'])->forceDelete();

        $this->quoteService = app(QuoteService::class);

        $salesRole = Role::firstOrCreate(['slug' => 'sales'], ['name' => 'Sales Representative']);
        $mgrRole = Role::firstOrCreate(['slug' => 'manager'], ['name' => 'Commercial Manager']);
        $adminRole = Role::firstOrCreate(['slug' => 'admin'], ['name' => 'Administrator']);

        $this->carlos = User::firstOrCreate(
            ['email' => 'carlos.silva@odrasweeper.com'],
            [
                'name' => 'Carlos Silva',
                'role_id' => $salesRole->id,
                'rep_code' => 'CS',
                'territory' => 'South Texas',
                'state' => 'Texas',
                'city' => 'San Antonio TX 78233',
                'is_active' => true,
            ]
        );

        $this->john = User::firstOrCreate(
            ['email' => 'john.wells@odrasweeper.com'],
            [
                'name' => 'John Wells',
                'role_id' => $salesRole->id,
                'rep_code' => 'JW',
                'territory' => 'Georgia',
                'state' => 'Georgia',
                'city' => 'Cartersville GA 30121',
                'is_active' => true,
            ]
        );

        $this->manager = User::firstOrCreate(
            ['email' => 'alex.byczynski@odrasweeper.com'],
            [
                'name' => 'Alex Byczynski',
                'role_id' => $mgrRole->id,
                'is_active' => true,
            ]
        );

        $this->admin = User::firstOrCreate(
            ['email' => 'it.support@challengermfg.net'],
            [
                'name' => 'HQ Administrator',
                'role_id' => $adminRole->id,
                'is_active' => true,
            ]
        );
    }

    protected function createSampleQuote(User $creator, string $quoteNumber = '3001', string $status = 'Finalized'): Quote
    {
        $quote = Quote::create([
            'user_id' => $creator->id,
            'quote_number' => $quoteNumber,
            'current_version' => 1,
            'status' => $status,
        ]);

        $version = QuoteVersion::create([
            'quote_id' => $quote->id,
            'version_number' => 1,
            'status' => $status,
            'finalized_at' => $status === 'Finalized' ? now() : null,
        ]);

        QuoteSnapshot::create([
            'quote_version_id' => $version->id,
            'customer_company' => 'City of Miami Public Works',
            'customer_contact' => 'Purchasing Officer',
            'model_code' => 'MT4H',
            'model_description' => 'MT4H Mechanical Broom Sweeper',
            'chassis_configuration' => 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper',
            'pricing_basis' => 'MSRP (Commercial)',
            'sales_rep_name' => $creator->name,
            'rep_territory' => 'San Antonio, Texas',
            'fob_terms' => 'Transport, estimate only noted in quote',
            'base_qty' => 1,
            'base_msrp' => 257493.00,
            'base_list_price' => 257493.00,
            'base_unit_price' => 257493.00,
            'show_discount_amount' => 0.00,
            'freight_amount' => 6250.00,
            'subtotal' => 257493.00,
            'total' => 263743.00,
            'enhancement_pricing_mode' => 'itemized',
            'pdf_path' => "quotes/{$creator->id}/Q-{$quoteNumber}_v1.pdf",
            'full_state' => [
                'quoteSeq' => $quoteNumber,
                'salesRep' => $creator->name,
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'toCompany' => 'City of Miami Public Works',
                'enhancementPricingMode' => 'itemized',
            ],
        ]);

        if ($status === 'Finalized') {
            Storage::disk('local')->put("private/quotes/{$creator->id}/Q-{$quoteNumber}_v1.pdf", "%PDF-1.4 Mock Content");
        }

        return $quote->load(['versions.snapshot', 'user']);
    }

    /**
     * Requirement 1 & 4: Manager opening a salesperson's quotation sees original creator name and read-only UI.
     */
    public function test_manager_viewing_salesperson_quotation_preserves_creator_name_and_shows_readonly_ui(): void
    {
        $quote = $this->createSampleQuote($this->carlos, '3001', 'Finalized');

        $response = $this->actingAs($this->manager)->get(route('sales.quotes.show', $quote));

        $response->assertStatus(200);

        // 1. Creator name is Carlos Silva, NOT the manager Alex Byczynski
        $response->assertViewHas('quoteOwnerName', 'Carlos Silva');
        $response->assertSee('Carlos Silva');
        $initialState = $response->viewData('initialState');
        $this->assertEquals('Carlos Silva', $initialState['salesRep']);

        // 2. View flags enforce read-only access for manager
        $response->assertViewHas('isReadonly', true);
        $response->assertViewHas('isManagerViewingOther', true);
        $response->assertViewHas('canRevise', false);
        $response->assertViewHas('canModify', false);

        // 3. Manager read-only banner is displayed, identifying original creator
        $response->assertSee('Viewing Quotation Revision 1 &middot; Created by Carlos Silva (Read-Only)', false);
        $response->assertSee('This quotation belongs to <strong>Carlos Silva</strong>', false);

        // 4. "Create Revised Quotation", "Save Draft", and "Finalize Quote" are hidden from manager
        $response->assertDontSee('id="btnRevise"', false);
        $response->assertDontSee('id="btnSaveDraft"', false);
        $response->assertDontSee('id="btnFinalize"', false);
    }

    /**
     * Requirement 4 & 5: Manager modification requests are rejected by server (PUT /api/quotes/{id} returns 403).
     */
    public function test_manager_cannot_modify_salesperson_draft_via_api(): void
    {
        $quote = $this->createSampleQuote($this->carlos, '3002', 'Draft');

        $payload = [
            'quote_number' => '3002',
            'customer' => 'Malicious Hijack Attempt',
            'state' => [
                'quoteSeq' => '3002',
                'toCompany' => 'Malicious Hijack Attempt',
                'model' => 'MT4H',
                'salesRep' => $this->manager->name,
            ],
        ];

        $response = $this->actingAs($this->manager)->putJson("/api/quotes/{$quote->id}", $payload);

        $response->assertStatus(403);

        // Verify quote in DB remained unchanged
        $quote->refresh();
        $this->assertEquals($this->carlos->id, $quote->user_id);
        $this->assertEquals('City of Miami Public Works', $quote->latestVersion->snapshot->customer_company);
        $this->assertEquals('Carlos Silva', $quote->latestVersion->snapshot->sales_rep_name);
    }

    /**
     * Requirement 4 & 5: Manager cannot finalize a salesperson's draft quotation (POST /api/quotes/{id}/finalize returns 403).
     */
    public function test_manager_cannot_finalize_salesperson_draft_via_api(): void
    {
        $quote = $this->createSampleQuote($this->carlos, '3003', 'Draft');

        $response = $this->actingAs($this->manager)->postJson("/api/quotes/{$quote->id}/finalize", [
            'state' => ['quoteSeq' => '3003', 'model' => 'MT4H'],
        ]);

        $response->assertStatus(403);

        $quote->refresh();
        $this->assertEquals('Draft', $quote->status);
    }

    /**
     * Requirement 4 & 5: Manager cannot create a revision of a salesperson's quotation (POST /api/quotes/{id}/revise returns 403).
     */
    public function test_manager_cannot_revise_salesperson_quotation_via_api(): void
    {
        $quote = $this->createSampleQuote($this->carlos, '3004', 'Finalized');

        $response = $this->actingAs($this->manager)->postJson("/api/quotes/{$quote->id}/revise");

        $response->assertStatus(403);

        $quote->refresh();
        $this->assertEquals(1, $quote->versions()->count());
    }

    /**
     * Requirement 4 & 5: Manager cannot delete a salesperson's quotation (DELETE /api/quotes/{id} returns 403).
     */
    public function test_manager_cannot_delete_salesperson_quotation(): void
    {
        $quote = $this->createSampleQuote($this->carlos, '3005', 'Draft');

        $response = $this->actingAs($this->manager)->deleteJson("/api/quotes/{$quote->id}");

        $response->assertStatus(403);

        $this->assertDatabaseHas('quotes', ['id' => $quote->id, 'deleted_at' => null]);
    }

    /**
     * Requirement 3: Salesperson isolation - Salespeople cannot access another salesperson's quotation.
     */
    public function test_salesperson_cannot_access_other_salesperson_quotation(): void
    {
        $quote = $this->createSampleQuote($this->carlos, '3006', 'Finalized');

        // Web view
        $responseWeb = $this->actingAs($this->john)->get(route('sales.quotes.show', $quote));
        $this->assertTrue(in_array($responseWeb->status(), [403, 404]));

        // API inspect
        $responseApi = $this->actingAs($this->john)->getJson("/api/quotes/{$quote->id}");
        $this->assertTrue(in_array($responseApi->status(), [403, 404]));

        // PDF download
        $responsePdf = $this->actingAs($this->john)->get(route('sales.quotes.pdf', $quote));
        $this->assertTrue(in_array($responsePdf->status(), [403, 404]));

        // Revision creation
        $responseRev = $this->actingAs($this->john)->postJson("/api/quotes/{$quote->id}/revise");
        $this->assertTrue(in_array($responseRev->status(), [403, 404]));
    }

    /**
     * Requirement 1 & 4: Commercial Manager can still create and manage their own quotations.
     */
    public function test_manager_can_create_and_manage_own_quotation(): void
    {
        $managerQuote = $this->createSampleQuote($this->manager, '7001', 'Draft');

        // Manager can update own draft
        $responseUpdate = $this->actingAs($this->manager)->putJson("/api/quotes/{$managerQuote->id}", [
            'customer' => 'Manager Direct Deal',
            'state' => [
                'quoteSeq' => '7001',
                'model' => 'MT4H',
                'fob' => 'EXW, Factory pick-up',
                'toCompany' => 'Manager Direct Deal',
            ],
        ]);
        $responseUpdate->assertStatus(200);

        // Manager can finalize own draft
        $responseFinalize = $this->actingAs($this->manager)->postJson("/api/quotes/{$managerQuote->id}/finalize", [
            'customer' => 'Manager Direct Deal',
            'state' => [
                'quoteSeq' => '7001',
                'model' => 'MT4H',
                'fob' => 'EXW, Factory pick-up',
                'toCompany' => 'Manager Direct Deal',
            ],
        ]);
        $responseFinalize->assertStatus(200);

        // Manager can revise own finalized quote
        $responseRevise = $this->actingAs($this->manager)->postJson("/api/quotes/{$managerQuote->id}/revise");
        $responseRevise->assertStatus(200);

        $managerQuote->refresh();
        $this->assertEquals(2, $managerQuote->versions()->count());
    }

    /**
     * Requirement 1 & 2: Creator identity is immutable and cannot be overwritten by submitted form data.
     */
    public function test_creator_identity_cannot_be_overwritten_via_payload(): void
    {
        $quote = $this->createSampleQuote($this->carlos, '3008', 'Draft');

        $response = $this->actingAs($this->carlos)->putJson("/api/quotes/{$quote->id}", [
            'customer' => 'Testing Immutability',
            'sales_rep_name' => 'Forged Identity',
            'user_id' => $this->john->id,
            'state' => [
                'quoteSeq' => '3008',
                'model' => 'MT4H',
                'salesRep' => 'Forged Identity',
            ],
        ]);

        $response->assertStatus(200);

        $quote->refresh();
        $this->assertEquals($this->carlos->id, $quote->user_id);
        $this->assertEquals('Carlos Silva', $quote->latestVersion->snapshot->sales_rep_name);
        $this->assertEquals('Carlos Silva', $quote->latestVersion->snapshot->full_state['salesRep']);
    }

    /**
     * Requirement 4: Manager API inspection returns can_edit=false and can_revise=false for salesperson quotes.
     */
    public function test_manager_api_inspection_reports_correct_read_only_permissions(): void
    {
        $quote = $this->createSampleQuote($this->carlos, '3009', 'Finalized');

        // Inspection API
        $responseShow = $this->actingAs($this->manager)->getJson("/api/quotes/{$quote->id}");
        $responseShow->assertStatus(200);
        $responseShow->assertJsonPath('is_readonly', true);
        $responseShow->assertJsonPath('can_edit', false);
        $responseShow->assertJsonPath('can_revise', false);
        $responseShow->assertJsonPath('owner_name', 'Carlos Silva');
        $responseShow->assertJsonPath('state.salesRep', 'Carlos Silva');

        // Revisions API
        $responseRev = $this->actingAs($this->manager)->getJson("/api/quotes/{$quote->id}/revisions");
        $responseRev->assertStatus(200);
        $responseRev->assertJsonPath('can_revise', false);
        $responseRev->assertJsonPath('can_edit', false);
    }
}
