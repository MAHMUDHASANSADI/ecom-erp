<?php

use Illuminate\Support\Facades\Route;
use Modules\Catalog\Http\Controllers\CategoryController;
use Modules\Catalog\Http\Controllers\ProductController;

Route::middleware(['auth', 'active.user'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        // ---------------------------------------------------------------
        // Products
        // ---------------------------------------------------------------
        // Static segments MUST come before wildcard {product} routes
        Route::middleware('permission:manage_products')->group(function (): void {
            Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
            Route::post('products', [ProductController::class, 'store'])->name('products.store');
        });

        Route::middleware('permission:view_products')->group(function (): void {
            Route::get('products', [ProductController::class, 'index'])->name('products.index');
            Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
        });

        Route::middleware('permission:manage_products')->group(function (): void {
            Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
            Route::put('products/{product}', [ProductController::class, 'update'])->name('products.update');
            Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
            Route::patch('products/{product}/toggle', [ProductController::class, 'toggleActive'])->name('products.toggle');
        });

        // ---------------------------------------------------------------
        // Categories
        // ---------------------------------------------------------------
        Route::middleware('permission:manage_products')->group(function (): void {
            Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
            Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        });

        Route::middleware('permission:view_products')->group(function (): void {
            Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        });

        Route::middleware('permission:manage_products')->group(function (): void {
            Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
            Route::put('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
            Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        });
    });
