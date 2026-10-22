<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Quote;
use App\Services\AuditService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    use AuthorizesRequests;

    /**
     * Download or stream the generated quotation PDF.
     */
    public function download(Quote $quote): Response
    {
        $this->authorize('downloadPdf', $quote);

        $requestedVersion = request()->query('version');
        if ($requestedVersion) {
            $version = $quote->versions()->where('version_number', (int) $requestedVersion)->firstOrFail();
        } else {
            $version = $quote->latestFinalizedVersion ?? $quote->latestVersion;
        }

        $snapshot = $version?->snapshot;

        $storagePath = "private/{$snapshot?->pdf_path}";

        if (! $snapshot || ! $snapshot->pdf_path || ! Storage::disk('local')->exists($storagePath)) {
            abort(404, "Quotation PDF for Revision {$version->version_number} has not been generated yet. Please open the Quote Builder and finalize the quotation.");
        }

        $content = Storage::disk('local')->get($storagePath);

        // Self-healing: if file on disk was stored as an un-decoded base64 string, decode and repair it
        if (preg_match('/^data:application\/pdf.*?;base64,(.+)$/s', $content, $matches)) {
            $decoded = base64_decode($matches[1]);
            if ($decoded !== false) {
                $content = $decoded;
                Storage::disk('local')->put($storagePath, $content);
            }
        } elseif (str_starts_with($content, 'data:application/pdf;base64,')) {
            $decoded = base64_decode(substr($content, 28));
            if ($decoded !== false) {
                $content = $decoded;
                Storage::disk('local')->put($storagePath, $content);
            }
        } elseif (! str_starts_with($content, '%PDF-')) {
            $decoded = base64_decode(trim($content));
            if ($decoded !== false && str_starts_with($decoded, '%PDF-')) {
                $content = $decoded;
                Storage::disk('local')->put($storagePath, $content);
            }
        }

        AuditService::log(
            action: 'PDF_DOWNLOADED',
            entityType: 'Quote',
            entityId: $quote->id,
            notes: "Downloaded PDF for Q-{$quote->quote_number} v{$version->version_number}"
        );

        $filename = "ODRA_Quotation_{$quote->quote_number}_v{$version->version_number}.pdf";
        $disposition = request()->has('inline') ? 'inline' : 'attachment';

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "{$disposition}; filename=\"{$filename}\"",
            'Content-Length' => strlen($content),
            'Cache-Control' => 'private, must-revalidate, post-check=0, pre-check=0',
            'Pragma' => 'public',
        ]);
    }
}
