<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Database\Seeders\AuthDatabaseSeeder;
use Modules\Catalog\Database\Seeders\CatalogDatabaseSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AuthDatabaseSeeder::class,
            CatalogDatabaseSeeder::class,
        ]);
    }
}
