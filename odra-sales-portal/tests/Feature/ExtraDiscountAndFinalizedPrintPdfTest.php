<?php

namespace Tests\Feature;

use App\Models\Quote;
use App\Models\User;
use App\Services\QuoteService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExtraDiscountAndFinalizedPrintPdfTest extends TestCase
{
    protected User $rep;
    protected User $manager;
    protected QuoteService $quoteService;

    protected function setUp(): void
    {
        parent::setUp();
        Quote::withoutGlobalScopes()->forceDelete();

        $this->rep = User::where('email', 'john.wells@odrasweeper.com')->firstOrFail();
        $this->manager = User::where('email', 'alex.byczynski@odrasweeper.com')->firstOrFail();
        $this->quoteService = app(QuoteService::class);
    }

    public function test_extra_discount_is_permitted_and_applied_for_msrp_commercial_basis(): void
    {
        $quoteNumber = (string) rand(100000, 999999);
        $quote = $this->quoteService->createDraft($this->rep, [
            'quote_number' => $quoteNumber,
            'customer' => 'Commercial Buyer LLC',
            'state' => [
                'quoteSeq' => $quoteNumber,
                'toCompany' => 'Commercial Buyer LLC',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'baseShowDiscount' => 5000.00,
                'salesRep' => $this->rep->name,
            ],
        ]);

        $finalized = $this->quoteService->finalizeQuote($quote, [
            'customer' => 'Commercial Buyer LLC',
            'state' => [
                'quoteSeq' => $quoteNumber,
                'toCompany' => 'Commercial Buyer LLC',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'baseShowDiscount' => 5000.00,
                'salesRep' => $this->rep->name,
            ],
        ]);

        $snapshot = $finalized->latestVersion->snapshot;
        $this->assertEquals(5000.00, (float) $snapshot->show_discount_amount);
        // Base unit price should be base MSRP minus 5,000
        $this->assertEquals($snapshot->base_msrp - 5000.00, (float) $snapshot->base_unit_price);
    }

    public function test_extra_discount_is_suppressed_and_forced_to_zero_for_cooperative_contracts(): void
    {
        $quoteNumber = (string) rand(100000, 999999);
        $quote = $this->quoteService->createDraft($this->rep, [
            'quote_number' => $quoteNumber,
            'customer' => 'City of Austin',
            'state' => [
                'quoteSeq' => $quoteNumber,
                'toCompany' => 'City of Austin',
                'model' => 'MT4H',
                'basis' => 'Sourcewell',
                'baseShowDiscount' => 8000.00, // Should be forced to 0
                'salesRep' => $this->rep->name,
            ],
        ]);

        $finalized = $this->quoteService->finalizeQuote($quote, [
            'customer' => 'City of Austin',
            'state' => [
                'quoteSeq' => $quoteNumber,
                'toCompany' => 'City of Austin',
                'model' => 'MT4H',
                'basis' => 'Sourcewell',
                'baseShowDiscount' => 8000.00,
                'salesRep' => $this->rep->name,
            ],
        ]);

        $snapshot = $finalized->latestVersion->snapshot;
        $this->assertEquals(0.00, (float) $snapshot->show_discount_amount);
        // Base unit price on Sourcewell should be standard 12% off sw_list, unaffected by extra discount
        $expectedCoopUnit = round($snapshot->base_list_price * (1 - 0.12), 2);
        $this->assertEquals($expectedCoopUnit, (float) $snapshot->base_unit_price);
    }

    public function test_print_and_pdf_options_are_strictly_hidden_for_draft_quotations(): void
    {
        $quoteNumber = (string) rand(100000, 999999);
        $quote = $this->quoteService->createDraft($this->rep, [
            'quote_number' => $quoteNumber,
            'customer' => 'Draft Customer Corp',
            'state' => [
                'quoteSeq' => $quoteNumber,
                'toCompany' => 'Draft Customer Corp',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'salesRep' => $this->rep->name,
            ],
        ]);

        // 1. Quote Builder page should NOT render Print or Download PDF buttons for Draft
        $builderRes = $this->actingAs($this->rep)->get("/sales/quotes/{$quote->id}");
        $builderRes->assertStatus(200);
        $builderRes->assertSee('id="btnSaveDraft"', false);
        $builderRes->assertSee('id="btnFinalize"', false);
        $builderRes->assertDontSee('window.print()');
        $builderRes->assertDontSee("sales/quotes/{$quote->id}/pdf");

        // 2. Sales Quotes index table should NOT render Print or Download for Draft
        $indexRes = $this->actingAs($this->rep)->get('/sales/quotes');
        $indexRes->assertStatus(200);
        $indexRes->assertDontSee("sales/quotes/{$quote->id}?version=1&amp;print=1");
        $indexRes->assertDontSee("sales/quotes/{$quote->id}?version=1&print=1");
        $indexRes->assertDontSee("sales/quotes/{$quote->id}/pdf");

        // 3. Manager Quotes index table should NOT render Print or Download for Draft
        $mgrIndexRes = $this->actingAs($this->manager)->get('/manager/quotes');
        $mgrIndexRes->assertStatus(200);
        $mgrIndexRes->assertDontSee("sales/quotes/{$quote->id}?version=1&amp;print=1");
        $mgrIndexRes->assertDontSee("sales/quotes/{$quote->id}?version=1&print=1");
        $mgrIndexRes->assertDontSee("sales/quotes/{$quote->id}/pdf");

        // 4. Sales Dashboard table should NOT render Print or Download for Draft
        $dashRes = $this->actingAs($this->rep)->get('/sales/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertDontSee("sales/quotes/{$quote->id}?print=1");
        $dashRes->assertDontSee("sales/quotes/{$quote->id}/pdf");

        // 5. Manager Dashboard table should NOT render Print or PDF for Draft
        $mgrDashRes = $this->actingAs($this->manager)->get('/manager/dashboard');
        $mgrDashRes->assertStatus(200);
        $mgrDashRes->assertDontSee("sales/quotes/{$quote->id}?version=1&amp;print=1");
        $mgrDashRes->assertDontSee("sales/quotes/{$quote->id}?version=1&print=1");
        $mgrDashRes->assertDontSee("sales/quotes/{$quote->id}/pdf");

        // 6. Direct PDF download on a draft version returns 404 or 403
        $pdfRes = $this->actingAs($this->rep)->get("/sales/quotes/{$quote->id}/pdf");
        $this->assertTrue(in_array($pdfRes->status(), [403, 404]));

        // 7. Inspect API should return null for print_url and pdf_url on draft
        $inspectRes = $this->actingAs($this->rep)->getJson("/api/quotes/{$quote->id}");
        $inspectRes->assertStatus(200);
        $inspectRes->assertJson([
            'status' => 'Draft',
            'print_url' => null,
            'pdf_url' => null,
            'can_download' => false,
        ]);

        // 8. Revision History API should return null for print_url and pdf_url on draft revision
        $revRes = $this->actingAs($this->rep)->getJson("/api/quotes/{$quote->id}/revisions");
        $revRes->assertStatus(200);
        $revisions = $revRes->json('revisions');
        $this->assertCount(1, $revisions);
        $this->assertNull($revisions[0]['print_url']);
        $this->assertNull($revisions[0]['pdf_url']);
    }

    public function test_print_and_pdf_options_are_rendered_when_quotation_is_finalized(): void
    {
        $quoteNumber = (string) rand(100000, 999999);
        $quote = $this->quoteService->createDraft($this->rep, [
            'quote_number' => $quoteNumber,
            'customer' => 'Finalized Client Inc',
            'state' => [
                'quoteSeq' => $quoteNumber,
                'toCompany' => 'Finalized Client Inc',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'salesRep' => $this->rep->name,
            ],
        ]);

        $finalized = $this->quoteService->finalizeQuote($quote, [
            'customer' => 'Finalized Client Inc',
            'state' => [
                'quoteSeq' => $quoteNumber,
                'toCompany' => 'Finalized Client Inc',
                'model' => 'MT4H',
                'basis' => 'MSRP (Commercial)',
                'salesRep' => $this->rep->name,
            ],
        ]);

        // Place a mock PDF in storage so PDF download succeeds
        $pdfPath = "quotes/{$this->rep->id}/Q-{$quoteNumber}_v1.pdf";
        Storage::disk('local')->put("private/{$pdfPath}", '%PDF-1.4 Mock Binary Content');
        $finalized->latestVersion->snapshot->update([
            'pdf_path' => $pdfPath,
            'pdf_generated_at' => now(),
        ]);

        // 1. Quote Builder page renders Print and Download PDF
        $builderRes = $this->actingAs($this->rep)->get("/sales/quotes/{$finalized->id}");
        $builderRes->assertStatus(200);
        $builderRes->assertSee('window.print()');
        $builderRes->assertSee('Download PDF');
        $builderRes->assertDontSee('id="btnSaveDraft"', false);
        $builderRes->assertDontSee('id="btnFinalize"', false);

        // 2. Sales Quotes index table renders Print and Download
        $indexRes = $this->actingAs($this->rep)->get('/sales/quotes');
        $indexRes->assertStatus(200);
        $indexRes->assertSee("sales/quotes/{$finalized->id}?version=1", false);
        $indexRes->assertSee('Print');
        $indexRes->assertSee("sales/quotes/{$finalized->id}/pdf");

        // 3. Manager Quotes index table renders Print and Download
        $mgrIndexRes = $this->actingAs($this->manager)->get('/manager/quotes');
        $mgrIndexRes->assertStatus(200);
        $mgrIndexRes->assertSee("sales/quotes/{$finalized->id}?version=1", false);
        $mgrIndexRes->assertSee('Print');
        $mgrIndexRes->assertSee("sales/quotes/{$finalized->id}/pdf");

        // 4. Sales Dashboard table renders Print and Download
        $dashRes = $this->actingAs($this->rep)->get('/sales/dashboard');
        $dashRes->assertStatus(200);
        $dashRes->assertSee("sales/quotes/{$finalized->id}?print=1");
        $dashRes->assertSee("sales/quotes/{$finalized->id}/pdf");

        // 5. Manager Dashboard table renders Print and PDF
        $mgrDashRes = $this->actingAs($this->manager)->get('/manager/dashboard');
        $mgrDashRes->assertStatus(200);
        $mgrDashRes->assertSee("sales/quotes/{$finalized->id}?version=1", false);
        $mgrDashRes->assertSee("sales/quotes/{$finalized->id}/pdf");

        // 6. Direct PDF download succeeds
        $pdfRes = $this->actingAs($this->rep)->get("/sales/quotes/{$finalized->id}/pdf");
        $pdfRes->assertStatus(200);
        $pdfRes->assertHeader('Content-Type', 'application/pdf');

        // 7. Inspect API returns valid print_url and pdf_url
        $inspectRes = $this->actingAs($this->rep)->getJson("/api/quotes/{$finalized->id}");
        $inspectRes->assertStatus(200);
        $inspectRes->assertJson([
            'status' => 'Finalized',
            'can_download' => true,
        ]);
        $this->assertNotNull($inspectRes->json('print_url'));
        $this->assertNotNull($inspectRes->json('pdf_url'));

        // 8. Revision History API returns valid print_url and pdf_url
        $revRes = $this->actingAs($this->rep)->getJson("/api/quotes/{$finalized->id}/revisions");
        $revRes->assertStatus(200);
        $revisions = $revRes->json('revisions');
        $this->assertCount(1, $revisions);
        $this->assertNotNull($revisions[0]['print_url']);
        $this->assertNotNull($revisions[0]['pdf_url']);
    }
}
