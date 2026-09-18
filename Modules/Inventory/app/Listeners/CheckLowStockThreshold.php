<?php

namespace Modules\Inventory\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Auth\Models\ActivityLog;
use Modules\Auth\Models\Setting;
use Modules\Inventory\Events\StockLevelChanged;

/**
 * Fires synchronously after every stock movement.
 * Logs a low-stock alert to the activity_log when stock
 * falls at or below the configured threshold.
 */
class CheckLowStockThreshold implements ShouldQueue
{
    public function handle(StockLevelChanged $event): void
    {
        $threshold = (int) Setting::getValue('low_stock_threshold', 5);

        if ($event->newStockLevel <= $threshold) {
            ActivityLog::record(
                subject: $event->product,
                description: 'low_stock_alert',
                causerId: null,
                properties: [
                    'current_stock' => $event->newStockLevel,
                    'threshold' => $threshold,
                    'product_name' => $event->product->name,
                    'sku' => $event->product->sku,
                ]
            );
        }
    }
}
