<?php

namespace Modules\Auth\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Modules\Auth\Models\Lookup;
use Modules\Auth\Models\NavigationItem;
use Modules\Auth\Models\Setting;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AuthDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedPermissions();
        $this->seedRoles();
        $this->seedDefaultOwner();
        $this->seedLookups();
        $this->seedSettings();
        $this->seedNavigation();
    }

    private function seedPermissions(): void
    {
        $permissions = [
            // Catalog
            'view_products',
            'manage_products',
            // Inventory
            'view_inventory',
            'manage_inventory',
            // POS
            'operate_pos',
            // Finance
            'view_finance_reports',
            'manage_expenses',
            // Storefront / Orders
            'view_orders',
            'manage_orders',
            // Users & System
            'manage_users',
            'manage_settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }
    }

    private function seedRoles(): void
    {
        $ownerRole = Role::firstOrCreate(['name' => 'Owner', 'guard_name' => 'web']);
        $managerRole = Role::firstOrCreate(['name' => 'Manager', 'guard_name' => 'web']);
        $cashierRole = Role::firstOrCreate(['name' => 'Cashier', 'guard_name' => 'web']);

        // Owner gets all permissions
        $ownerRole->syncPermissions(Permission::all());

        // Manager: catalog, inventory, POS, finance, orders — no user/settings management
        $managerRole->syncPermissions([
            'view_products', 'manage_products',
            'view_inventory', 'manage_inventory',
            'operate_pos',
            'view_finance_reports', 'manage_expenses',
            'view_orders', 'manage_orders',
        ]);

        // Cashier: POS only
        $cashierRole->syncPermissions(['operate_pos']);
    }

    private function seedDefaultOwner(): void
    {
        if (User::where('email', 'owner@admin.com')->exists()) {
            return;
        }

        $user = User::create([
            'name' => 'Store Owner',
            'email' => 'owner@admin.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $user->assignRole('Owner');
    }

    private function seedLookups(): void
    {
        $lookups = [
            // Order statuses
            ['type' => 'order_status', 'code' => 'pending',    'label' => 'Pending',    'sort_order' => 1],
            ['type' => 'order_status', 'code' => 'processing', 'label' => 'Processing', 'sort_order' => 2],
            ['type' => 'order_status', 'code' => 'completed',  'label' => 'Completed',  'sort_order' => 3],
            ['type' => 'order_status', 'code' => 'cancelled',  'label' => 'Cancelled',  'sort_order' => 4],
            ['type' => 'order_status', 'code' => 'refunded',   'label' => 'Refunded',   'sort_order' => 5],

            // Payment methods
            ['type' => 'payment_method', 'code' => 'cash',            'label' => 'Cash',             'sort_order' => 1],
            ['type' => 'payment_method', 'code' => 'card',            'label' => 'Card',             'sort_order' => 2],
            ['type' => 'payment_method', 'code' => 'cash_on_delivery', 'label' => 'Cash on Delivery', 'sort_order' => 3],

            // Sale statuses
            ['type' => 'sale_status', 'code' => 'completed', 'label' => 'Completed', 'sort_order' => 1],
            ['type' => 'sale_status', 'code' => 'refunded',  'label' => 'Refunded',  'sort_order' => 2],
            ['type' => 'sale_status', 'code' => 'voided',    'label' => 'Voided',    'sort_order' => 3],

            // Sale channels
            ['type' => 'sale_channel', 'code' => 'pos',    'label' => 'Point of Sale', 'sort_order' => 1],
            ['type' => 'sale_channel', 'code' => 'online', 'label' => 'Online Store',  'sort_order' => 2],

            // Stock reasons
            ['type' => 'stock_reason', 'code' => 'sale',              'label' => 'Sale',              'sort_order' => 1],
            ['type' => 'stock_reason', 'code' => 'manual_adjustment', 'label' => 'Manual Adjustment', 'sort_order' => 2],
            ['type' => 'stock_reason', 'code' => 'restock',           'label' => 'Restock',           'sort_order' => 3],
            ['type' => 'stock_reason', 'code' => 'damaged',           'label' => 'Damaged / Waste',   'sort_order' => 4],
            ['type' => 'stock_reason', 'code' => 'return',            'label' => 'Customer Return',   'sort_order' => 5],

            // Expense categories
            ['type' => 'expense_category', 'code' => 'rent',       'label' => 'Rent',              'sort_order' => 1],
            ['type' => 'expense_category', 'code' => 'utilities',  'label' => 'Utilities',         'sort_order' => 2],
            ['type' => 'expense_category', 'code' => 'salaries',   'label' => 'Salaries',          'sort_order' => 3],
            ['type' => 'expense_category', 'code' => 'supplies',   'label' => 'Office Supplies',   'sort_order' => 4],
            ['type' => 'expense_category', 'code' => 'marketing',  'label' => 'Marketing',         'sort_order' => 5],
            ['type' => 'expense_category', 'code' => 'other',      'label' => 'Other',             'sort_order' => 6],
        ];

        foreach ($lookups as $lookup) {
            Lookup::firstOrCreate(
                ['type' => $lookup['type'], 'code' => $lookup['code']],
                array_merge($lookup, ['is_active' => true])
            );
        }
    }

    private function seedSettings(): void
    {
        $defaults = [
            ['key' => 'app_name',            'value' => config('app.name'),  'group' => 'general'],
            ['key' => 'currency',            'value' => 'USD',               'group' => 'general'],
            ['key' => 'currency_symbol',     'value' => '$',                 'group' => 'general'],
            ['key' => 'low_stock_threshold', 'value' => '5',                 'group' => 'inventory'],
            ['key' => 'invoice_prefix',      'value' => 'INV-',              'group' => 'pos'],
            ['key' => 'receipt_footer',      'value' => 'Thank you for your purchase!', 'group' => 'pos'],
            ['key' => 'store_email',         'value' => config('mail.from.address'), 'group' => 'general'],
        ];

        foreach ($defaults as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }
    }

    private function seedNavigation(): void
    {
        // Clear and re-seed so sort order stays clean on fresh install
        NavigationItem::truncate();

        // Top-level items
        $catalog = NavigationItem::create([
            'label' => 'Catalog',
            'icon' => 'fas fa-tags',
            'permission_required' => 'view_products',
            'module' => 'Catalog',
            'sort_order' => 10,
        ]);

        $inventory = NavigationItem::create([
            'label' => 'Inventory',
            'icon' => 'fas fa-boxes',
            'permission_required' => 'view_inventory',
            'module' => 'Inventory',
            'sort_order' => 20,
        ]);

        $pos = NavigationItem::create([
            'label' => 'Point of Sale',
            'route_name' => 'admin.pos.index',
            'icon' => 'fas fa-cash-register',
            'permission_required' => 'operate_pos',
            'module' => 'POS',
            'sort_order' => 30,
        ]);

        $storefront = NavigationItem::create([
            'label' => 'Storefront',
            'icon' => 'fas fa-store',
            'permission_required' => 'manage_orders',
            'module' => 'Storefront',
            'sort_order' => 40,
        ]);

        $finance = NavigationItem::create([
            'label' => 'Finance',
            'icon' => 'fas fa-chart-line',
            'permission_required' => 'view_finance_reports',
            'module' => 'Finance',
            'sort_order' => 50,
        ]);

        // Catalog children (routes to be created in Phase 2)
        NavigationItem::create(['parent_id' => $catalog->id, 'label' => 'Products',   'route_name' => 'admin.products.index',   'icon' => 'far fa-circle', 'permission_required' => 'view_products',  'sort_order' => 1]);
        NavigationItem::create(['parent_id' => $catalog->id, 'label' => 'Categories', 'route_name' => 'admin.categories.index', 'icon' => 'far fa-circle', 'permission_required' => 'manage_products', 'sort_order' => 2]);

        // Inventory children (Phase 3)
        NavigationItem::create(['parent_id' => $inventory->id, 'label' => 'Stock Levels',    'route_name' => 'admin.inventory.index',   'icon' => 'far fa-circle', 'permission_required' => 'view_inventory',   'sort_order' => 1]);
        NavigationItem::create(['parent_id' => $inventory->id, 'label' => 'Adjust Stock',    'route_name' => 'admin.inventory.adjust',  'icon' => 'far fa-circle', 'permission_required' => 'manage_inventory', 'sort_order' => 2]);
        NavigationItem::create(['parent_id' => $inventory->id, 'label' => 'Movement History', 'route_name' => 'admin.inventory.history', 'icon' => 'far fa-circle', 'permission_required' => 'view_inventory',   'sort_order' => 3]);

        // Storefront children (Phase 5)
        NavigationItem::create(['parent_id' => $storefront->id, 'label' => 'Orders',  'route_name' => 'admin.orders.index',  'icon' => 'far fa-circle', 'permission_required' => 'view_orders',   'sort_order' => 1]);

        // Finance children (Phase 6)
        NavigationItem::create(['parent_id' => $finance->id, 'label' => 'Sales Report',   'route_name' => 'admin.finance.sales',   'icon' => 'far fa-circle', 'permission_required' => 'view_finance_reports', 'sort_order' => 1]);
        NavigationItem::create(['parent_id' => $finance->id, 'label' => 'Expenses',       'route_name' => 'admin.finance.expenses', 'icon' => 'far fa-circle', 'permission_required' => 'manage_expenses',     'sort_order' => 2]);
        NavigationItem::create(['parent_id' => $finance->id, 'label' => 'Profit Report',  'route_name' => 'admin.finance.profit',  'icon' => 'far fa-circle', 'permission_required' => 'view_finance_reports', 'sort_order' => 3]);
    }
}
