<?php

namespace Modules\Inventory\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Product;
use Modules\Inventory\Models\StockMovement;

class InventoryDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::first();

        if (! $owner) {
            return;
        }

        // Opening stock quantities per SKU
        $openingStock = [
            'IPH-15-PRO'   => 25,
            'SAM-S24'      => 30,
            'MBP-14-M3'    => 10,
            'ACC-USBC-1M'  => 150,
            'BEV-WATER-500' => 200,
            'SNK-NUTS-200' => 80,
            'OFF-A4-500'   => 60,
            'OFF-PEN-10'   => 100,
        ];

        foreach ($openingStock as $sku => $qty) {
            $product = Product::where('sku', $sku)->first();

            if (! $product) {
                continue;
            }

            // Only seed if no movements exist yet (idempotent)
            $exists = StockMovement::where('product_id', $product->id)->exists();

            if ($exists) {
                continue;
            }

            StockMovement::create([
                'product_id' => $product->id,
                'quantity_change' => $qty,
                'reason' => 'restock',
                'reference_type' => 'OpeningStock',
                'notes' => 'Opening stock — initial setup',
                'created_by' => $owner->id,
            ]);
        }
    }
}
