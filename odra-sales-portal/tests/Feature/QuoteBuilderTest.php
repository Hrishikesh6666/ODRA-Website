<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use App\Services\QuoteService;
use Tests\TestCase;

class QuoteBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Quote::withoutGlobalScopes()->forceDelete();
    }

    public function test_catalog_api_returns_complete_payload(): void
    {
        $user = User::where('email', 'john.wells@odrasweeper.com')->first();

        $response = $this->actingAs($user)->getJson('/api/catalog');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'items',
            'chassis',
            'chassis_short',
            'freight',
            'pricing_bases',
            'territories',
            'current_user' => [
                'id',
                'name',
                'email',
                'rep_code',
                'is_admin',
            ],
        ]);
        $this->assertNotEmpty($response->json('items'));
        $this->assertNotEmpty($response->json('freight'));
        $this->assertNotEmpty($response->json('pricing_bases'));
    }

    public function test_sales_rep_can_create_and_save_draft_quote(): void
    {
        $user = User::where('email', 'john.wells@odrasweeper.com')->first();

        $payload = [
            'quote_number' => '3001',
            'customer' => 'Acme Road Maintenance',
            'base_msrp' => 257493,
            'base_list_price' => 257493,
            'base_unit_price' => 257493,
            'subtotal' => 257493,
            'total' => 263508,
            'state' => [
                'quoteSeq' => '3001',
                'date' => '2026-10-01',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'chassis' => 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper',
                'toCompany' => 'Acme Road Maintenance',
                'toContact' => 'John Doe',
                'salesRep' => 'John Wells',
                'baseQty' => 1,
                'freight' => 6015,
                'enh' => [],
            ],
        ];

        $response = $this->actingAs($user)->postJson('/api/quotes', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('quotes', [
            'quote_number' => '3001',
            'user_id' => $user->id,
            'status' => 'Draft',
        ]);
        $this->assertDatabaseHas('quote_snapshots', [
            'customer_company' => 'Acme Road Maintenance',
            'model_code' => 'MT4H',
        ]);
    }

    public function test_sales_rep_can_finalize_quote_and_freeze_snapshot(): void
    {
        $user = User::where('email', 'john.wells@odrasweeper.com')->first();
        $quoteService = app(QuoteService::class);
        $quote = $quoteService->createDraft($user, [
            'quote_number' => '3002',
            'customer' => 'Test Finalize Corp',
            'state' => [
                'quoteSeq' => '3002',
                'toCompany' => 'Test Finalize Corp',
                'model' => 'MT4H',
                'salesRep' => 'John Wells',
            ],
        ]);

        $payload = [
            'customer' => 'Acme Road Maintenance Final',
            'base_msrp' => 257493,
            'base_list_price' => 257493,
            'base_unit_price' => 257493,
            'subtotal' => 260315,
            'total' => 266330,
            'state' => [
                'quoteSeq' => '3002',
                'date' => '2026-10-01',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'chassis' => 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper',
                'toCompany' => 'Acme Road Maintenance Final',
                'salesRep' => 'John Wells',
                'baseQty' => 1,
                'freight' => 6015,
                'enh' => [
                    ['desc' => 'Front LED Strobe Light (behind cab, visible from front)', 'qty' => 1, 'unit' => 529, 'msrp' => 529, 'list' => 529],
                    ['desc' => 'Rear Mounted LED Light Bar', 'qty' => 1, 'unit' => 2293, 'msrp' => 2293, 'list' => 2293],
                ],
            ],
        ];

        $response = $this->actingAs($user)->postJson("/api/quotes/{$quote->id}/finalize", $payload);

        $response->assertStatus(200);
        $this->assertDatabaseHas('quotes', [
            'id' => $quote->id,
            'status' => 'Finalized',
        ]);
        $this->assertDatabaseHas('quote_versions', [
            'quote_id' => $quote->id,
            'status' => 'Finalized',
        ]);
        $this->assertDatabaseHas('quote_items', [
            'description' => 'Front LED Strobe Light (behind cab, visible from front)',
            'unit_price_at_time' => 529,
        ]);
    }

    public function test_sales_rep_cannot_view_or_edit_another_reps_quote(): void
    {
        $repA = User::where('email', 'john.wells@odrasweeper.com')->first();
        $repB = User::where('email', 'carlos.silva@odrasweeper.com')->first();
        
        $quoteService = app(QuoteService::class);
        $quote = $quoteService->createDraft($repA, [
            'quote_number' => '3003',
            'customer' => 'Private Client',
            'state' => [
                'quoteSeq' => '3003',
                'toCompany' => 'Private Client',
                'salesRep' => 'John Wells',
            ],
        ]);

        // Rep B tries to view Rep A's quote (global scope returns 404 or policy returns 403)
        $response = $this->actingAs($repB)->getJson("/api/quotes/{$quote->id}");
        $this->assertTrue(in_array($response->status(), [403, 404]));

        // Rep B tries to update Rep A's quote
        $response = $this->actingAs($repB)->putJson("/api/quotes/{$quote->id}", [
            'customer' => 'Hacked Customer',
            'state' => ['toCompany' => 'Hacked'],
        ]);
        $this->assertTrue(in_array($response->status(), [403, 404]));
    }

    public function test_sales_rep_can_create_new_revision_from_finalized_quote(): void
    {
        $user = User::where('email', 'john.wells@odrasweeper.com')->first();
        $quoteService = app(QuoteService::class);
        
        $quote = $quoteService->createDraft($user, [
            'quote_number' => '3004',
            'customer' => 'Revision Corp',
            'state' => [
                'quoteSeq' => '3004',
                'toCompany' => 'Revision Corp',
                'salesRep' => 'John Wells',
            ],
        ]);

        // Finalize v1 first
        $quoteService->finalizeQuote($quote, [
            'customer' => 'Revision Corp',
            'state' => [
                'quoteSeq' => '3004',
                'toCompany' => 'Revision Corp',
                'salesRep' => 'John Wells',
            ],
        ]);

        // Now create revision
        $response = $this->actingAs($user)->postJson("/api/quotes/{$quote->id}/revise");

        $response->assertStatus(200);
        $quote->refresh();

        $this->assertEquals(2, $quote->current_version);
        $this->assertEquals('Draft', $quote->status);

        // Verify Version 1 is still preserved in quote_versions table
        $this->assertDatabaseHas('quote_versions', [
            'quote_id' => $quote->id,
            'version_number' => 1,
        ]);
        $this->assertDatabaseHas('quote_versions', [
            'quote_id' => $quote->id,
            'version_number' => 2,
            'status' => 'Draft',
        ]);
    }

    public function test_pdf_upload_and_download_serves_valid_binary_pdf(): void
    {
        $user = User::where('email', 'john.wells@odrasweeper.com')->first();
        $quoteService = app(\App\Services\QuoteService::class);

        $quote = $quoteService->createDraft($user, [
            'quote_number' => '3009',
            'customer' => 'PDF Verification Corp',
            'state' => ['quoteSeq' => '3009'],
        ]);

        $finalized = $quoteService->finalizeQuote($quote, [
            'customer' => 'PDF Verification Corp',
            'base_msrp' => 257493,
            'base_list_price' => 257493,
            'base_unit_price' => 257493,
            'subtotal' => 257493,
            'total' => 257493,
            'state' => ['quoteSeq' => '3009', 'model' => 'MT4H'],
        ]);

        // Upload a base64 Data URI with filename parameter (as sent by html2pdf/jsPDF)
        $fakePdfBinary = "%PDF-1.4\n1 0 obj\n<<>>\nendobj\ntrailer\n<<>>\n%%EOF";
        $dataUri = 'data:application/pdf;filename=generated.pdf;base64,' . base64_encode($fakePdfBinary);

        $uploadResponse = $this->actingAs($user)->postJson("/api/quotes/{$quote->id}/pdf", [
            'pdf' => $dataUri,
        ]);
        $uploadResponse->assertStatus(200);

        // Download PDF
        $downloadResponse = $this->actingAs($user)->get("/sales/quotes/{$quote->id}/pdf");
        $downloadResponse->assertStatus(200);
        $downloadResponse->assertHeader('Content-Type', 'application/pdf');

        // Check that content begins with '%PDF-' and NOT with 'data:application/pdf'
        $streamedContent = $downloadResponse->getContent();
        $this->assertStringStartsWith('%PDF-', $streamedContent);
        $this->assertStringNotContainsString('data:application/pdf', $streamedContent);
    }

    public function test_custom_machine_description_persists_in_snapshot(): void
    {
        $admin = User::whereHas('role', fn ($q) => $q->where('slug', 'admin'))->first();

        $customDesc = 'SPECIAL ADMIN CUSTOMIZATION: MT4H Sweeper with reinforced chassis, customized dual brooms, and stainless steel high-durability body.';

        $payload = [
            'quote_number' => '3015',
            'customer' => 'Custom City Works',
            'model_description' => $customDesc,
            'base_msrp' => 257493,
            'base_list_price' => 257493,
            'base_unit_price' => 257493,
            'subtotal' => 257493,
            'total' => 257493,
            'state' => [
                'quoteSeq' => '3015',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'chassis' => 'MT4H - Isuzu NRR cabover, 19,500 GVWR, Class 5, non-CDL - LEFT side dumping hopper',
                'machineDescription' => $customDesc,
                'isCustomDesc' => true,
                'toCompany' => 'Custom City Works',
                'baseQty' => 1,
                'freight' => 0,
                'enh' => [],
            ],
        ];

        $response = $this->actingAs($admin)->postJson('/api/quotes', $payload);
        $response->assertStatus(201);

        $quote = Quote::where('quote_number', '3015')->first();
        $this->assertNotNull($quote);
        $this->assertEquals($customDesc, $quote->latestVersion->snapshot->model_description);

        // Check that builder view provides the custom description in initialState
        $viewResponse = $this->actingAs($admin)->get("/sales/quotes/{$quote->id}");
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Machine Description');
        $viewResponse->assertSee('ADMIN EDITABLE');
        $viewResponse->assertSee($customDesc);
    }

    public function test_dashboard_renders_print_button_for_quote_rows(): void
    {
        $user = User::where('email', 'john.wells@odrasweeper.com')->first();
        $quoteService = app(\App\Services\QuoteService::class);

        $quote = $quoteService->createDraft($user, [
            'quote_number' => '3016',
            'customer' => 'Miami Public Works Dept',
            'state' => [
                'quoteSeq' => '3016',
                'toCompany' => 'Miami Public Works Dept',
                'model' => 'MT4H',
                'salesRep' => 'John Wells',
            ],
        ]);

        $response = $this->actingAs($user)->get('/sales/dashboard');
        $response->assertStatus(200);
        $response->assertSee('?print=1');
        $response->assertSee('Print');
        $response->assertSee('modalQuotePrintBtn');
    }
}
