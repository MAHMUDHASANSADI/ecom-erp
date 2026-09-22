<?php

use Illuminate\Support\Facades\Route;

// Root redirect — authenticated users go to admin dashboard, guests go to the shop
Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'admin.dashboard' : 'storefront.products.index');
});
