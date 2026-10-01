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
}
