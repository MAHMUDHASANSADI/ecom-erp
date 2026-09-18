<?php

namespace Modules\Inventory\Providers;

use Modules\Inventory\Repositories\Contracts\StockRepositoryInterface;
use Modules\Inventory\Repositories\Eloquent\StockRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class InventoryServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Inventory';

    protected string $nameLower = 'inventory';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(StockRepositoryInterface::class, StockRepository::class);
    }
}
