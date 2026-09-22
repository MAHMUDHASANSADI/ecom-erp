<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Database\Seeders\AuthDatabaseSeeder;
use Modules\Catalog\Database\Seeders\CatalogDatabaseSeeder;
use Modules\Inventory\Database\Seeders\InventoryDatabaseSeeder;
use Modules\Storefront\Database\Seeders\StorefrontDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AuthDatabaseSeeder::class,
            CatalogDatabaseSeeder::class,
            InventoryDatabaseSeeder::class,
            StorefrontDatabaseSeeder::class,
        ]);
    }
}
