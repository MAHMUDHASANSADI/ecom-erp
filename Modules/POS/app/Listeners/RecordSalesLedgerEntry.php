<?php

namespace Modules\POS\Listeners;

use Modules\Auth\Models\ActivityLog;
use Modules\POS\Events\SaleCompleted;

/**
 * Sync listener — records the sale in the activity audit trail immediately.
 * This is the hook point for the Finance ledger entry in Phase 6.
 */
class RecordSalesLedgerEntry
{
    public function handle(SaleCompleted $event): void
    {
        $sale = $event->sale;

        ActivityLog::record(
            subject: $sale,
            description: 'sale_completed',
            causerId: $sale->created_by,
            properties: [
                'sale_number' => $sale->sale_number,
                'channel' => $sale->channel,
                'total' => (float) $sale->total,
                'payment_method' => $sale->payment_method,
                'items_count' => $sale->items->count(),
            ]
        );
    }
}
