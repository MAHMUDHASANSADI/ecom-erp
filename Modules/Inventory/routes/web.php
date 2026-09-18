<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;

Route::middleware(['auth', 'active.user'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        // View access — stock levels and movement history
        Route::middleware('permission:view_inventory')->group(function (): void {
            Route::get('inventory', [InventoryController::class, 'index'])
                ->name('inventory.index');

            Route::get('inventory/history', [InventoryController::class, 'history'])
                ->name('inventory.history');

            Route::get('inventory/products/{product}/history', [InventoryController::class, 'productHistory'])
                ->name('inventory.product-history');
        });

        // Write access — stock adjustments
        Route::middleware('permission:manage_inventory')->group(function (): void {
            Route::get('inventory/adjust', [InventoryController::class, 'adjust'])
                ->name('inventory.adjust');

            Route::post('inventory/adjust', [InventoryController::class, 'storeAdjustment'])
                ->name('inventory.store-adjustment');
        });
    });
