# Phase 1 — Foundation: Auth, RBAC & Dynamic Dashboard

## Overview

Phase 1 establishes the skeleton that every other module plugs into. Nothing else in the application can function without roles, permissions, and navigation, so this phase ships first. The result is a fully working admin shell — login, role-based access control, a dynamic sidebar, and all the configuration screens an owner needs to manage the system.

---

## Packages Installed

| Package | Version | Purpose |
|---|---|---|
| `nwidart/laravel-modules` | 13.0 | Module-based architecture — each business domain is a self-contained module |
| `spatie/laravel-permission` | 8.3 | RBAC — roles, permissions, middleware, Blade directives |
| `barryvdh/laravel-dompdf` | 3.1 | PDF generation for receipts and reports (used from Phase 4) |
| `intervention/image` | 3.0 | Image resizing on product upload (used from Phase 2) |
| `wikimedia/composer-merge-plugin` | — | Merges each module's `composer.json` PSR-4 autoload into the root autoloader |

---

## Module Structure

```
Modules/
└── Auth/
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/   LoginController, DashboardController, UserController,
    │   │   │                  SettingController, LookupController, RoleController,
    │   │   │                  PermissionController, NavigationItemController
    │   │   ├── Middleware/    EnsureUserIsActive
    │   │   └── Requests/      LoginRequest, StoreUserRequest, UpdateUserRequest,
    │   │                      StoreSettingRequest, StoreLookupRequest
    │   ├── Models/            NavigationItem, Setting, Lookup, ActivityLog
    │   └── Providers/         AuthServiceProvider, RouteServiceProvider, EventServiceProvider
    ├── database/
    │   ├── migrations/
    │   └── seeders/           AuthDatabaseSeeder
    ├── resources/views/
    │   ├── layouts/           admin.blade.php  ← AdminLTE 3 shell
    │   ├── partials/          navigation.blade.php  ← dynamic sidebar
    │   ├── users/
    │   ├── roles/
    │   ├── permissions/
    │   ├── settings/
    │   ├── lookups/
    │   ├── navigation/
    │   └── login.blade.php, dashboard.blade.php
    └── routes/
        └── web.php
```

---

## Database Tables Created

### Spatie (package-managed, not hand-written)
| Table | Purpose |
|---|---|
| `roles` | Named roles — Owner, Manager, Cashier |
| `permissions` | Discrete capability names — e.g. `manage_products` |
| `role_has_permissions` | Which permissions a role grants |
| `model_has_roles` | Which role(s) a user holds |
| `model_has_permissions` | Permissions granted directly to a user |

### Custom
| Table | Key Columns | Notes |
|---|---|---|
| `users` (extended) | `is_active`, `meta` | Added to Laravel's default users table |
| `settings` | `key`, `value`, `group` | Runtime-configurable system values — no deploy needed to change behaviour |
| `lookups` | `type`, `code`, `label`, `sort_order`, `is_active` | Generic reference data replacing hardcoded enums |
| `navigation_items` | `parent_id`, `label`, `route_name`, `icon`, `permission_required`, `sort_order` | DB-driven dynamic sidebar |
| `activity_log` | `subject_type`, `subject_id`, `causer_id`, `description`, `properties` | Append-only audit trail |

---

## Roles & Permissions

### Roles seeded
| Role | Description |
|---|---|
| **Owner** | All permissions — full access to every module |
| **Manager** | Catalog, inventory, POS, finance, orders — no user/settings management |
| **Cashier** | POS only |

### Permissions seeded (11 total)
```
view_products       manage_products
view_inventory      manage_inventory
operate_pos
view_finance_reports  manage_expenses
view_orders           manage_orders
manage_users
manage_settings
```

Roles and permissions are **editable data** — add new permissions or change role assignments from `/admin/permissions` and `/admin/roles` without any code change.

---

## Authentication

- **Login** — email + password via Laravel's built-in session auth
- **Active check** — `EnsureUserIsActive` middleware runs on every admin request; inactive users are logged out immediately
- **Logout** — POST to `/logout`, session invalidated and token regenerated
- **Guest middleware** — login page redirects to dashboard if already authenticated
- **Unauthenticated redirect** — registered in `bootstrap/app.php` via Laravel 11's `withExceptions`

