<?php

use Illuminate\Support\Facades\Route;
use Modules\Storefront\Http\Controllers\CartController;
use Modules\Storefront\Http\Controllers\CheckoutController;
use Modules\Storefront\Http\Controllers\OrderController;
use Modules\Storefront\Http\Controllers\StorefrontController;

/*
|--------------------------------------------------------------------------
| Public Storefront Routes — no auth required
|--------------------------------------------------------------------------
*/
Route::middleware('web')
    ->name('storefront.')
    ->group(function (): void {

        // Product listing & detail — static 'category' segment before {product} wildcard
        Route::get('/shop', [StorefrontController::class, 'index'])
            ->name('products.index');

        Route::get('/shop/category/{slug}', [StorefrontController::class, 'byCategory'])
            ->name('products.category');

        Route::get('/shop/products/{product}', [StorefrontController::class, 'show'])
            ->name('products.show');

        // Cart
        Route::get('/cart', [CartController::class, 'index'])
            ->name('cart.index');

        Route::post('/cart/add', [CartController::class, 'add'])
            ->name('cart.add');

        Route::patch('/cart/{productId}', [CartController::class, 'update'])
            ->name('cart.update');

        Route::delete('/cart/{productId}', [CartController::class, 'remove'])
            ->name('cart.remove');

        Route::post('/cart/clear', [CartController::class, 'clear'])
            ->name('cart.clear');

        // Checkout — static 'confirmation' before {order} wildcard
        Route::get('/checkout', [CheckoutController::class, 'index'])
            ->name('checkout.index');

        Route::post('/checkout', [CheckoutController::class, 'store'])
            ->name('checkout.store');

        Route::get('/checkout/confirmation/{order}', [CheckoutController::class, 'confirmation'])
            ->name('checkout.confirmation');
    });

/*
|--------------------------------------------------------------------------
| Admin Orders Panel — authenticated + permission guarded
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active.user'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        // View orders — matches seeded nav route admin.orders.index
        Route::middleware('permission:view_orders')->group(function (): void {
            Route::get('orders', [OrderController::class, 'index'])
                ->name('orders.index');

            Route::get('orders/{order}', [OrderController::class, 'show'])
                ->name('orders.show');
        });

        // Update order status
        Route::middleware('permission:manage_orders')->group(function (): void {
            Route::put('orders/{order}', [OrderController::class, 'update'])
                ->name('orders.update');
        });
    });
