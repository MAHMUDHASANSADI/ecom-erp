<?php

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;
use Modules\Storefront\Http\Controllers\AiChatController;
use Modules\Storefront\Http\Controllers\CartController;
use Modules\Storefront\Http\Controllers\CheckoutController;
use Modules\Storefront\Http\Controllers\OrderController;
use Modules\Storefront\Http\Controllers\PaymentController;
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

        // -----------------------------------------------------------------------
        // SSLCommerz payment callbacks
        // Static 'confirmation', 'failed', 'retry', 'initiate' before {order} wildcard
        // IPN must be CSRF-exempt — excluded via VerifyCsrfToken or web middleware
        // -----------------------------------------------------------------------

        // Initiate payment (called after order creation)
        Route::get('/checkout/payment/initiate/{order}', [PaymentController::class, 'initiate'])
            ->name('payment.initiate');

        // Callbacks from SSLCommerz (GET or POST — CSRF excluded below)
        Route::match(['get', 'post'], '/checkout/payment/success', [PaymentController::class, 'success'])
            ->name('payment.success')
            ->withoutMiddleware([VerifyCsrfToken::class]);

        Route::match(['get', 'post'], '/checkout/payment/fail', [PaymentController::class, 'fail'])
            ->name('payment.fail')
            ->withoutMiddleware([VerifyCsrfToken::class]);

        Route::match(['get', 'post'], '/checkout/payment/cancel', [PaymentController::class, 'cancel'])
            ->name('payment.cancel')
            ->withoutMiddleware([VerifyCsrfToken::class]);

        Route::post('/checkout/payment/ipn', [PaymentController::class, 'ipn'])
            ->name('payment.ipn')
            ->withoutMiddleware([VerifyCsrfToken::class]);

        // Failed page (GET) — static before {order}
        Route::get('/checkout/payment/failed/{order}', [PaymentController::class, 'failed'])
            ->name('payment.failed');

        // Retry payment (GET)
        Route::get('/checkout/payment/retry/{order}', [PaymentController::class, 'retry'])
            ->name('payment.retry');

        // Switch to COD (POST)
        Route::post('/checkout/payment/switch-to-cod/{order}', [PaymentController::class, 'switchToCod'])
            ->name('payment.switch-to-cod');

        // Pages
        Route::view('/about', 'storefront::pages.about')->name('about');
        Route::view('/contact', 'storefront::pages.contact')->name('contact');

        // AI Chatbot Assistant
        Route::post('/shop/chat/message', [AiChatController::class, 'sendMessage'])
            ->middleware('throttle:30,1')
            ->name('chat.message');

        Route::post('/shop/chat/clear', [AiChatController::class, 'clearHistory'])
            ->name('chat.clear');
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
