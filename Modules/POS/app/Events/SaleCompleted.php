<?php

namespace Modules\POS\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\POS\Models\Sale;

class SaleCompleted
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Sale $sale
    ) {}
}
