<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\AuditService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PdfController extends Controller
{
    use AuthorizesRequests;

    /**
     * Download or stream the generated quotation PDF.
     */
    public function download(Quote $quote): StreamedResponse|Response
    {
        $this->authorize('downloadPdf', $quote);

        $version = $quote->latestVersion;
        $snapshot = $version?->snapshot;

        if (! $snapshot || ! $snapshot->pdf_path || ! Storage::disk('local')->exists("private/{$snapshot->pdf_path}")) {
            abort(404, 'Quotation PDF has not been generated yet. Please open the Quote Builder and finalize the quotation.');
        }

        AuditService::log(
            action: 'PDF_DOWNLOADED',
            entityType: 'Quote',
            entityId: $quote->id,
            notes: "Downloaded PDF for Q-{$quote->quote_number} v{$quote->current_version}"
        );

        $filename = "ODRA_Quotation_Q-{$quote->quote_number}_v{$quote->current_version}.pdf";

        return Storage::disk('local')->download("private/{$snapshot->pdf_path}", $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }
}
