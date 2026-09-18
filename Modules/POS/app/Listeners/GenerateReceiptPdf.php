<?php

namespace Modules\POS\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\POS\Events\SaleCompleted;

/**
 * Queued listener — PDF generation is a side effect that must not block
 * the HTTP response. The cashier gets redirected to the receipt page
 * immediately; the PDF is generated in the background.
 *
 * In v1 the receipt is rendered as a printable Blade view.
 * This listener is the extension point for background PDF storage / emailing.
 */
class GenerateReceiptPdf implements ShouldQueue
{
    public function handle(SaleCompleted $event): void
    {
        // v1: receipt rendered on-demand via Blade view at pos.sales.receipt
        // Future: generate PDF, store to disk, email to customer, etc.
        // $pdf = Pdf::loadView('pos::pos.receipt', ['sale' => $event->sale]);
        // Storage::put("receipts/{$event->sale->sale_number}.pdf", $pdf->output());
    }
}
