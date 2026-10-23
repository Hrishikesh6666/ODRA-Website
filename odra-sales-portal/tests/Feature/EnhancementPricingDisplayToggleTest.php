<?php

namespace Tests\Feature;

use App\Models\Enhancement;
use App\Models\FreightRule;
use App\Models\ModelPrice;
use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\QuoteSnapshot;
use App\Models\QuoteVersion;
use App\Models\Role;
use App\Models\SweeperModel;
use App\Models\User;
use App\Services\QuoteService;
use Tests\TestCase;

class EnhancementPricingDisplayToggleTest extends TestCase
{
    protected User $carlos;
    protected User $manager;
    protected QuoteService $quoteService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->quoteService = app(QuoteService::class);

        // Fetch or create Carlos Silva
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

        // Fetch or create Manager
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

        // Ensure MT4H and freight rule exist
        $mt4h = SweeperModel::firstOrCreate(['code' => 'MT4H'], [
            'description' => 'MT4H Mechanical Broom Sweeper',
            'category' => 'MECHANICAL BROOM SWEEPER',
            'is_active' => true,
        ]);

        ModelPrice::firstOrCreate(['sweeper_model_id' => $mt4h->id], [
            'msrp' => 257493.00,
            'sw_list' => 307378.00,
            'sw_net' => 270493.00,
            'effective_from' => now()->subDay(),
        ]);

