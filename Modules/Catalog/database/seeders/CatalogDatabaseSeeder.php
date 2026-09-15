<?php

namespace Modules\Catalog\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;

class CatalogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCategories();
        $this->seedProducts();
    }

    private function seedCategories(): void
    {
        $tree = [
            'Electronics' => ['Smartphones', 'Laptops', 'Accessories'],
            'Clothing' => ['Men\'s Wear', 'Women\'s Wear'],
            'Food & Beverages' => ['Beverages', 'Snacks'],
            'Office Supplies' => [],
        ];

        foreach ($tree as $parentName => $children) {
            $parent = Category::firstOrCreate(
                ['slug' => Category::generateSlug($parentName)],
                ['name' => $parentName, 'is_active' => true]
            );

            foreach ($children as $childName) {
                Category::firstOrCreate(
                    ['slug' => Category::generateSlug($childName)],
                    [
                        'name' => $childName,
                        'parent_category_id' => $parent->id,
                        'is_active' => true,
                    ]
                );
            }
        }
    }

    private function seedProducts(): void
    {
        $smartphones = Category::where('name', 'Smartphones')->first();
        $laptops = Category::where('name', 'Laptops')->first();
        $accessories = Category::where('name', 'Accessories')->first();
        $beverages = Category::where('name', 'Beverages')->first();
        $snacks = Category::where('name', 'Snacks')->first();
        $officeSupplies = Category::where('name', 'Office Supplies')->first();

        $products = [
            [
                'category_id' => $smartphones?->id,
                'name' => 'iPhone 15 Pro',
                'sku' => 'IPH-15-PRO',
                'price' => 999.00,
                'cost_price' => 720.00,
                'tax_class' => 'standard',
                'description' => 'Apple iPhone 15 Pro — 256GB, Titanium.',
            ],
            [
                'category_id' => $smartphones?->id,
                'name' => 'Samsung Galaxy S24',
                'sku' => 'SAM-S24',
                'price' => 849.00,
                'cost_price' => 600.00,
                'tax_class' => 'standard',
                'description' => 'Samsung Galaxy S24 — 128GB.',
            ],
            [
                'category_id' => $laptops?->id,
                'name' => 'MacBook Pro 14"',
                'sku' => 'MBP-14-M3',
                'price' => 1999.00,
                'cost_price' => 1500.00,
                'tax_class' => 'standard',
                'description' => 'Apple MacBook Pro 14-inch, M3 chip.',
            ],
            [
                'category_id' => $accessories?->id,
                'name' => 'USB-C Charging Cable',
                'sku' => 'ACC-USBC-1M',
                'price' => 19.99,
                'cost_price' => 5.00,
                'tax_class' => 'standard',
                'description' => '1-metre braided USB-C cable.',
            ],
            [
                'category_id' => $beverages?->id,
                'name' => 'Mineral Water 500ml',
                'sku' => 'BEV-WATER-500',
                'price' => 1.50,
                'cost_price' => 0.40,
                'tax_class' => 'zero',
                'description' => 'Still mineral water, 500ml bottle.',
            ],
            [
                'category_id' => $snacks?->id,
                'name' => 'Mixed Nuts 200g',
                'sku' => 'SNK-NUTS-200',
                'price' => 6.99,
                'cost_price' => 3.00,
                'tax_class' => 'zero',
                'description' => 'Premium mixed nuts, 200g pack.',
            ],
            [
                'category_id' => $officeSupplies?->id,
                'name' => 'A4 Paper Ream (500 sheets)',
                'sku' => 'OFF-A4-500',
                'price' => 8.99,
                'cost_price' => 4.50,
                'tax_class' => 'standard',
                'description' => '80gsm A4 white copy paper, 500 sheets.',
            ],
            [
                'category_id' => $officeSupplies?->id,
                'name' => 'Ballpoint Pen (Box of 10)',
                'sku' => 'OFF-PEN-10',
                'price' => 4.99,
                'cost_price' => 1.50,
                'tax_class' => 'standard',
                'description' => 'Blue ballpoint pens, box of 10.',
            ],
        ];

        foreach ($products as $data) {
            if (! $data['category_id']) {
                continue;
            }

            Product::firstOrCreate(
                ['sku' => $data['sku']],
                array_merge($data, ['is_active' => true])
            );
        }
    }
}
