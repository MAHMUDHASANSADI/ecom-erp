<?php

namespace Modules\Storefront\Providers;

use Modules\Storefront\Repositories\Contracts\OrderRepositoryInterface;
use Modules\Storefront\Repositories\Eloquent\OrderRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class StorefrontServiceProvider extends ModuleServiceProvider
{
    protected string $name = 'Storefront';

    protected string $nameLower = 'storefront';

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }
}
