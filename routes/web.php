<?php

use Illuminate\Support\Facades\Route;

// Redirect root to admin dashboard (or login if unauthenticated)
Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'admin.dashboard' : 'login');
});
