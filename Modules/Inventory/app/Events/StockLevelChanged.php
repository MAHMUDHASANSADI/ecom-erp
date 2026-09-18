<?php

namespace Modules\Inventory\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Catalog\Models\Product;
use Modules\Inventory\Models\StockMovement;

class StockLevelChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Product $product,
        public readonly StockMovement $movement,
        public readonly int $newStockLevel
    ) {}
}
