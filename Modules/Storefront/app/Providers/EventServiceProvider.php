<?php

namespace Modules\Storefront\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Storefront\Events\OrderPlaced;
use Modules\Storefront\Listeners\SendOrderConfirmationEmail;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        OrderPlaced::class => [
            SendOrderConfirmationEmail::class, // queued — must not block checkout redirect
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void {}
}
