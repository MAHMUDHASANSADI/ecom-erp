<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\DashboardController;
use Modules\Auth\Http\Controllers\LoginController;
use Modules\Auth\Http\Controllers\LookupController;
use Modules\Auth\Http\Controllers\NavigationItemController;
use Modules\Auth\Http\Controllers\PermissionController;
use Modules\Auth\Http\Controllers\RoleController;
use Modules\Auth\Http\Controllers\SettingController;
use Modules\Auth\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Guest Routes (unauthenticated)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Admin Routes (authenticated)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'active.user'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // User management
        Route::resource('users', UserController::class)
            ->except(['show'])
            ->middleware('permission:manage_users');

        // Settings + Lookups
        Route::middleware('permission:manage_settings')->group(function (): void {
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
            Route::post('settings', [SettingController::class, 'update'])->name('settings.update');

            Route::resource('lookups', LookupController::class)->except(['show']);
        });

        // Roles & Permissions (manage_users covers access — Owner only in practice)
        Route::middleware('permission:manage_users')->group(function (): void {
            Route::resource('roles', RoleController::class)->except(['show']);
            Route::resource('permissions', PermissionController::class)->only(['index', 'create', 'store', 'destroy']);
        });

        // Navigation menu management
        Route::middleware('permission:manage_settings')->group(function (): void {
            Route::resource('navigation', NavigationItemController::class)->except(['show']);
        });
    });
