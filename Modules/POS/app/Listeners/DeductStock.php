<?php

namespace Modules\POS\Listeners;

use Modules\Inventory\Events\StockLevelChanged;
use Modules\Inventory\Repositories\Contracts\StockRepositoryInterface;
use Modules\POS\Events\SaleCompleted;

/**
 * Sync listener — deducts stock for every sale item immediately.
 * Stock must be confirmed decremented before the sale is considered done.
 */
class DeductStock
{
    public function __construct(
        private readonly StockRepositoryInterface $stock
    ) {}

    public function handle(SaleCompleted $event): void
    {
        $sale = $event->sale;

        foreach ($sale->items as $item) {
            $movement = $this->stock->recordMovement([
                'product_id' => $item->product_id,
                'quantity_change' => -$item->quantity,
                'reason' => 'sale',
                'reference_type' => 'Sale',
                'reference_id' => $sale->id,
                'notes' => "Sale {$sale->sale_number}",
                'created_by' => $sale->created_by,
            ]);

            $newStock = $this->stock->currentStock($item->product_id);

            // Fire StockLevelChanged so low-stock threshold is checked per item
            StockLevelChanged::dispatch($item->product, $movement, $newStock);
        }
    }
}
