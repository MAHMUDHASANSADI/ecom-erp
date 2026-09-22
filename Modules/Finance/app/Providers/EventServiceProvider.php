<?php

namespace Modules\Finance\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Finance\Events\ExpenseLogged;
use Modules\Finance\Listeners\RecalculateMonthlyProfitSnapshot;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        ExpenseLogged::class => [
            RecalculateMonthlyProfitSnapshot::class,
        ],
    ];

    protected static $shouldDiscoverEvents = false;

    protected function configureEmailVerification(): void {}
}