        FreightRule::firstOrCreate([
            'state' => 'Texas',
            'city' => 'San Antonio TX 78233',
        ], [
            'customer_rate' => 6760.00,
            'municipality_rate' => 7115.00,
        ]);
    }

    protected function uniqueQuoteNumber(): string
    {
        return (string) rand(100000, 999999);
    }

    /**
     * Test 1: New quotations default to Itemized display mode.
     */
    public function test_default_enhancement_pricing_mode_is_itemized(): void
    {
        $this->actingAs($this->carlos);
        $qNum = $this->uniqueQuoteNumber();

        $payload = [
            'quote_number' => $qNum,
            'customer' => 'City of San Antonio',
            'state' => [
                'quoteSeq' => $qNum,
                'toCompany' => 'City of San Antonio',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'destState' => 'Texas',
                'destCity' => 'San Antonio TX 78233',
                'baseQty' => 1,
                'enh' => [],
            ],
        ];

        $response = $this->postJson('/api/quotes', $payload);
        $response->assertStatus(201);

        $quote = Quote::where('quote_number', $qNum)->firstOrFail();
        $snapshot = $quote->latestVersion->snapshot;

        $this->assertEquals('itemized', $snapshot->enhancement_pricing_mode);
        $this->assertTrue($snapshot->isItemized());
        $this->assertFalse($snapshot->isBundled());
        $this->assertEquals('itemized', $snapshot->full_state['enhancementPricingMode']);
    }

    /**
     * Test 2: Bundled mode persists in database snapshot and full_state.
     */
    public function test_bundled_mode_persistence_in_snapshot_and_full_state(): void
    {
        $this->actingAs($this->carlos);
        $qNum = $this->uniqueQuoteNumber();

        $payload = [
            'quote_number' => $qNum,
            'customer' => 'Bexar County Public Works',
            'enhancement_pricing_mode' => 'bundled',
            'state' => [
                'quoteSeq' => $qNum,
                'toCompany' => 'Bexar County Public Works',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'destState' => 'Texas',
                'destCity' => 'San Antonio TX 78233',
                'enhancementPricingMode' => 'bundled',
                'baseQty' => 1,
                'enh' => [
                    ['desc' => 'Rear Mounted LED Light Bar', 'qty' => 1],
                ],
            ],
        ];

        $response = $this->postJson('/api/quotes', $payload);
        $response->assertStatus(201);

        $quote = Quote::where('quote_number', $qNum)->firstOrFail();
        $snapshot = $quote->latestVersion->snapshot;

        $this->assertEquals('bundled', $snapshot->enhancement_pricing_mode);
        $this->assertTrue($snapshot->isBundled());
        $this->assertFalse($snapshot->isItemized());
        $this->assertEquals('bundled', $snapshot->full_state['enhancementPricingMode']);
    }

    /**
     * Test 3: Internal itemized breakdown is permanently preserved in quote_items and snapshot.
     */
    public function test_internal_itemized_breakdown_preserved_when_bundled(): void
    {
        $this->actingAs($this->carlos);
        $qNum = $this->uniqueQuoteNumber();

        // Ensure enhancement exists
        Enhancement::firstOrCreate(['description' => 'Rear Mounted LED Light Bar'], [
            'sweeper_model_id' => SweeperModel::where('code', 'MT4H')->first()->id,
            'category' => 'LIGHTS',
            'msrp' => 2293.00,
            'sw_list' => 2504.00,
            'sw_net' => 2454.00,
            'is_active' => true,
        ]);

        $payload = [
            'quote_number' => $qNum,
            'customer' => 'Texas Highway Services',
            'enhancement_pricing_mode' => 'bundled',
            'state' => [
                'quoteSeq' => $qNum,
                'toCompany' => 'Texas Highway Services',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'destState' => 'Texas',
                'destCity' => 'San Antonio TX 78233',
                'enhancementPricingMode' => 'bundled',
                'baseQty' => 1,
                'enh' => [
                    ['desc' => 'Rear Mounted LED Light Bar', 'qty' => 2],
                ],
            ],
        ];

        // 1. Create draft
        $draftRes = $this->postJson('/api/quotes', $payload);
        $draftRes->assertStatus(201);

        $quote = Quote::where('quote_number', $qNum)->firstOrFail();

        // 2. Finalize quote
        $finalizeRes = $this->postJson("/api/quotes/{$quote->id}/finalize", $payload);
        $finalizeRes->assertStatus(200);

        $version = $quote->latestVersion->fresh();
        $snapshot = $version->snapshot;

        // Snapshot retains the itemized array with quantities and unit prices
        $this->assertNotEmpty($snapshot->enhancements_snapshot);
        $firstEnh = $snapshot->enhancements_snapshot[0];
        $this->assertEquals('Rear Mounted LED Light Bar', $firstEnh['desc']);
        $this->assertEquals(2, $firstEnh['qty']);
        $this->assertEquals(2293.00, (float) $firstEnh['unit']);
        $this->assertEquals(4586.00, (float) $firstEnh['extended']);

        // QuoteItem normalized records are fully itemized for audit and margin tracking
        $items = QuoteItem::where('quote_version_id', $version->id)->get();
        $this->assertCount(2, $items); // 1 base_unit + 1 enhancement

        $baseItem = $items->firstWhere('item_type', 'base_unit');
        $this->assertNotNull($baseItem);
        $this->assertEquals(257493.00, (float) $baseItem->unit_price_at_time);

        $enhItem = $items->firstWhere('item_type', 'enhancement');
        $this->assertNotNull($enhItem);
        $this->assertEquals('Rear Mounted LED Light Bar', $enhItem->description);
        $this->assertEquals(2, $enhItem->qty);
        $this->assertEquals(2293.00, (float) $enhItem->unit_price_at_time);
        $this->assertEquals(4586.00, (float) $enhItem->extended_price);
    }

    /**
     * Test 4: Pricing invariance - switching display mode does not alter subtotal or total.
     */
    public function test_pricing_invariance_between_modes(): void
    {
        $this->actingAs($this->carlos);
        $qNum = $this->uniqueQuoteNumber();

        Enhancement::firstOrCreate(['description' => 'Elevator System Flush'], [
            'sweeper_model_id' => SweeperModel::where('code', 'MT4H')->first()->id,
            'category' => 'WATER',
            'msrp' => 1405.00,
            'sw_list' => 1505.00,
            'sw_net' => 1475.00,
            'is_active' => true,
        ]);

        $basePayload = [
            'customer' => 'Invariance Testing Co',
            'state' => [
                'quoteSeq' => $qNum,
                'toCompany' => 'Invariance Testing Co',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'destState' => 'Texas',
                'destCity' => 'San Antonio TX 78233',
                'baseQty' => 1,
                'enh' => [
                    ['desc' => 'Elevator System Flush', 'qty' => 1],
                ],
            ],
        ];

        // 1. Create as Itemized
        $itemizedPayload = $basePayload;
        $itemizedPayload['quote_number'] = $qNum;
        $itemizedPayload['enhancement_pricing_mode'] = 'itemized';
        $itemizedPayload['state']['enhancementPricingMode'] = 'itemized';

        $resItemized = $this->postJson('/api/quotes', $itemizedPayload);
        $resItemized->assertStatus(201);
        $quote = Quote::where('quote_number', $qNum)->firstOrFail();

        $subtotal1 = (float) $quote->latestVersion->snapshot->subtotal;
        $total1 = (float) $quote->latestVersion->snapshot->total;

        // 2. Update same draft to Bundled
        $bundledPayload = $basePayload;
        $bundledPayload['enhancement_pricing_mode'] = 'bundled';
        $bundledPayload['state']['enhancementPricingMode'] = 'bundled';

        $resBundled = $this->putJson("/api/quotes/{$quote->id}", $bundledPayload);
        $resBundled->assertStatus(200);

        $quote->refresh();
        $subtotal2 = (float) $quote->latestVersion->snapshot->subtotal;
        $total2 = (float) $quote->latestVersion->snapshot->total;

        // Both modes produce identical financials
        $this->assertEquals($subtotal1, $subtotal2);
        $this->assertEquals($total1, $total2);
        $this->assertEquals('bundled', $quote->latestVersion->snapshot->enhancement_pricing_mode);
    }

    /**
     * Test 5: Revisions inherit pricing display mode and allow toggling in new draft.
     */
    public function test_revisions_inherit_enhancement_pricing_mode(): void
    {
        $this->actingAs($this->carlos);
        $qNum = $this->uniqueQuoteNumber();

        // 1. Create and finalize a quote in bundled mode
        $payload = [
            'quote_number' => $qNum,
            'customer' => 'Inheritance Test LLC',
            'enhancement_pricing_mode' => 'bundled',
            'state' => [
                'quoteSeq' => $qNum,
                'toCompany' => 'Inheritance Test LLC',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'destState' => 'Texas',
                'destCity' => 'San Antonio TX 78233',
                'enhancementPricingMode' => 'bundled',
                'baseQty' => 1,
                'enh' => [],
            ],
        ];

        $this->postJson('/api/quotes', $payload);
        $quote = Quote::where('quote_number', $qNum)->firstOrFail();
        $this->postJson("/api/quotes/{$quote->id}/finalize", $payload);

        $rev1 = $quote->versions()->where('version_number', 1)->firstOrFail();
        $this->assertEquals('bundled', $rev1->snapshot->enhancement_pricing_mode);

        // 2. Create revision
        $reviseRes = $this->postJson("/api/quotes/{$quote->id}/revise");
        $reviseRes->assertStatus(200);

        $quote->refresh();
        $this->assertEquals(2, $quote->current_version);
        $rev2 = $quote->versions()->where('version_number', 2)->firstOrFail();

        // Inherited bundled mode
        $this->assertEquals('bundled', $rev2->snapshot->enhancement_pricing_mode);
        $this->assertEquals('bundled', $rev2->snapshot->full_state['enhancementPricingMode']);

        // 3. Update rev2 to itemized
        $updatePayload = $payload;
        $updatePayload['enhancement_pricing_mode'] = 'itemized';
        $updatePayload['state']['enhancementPricingMode'] = 'itemized';

        $this->putJson("/api/quotes/{$quote->id}", $updatePayload);

        $rev2->refresh();
        $this->assertEquals('itemized', $rev2->snapshot->enhancement_pricing_mode);

        // Original rev1 snapshot remains frozen and unchanged in bundled mode
        $rev1->refresh();
        $this->assertEquals('bundled', $rev1->snapshot->enhancement_pricing_mode);
    }

    /**
     * Test 6: Builder view renders the toggle with correct labels and options.
     */
    public function test_builder_view_contains_enhancement_price_display_toggle(): void
    {
        $this->actingAs($this->carlos);

        $response = $this->get('/sales/quotes/create');
        $response->assertStatus(200);
        $response->assertSee('Enhancement Price Display');
        $response->assertSee('Itemized');
        $response->assertSee('Bundled into Machine Price');
    }

    /**
     * Test 7: Show endpoint returns enhancement_pricing_mode for API and modal consumption.
     */
    public function test_api_show_returns_enhancement_pricing_mode(): void
    {
        $this->actingAs($this->carlos);
        $qNum = $this->uniqueQuoteNumber();

        $quote = Quote::create([
            'user_id' => $this->carlos->id,
            'quote_number' => $qNum,
            'current_version' => 1,
            'status' => 'Draft',
        ]);

        $version = QuoteVersion::create([
            'quote_id' => $quote->id,
            'version_number' => 1,
            'status' => 'Draft',
        ]);

        QuoteSnapshot::create([
            'quote_version_id' => $version->id,
            'customer_company' => 'Test Agency',
            'model_code' => 'MT4H',
            'model_description' => 'MT4H Sweeper',
            'chassis_configuration' => 'Isuzu NRR cabover',
            'pricing_basis' => 'MSRP (Commercial)',
            'enhancement_pricing_mode' => 'bundled',
            'sales_rep_name' => $this->carlos->name,
            'base_qty' => 1,
            'base_msrp' => 257493.00,
            'base_list_price' => 257493.00,
            'base_unit_price' => 257493.00,
            'subtotal' => 257493.00,
            'total' => 264253.00,
        ]);

        $response = $this->getJson("/api/quotes/{$quote->id}");
        $response->assertStatus(200);
        $response->assertJsonPath('snapshot.enhancement_pricing_mode', 'bundled');
    }

    /**
     * Test 8: Script does not have sectionHeading temporal dead zone reference errors, and bundled mode suppresses enhancement details.
     */
    public function test_builder_view_renders_cleanly_without_reference_errors(): void
    {
        $this->actingAs($this->carlos);

        $response = $this->get('/sales/quotes/create');
        $response->assertStatus(200);

        $content = $response->getContent();

        // Ensure no syntax or reference errors
        $this->assertStringNotContainsString("Cannot access 'sectionHeading' before initialization", $content);

        // Ensure sectionHeading is declared before sectionRowHtml in the JS bundle
        $posSectionHeading = strpos($content, 'const sectionHeading =');
        $posSectionRowHtml = strpos($content, 'const sectionRowHtml =');
        $this->assertNotFalse($posSectionHeading);
        $this->assertNotFalse($posSectionRowHtml);
        $this->assertLessThan($posSectionRowHtml, $posSectionHeading, 'sectionHeading must be declared before sectionRowHtml');

        // Ensure bundled mode removes "including selected enhancements" and "Included in Machine Price"
        $this->assertStringNotContainsString('including selected enhancements', $content);
        $this->assertStringNotContainsString('Included in Machine Price', $content);
    }
}
