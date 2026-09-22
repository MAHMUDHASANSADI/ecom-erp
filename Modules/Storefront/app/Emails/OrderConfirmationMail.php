<?php

namespace Modules\Storefront\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Modules\Auth\Models\Setting;
use Modules\Storefront\Models\Order;

class OrderConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Order $order
    ) {}

    public function envelope(): Envelope
    {
        $appName = Setting::getValue('app_name', config('app.name'));

        return new Envelope(
            subject: "Order Confirmation – {$this->order->id} | {$appName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'storefront::emails.order-confirmation',
        );
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
