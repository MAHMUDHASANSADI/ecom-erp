<?php

namespace Modules\Storefront\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Storefront\Models\Order;

class OrderPlaced
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Order $order
    ) {}
}
