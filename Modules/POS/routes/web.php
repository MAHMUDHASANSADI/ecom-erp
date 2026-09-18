<?php

use Illuminate\Support\Facades\Route;
use Modules\POS\Http\Controllers\PosController;

Route::middleware(['auth', 'active.user', 'permission:operate_pos'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        // Main checkout screen — matches seeded nav admin.pos.index
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');

        // Product search (AJAX/JSON)
        Route::get('pos/product-search', [PosController::class, 'productSearch'])
            ->name('pos.product-search');

        // Daily summary
        Route::get('pos/daily-summary', [PosController::class, 'dailySummary'])
            ->name('pos.daily-summary');

        // Complete sale — static route must come before {sale} wildcard
        Route::post('pos/sales', [PosController::class, 'store'])
            ->name('pos.sales.store');

        // Receipt — {sale} wildcard after static segments
        Route::get('pos/sales/{sale}/receipt', [PosController::class, 'receipt'])
            ->name('pos.sales.receipt');
    });