All middleware aliases registered in `bootstrap/app.php`:
```php
'role'               => RoleMiddleware::class,
'permission'         => PermissionMiddleware::class,
'role_or_permission' => RoleOrPermissionMiddleware::class,
'active.user'        => EnsureUserIsActive::class,
```

---

## Dynamic Navigation System

The sidebar is driven entirely from the `navigation_items` table — no hardcoded menu in Blade. The logic lives in `partials/navigation.blade.php`:

1. Queries top-level items with children eager-loaded, ordered by `sort_order`
2. For each item, checks `auth()->user()->can($item->permission_required)` — items the user lacks permission for are skipped
3. Items with children render as AdminLTE treeview accordions; items without children render as flat links
4. Undefined routes (future modules not yet built) render as `#` via `Route::has()` check — no 500 errors

The Administration section at the bottom (Users, Roles & Permissions, Settings) is always rendered from Blade `@can` directives rather than the DB table, so it can never be accidentally removed.

### Managing the navigation
Go to **Settings → Navigation Menu** (`/admin/navigation`) to add, edit, reorder, or hide any menu item. Deleting a parent re-parents its children to top-level automatically.

---

## Settings System

`/admin/settings` reads and writes the `settings` table. Values are grouped (general, inventory, pos) for display.

Default values seeded:
| Key | Default | Group |
|---|---|---|
| `app_name` | Laravel (from config) | general |
| `currency` | USD | general |
| `currency_symbol` | $ | general |
| `low_stock_threshold` | 5 | inventory |
| `invoice_prefix` | INV- | pos |
| `receipt_footer` | Thank you for your purchase! | pos |
| `store_email` | (from mail config) | general |

Access a setting anywhere in the app: `Setting::getValue('currency', 'USD')`.

---

## Lookups System

The `lookups` table replaces hardcoded status/reason strings. Types seeded:

| Type | Codes |
|---|---|
| `order_status` | pending, processing, completed, cancelled, refunded |
| `payment_method` | cash, card, cash_on_delivery |
| `sale_status` | completed, refunded, voided |
| `sale_channel` | pos, online |
| `stock_reason` | sale, manual_adjustment, restock, damaged, return |
| `expense_category` | rent, utilities, salaries, supplies, marketing, other |

New values are added from `/admin/lookups` — no migration required.

---

## Default Seed Credentials

| Field | Value |
|---|---|
| Email | `owner@admin.com` |
| Password | `password` |
| Role | Owner (all permissions) |

---

## Routes Summary

```
GET  /login                     → LoginController@showLoginForm
POST /login                     → LoginController@login
POST /logout                    → LoginController@logout

GET  /admin                     → DashboardController@index
GET  /admin/users               → UserController@index          [manage_users]
GET  /admin/roles               → RoleController@index          [manage_users]
GET  /admin/permissions         → PermissionController@index    [manage_users]
GET  /admin/settings            → SettingController@index       [manage_settings]
GET  /admin/lookups             → LookupController@index        [manage_settings]
GET  /admin/navigation          → NavigationItemController@index [manage_settings]
```

All admin routes sit behind `auth` + `active.user` middleware. Resource routes follow Laravel conventions.

---

## Key Decisions

- **No registration page** — users are created by an Owner from `/admin/users`. This is a staff-management system, not a public sign-up app.
- **Deactivate, never delete** — users are deactivated (`is_active = false`) rather than hard-deleted to preserve audit trail integrity.
- **Roles are editable data** — adding a permission or changing what a role can do is a data operation, not a code deployment.
- **`btn-sm` not `btn-xs`** — Bootstrap 4 (used by AdminLTE 3) dropped `btn-xs`; use `btn-sm` for small action buttons throughout the project.
- **No SRI integrity hash on Font Awesome CDN** — the integrity hash caused silent load failures on some pages; removed to ensure consistent icon rendering.
