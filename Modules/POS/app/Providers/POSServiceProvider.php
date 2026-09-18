<?php

namespace Modules\POS\Providers;

use Modules\POS\Repositories\Contracts\SaleRepositoryInterface;
use Modules\POS\Repositories\Eloquent\SaleRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class POSServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'POS';

    protected string $nameLower = 'pos';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
    }
}
