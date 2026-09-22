<?php

namespace Modules\Finance\Providers;

use Modules\Finance\Repositories\Contracts\ExpenseRepositoryInterface;
use Modules\Finance\Repositories\Eloquent\ExpenseRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class FinanceServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Finance';

    protected string $nameLower = 'finance';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(ExpenseRepositoryInterface::class, ExpenseRepository::class);
    }
}
