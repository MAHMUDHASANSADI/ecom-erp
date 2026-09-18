<?php

namespace Modules\POS\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\POS\Events\SaleCompleted;
use Modules\POS\Listeners\DeductStock;
use Modules\POS\Listeners\GenerateReceiptPdf;
use Modules\POS\Listeners\RecordSalesLedgerEntry;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        SaleCompleted::class => [
            DeductStock::class,              // sync — must complete before response
            RecordSalesLedgerEntry::class,   // sync — must complete before response
            GenerateReceiptPdf::class,       // queued — side effect, non-blocking
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void {}
}
