<?php

namespace Modules\Storefront\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Modules\Storefront\Emails\OrderConfirmationMail;
use Modules\Storefront\Events\OrderPlaced;

/**
 * Queued — email delivery is a side effect and must not block
 * the checkout redirect back to the confirmation page.
 */
class SendOrderConfirmationEmail implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        Mail::to($event->order->email)
            ->send(new OrderConfirmationMail($event->order));
    }
}
