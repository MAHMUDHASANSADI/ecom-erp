# Phase 3 — Inventory Management

## Overview

Phase 3 implements the stock tracking layer. Per the system design, stock is never stored as a single mutable quantity field — it is derived at runtime by summing an **append-only ledger** (`stock_movements`). Every stock change, whether from a manual adjustment, a sale, or a return, is a new row in that table. Nothing is ever updated or deleted. This gives a full, auditable history of every unit that has ever entered or left the store.

---

## Module Structure

```
Modules/
└── Inventory/
    ├── app/
    │   ├── Events/            StockLevelChanged
    │   ├── Http/
    │   │   ├── Controllers/   InventoryController
    │   │   └── Requests/      StockAdjustmentRequest
    │   ├── Listeners/         CheckLowStockThreshold
    │   ├── Models/            StockMovement
    │   ├── Providers/         InventoryServiceProvider, EventServiceProvider, RouteServiceProvider
    │   └── Repositories/
    │       ├── Contracts/     StockRepositoryInterface
    │       └── Eloquent/      StockRepository
    ├── database/
    │   ├── migrations/        create_stock_movements_table
    │   └── seeders/           InventoryDatabaseSeeder
    ├── resources/views/stock/
    │   ├── index.blade.php          ← stock levels list
    │   ├── adjust.blade.php         ← manual adjustment form
    │   ├── history.blade.php        ← full movement log
    │   └── product-history.blade.php ← per-product movement history
    └── routes/
        └── web.php
```

**Also modified in Phase 3:**
- `Modules/Catalog/app/Models/Product.php` — added `stockMovements()` HasMany and `currentStock` computed accessor
- `Modules/Catalog/resources/views/products/show.blade.php` — stock card now shows live count and links to inventory

---

## Database Table: `stock_movements`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | Auto-increment |
| `product_id` | bigint FK | → `products.id`, cascade on delete |
| `quantity_change` | integer | Positive = stock in, negative = stock out |
| `reason` | varchar(50) | `lookups.code` where `type = stock_reason` |
| `reference_type` | varchar(100) nullable | e.g. `Sale`, `ManualAdjustment`, `OpeningStock` |
| `reference_id` | bigint nullable | ID of the referenced record |
| `notes` | text nullable | Free-text context for the movement |
| `created_by` | bigint FK | → `users.id`, restrict on delete |
| `created_at` | timestamp | Auto-set on insert — **no `updated_at`** |

**Indexes:** composite `(product_id, created_at)` for fast current-stock and history queries; `created_by`.

> There is **no `stock_levels` table**. Current stock = `SELECT SUM(quantity_change) FROM stock_movements WHERE product_id = ?`. This is the system design's explicit choice.

---

## How Current Stock is Calculated

Anywhere current stock is needed:

```php
// Single product (via Product model accessor)
$product->current_stock;

// Single product (via repository)
$stock->currentStock($productId);  // int

// All products at once — returns array<product_id, stock>
$stock->allCurrentStock();

// Paginated list with withSum() — one query
Product::withSum('stockMovements as current_stock', 'quantity_change')
```

`withSum()` is used for list pages to avoid N+1. The `current_stock` accessor on `Product` is used for single-record pages.

---

## StockRepositoryInterface

```php
currentStock(int $productId): int
allCurrentStock(): array<int, int>
stockLevelsPaginated(array $filters, int $perPage): LengthAwarePaginator
lowStockProducts(int $threshold): Collection
recordMovement(array $data): StockMovement
historyPaginated(array $filters, int $perPage): LengthAwarePaginator
```

Bound in `InventoryServiceProvider::register()`:
```php
$this->app->bind(StockRepositoryInterface::class, StockRepository::class);
```

---

## Event / Listener: Low-Stock Detection

Per the system design doc, `StockLevelChanged` fires **synchronously** after every stock movement. The listener `CheckLowStockThreshold` then checks whether the new stock level is at or below the configured threshold.

```
StockLevelChanged (sync)
    → CheckLowStockThreshold (queued)
        reads:  Setting::getValue('low_stock_threshold', 5)
        writes: ActivityLog::record(..., 'low_stock_alert', properties: [...])
```

`CheckLowStockThreshold` implements `ShouldQueue` — the database check is fast but the notification side-effect (future: email manager) should not block the HTTP response.

The `low_stock_threshold` setting is already seeded in Phase 1 (`value = '5'`, group `inventory`) and is editable from the admin Settings page at runtime.

---

## Permissions

| Permission | Grants |
|---|---|
| `view_inventory` | Stock levels index, movement history, per-product history |
| `manage_inventory` | Adjust stock form and POST |

Both permissions were seeded in Phase 1 and are already assigned to Owner and Manager roles.

---

## Routes

```
GET  /admin/inventory                              admin.inventory.index           [view_inventory]
GET  /admin/inventory/adjust                       admin.inventory.adjust          [manage_inventory]
POST /admin/inventory/adjust                       admin.inventory.store-adjustment [manage_inventory]
GET  /admin/inventory/history                      admin.inventory.history         [view_inventory]
GET  /admin/inventory/products/{product}/history   admin.inventory.product-history [view_inventory]
```

Route names match exactly the navigation items seeded in Phase 1 — the sidebar links become active automatically once the module is enabled.

---

## Pages

### Stock Levels (`/admin/inventory`)
- Lists all products with current stock, colour-coded badges: green (OK), amber (low stock), red (zero)
- Low-stock banner at top when any products are at/below threshold
- Filters: name/SKU search, category, "low stock only" checkbox
- Each row: history button, adjust button (manage_inventory only)

### Adjust Stock (`/admin/inventory/adjust`)
- Product select shows current stock next to each product name
- Adjustment type: Add / Remove (radio)
- Quantity field (min 1)
- Reason dropdown populated from `lookups` where `type = stock_reason`
- Optional notes field
- On save: writes `StockMovement`, logs to `activity_log`, fires `StockLevelChanged`
- Pre-selects product when `?product_id=X` passed in query string (linked from stock levels and product detail pages)

### Movement History (`/admin/inventory/history`)
- Full chronological log of all movements across all products
- Filters: product, reason, date from/to
- Shows: date, product name+SKU, reason badge, +/− quantity badge, notes, created-by user

### Per-Product History (`/admin/inventory/products/{id}/history`)
- Summary card: product name, SKU, category, current stock
- Movement table scoped to that product
- Quick-action buttons: Adjust Stock, View Product

---

## Stock Reasons (from `lookups` table)

| Code | Label |
|---|---|
| `sale` | Sale |
| `manual_adjustment` | Manual Adjustment |
| `restock` | Restock |
| `damaged` | Damaged / Waste |
| `return` | Customer Return |

New reasons can be added at runtime from `/admin/lookups` without any code change.

---

## Opening Stock Seeded

| SKU | Product | Opening Qty |
|---|---|---|
| IPH-15-PRO | iPhone 15 Pro | 25 |
| SAM-S24 | Samsung Galaxy S24 | 30 |
| MBP-14-M3 | MacBook Pro 14" | 10 |
| ACC-USBC-1M | USB-C Charging Cable | 150 |
| BEV-WATER-500 | Mineral Water 500ml | 200 |
| SNK-NUTS-200 | Mixed Nuts 200g | 80 |
| OFF-A4-500 | A4 Paper Ream | 60 |
| OFF-PEN-10 | Ballpoint Pen Box | 100 |

All seeded with `reason = restock`, `reference_type = OpeningStock`. Seeder is idempotent — re-running it will not create duplicate entries.

---

## Key Decisions

- **No stock_levels table** — current stock is computed from the ledger. This avoids dual-write bugs (ledger row + update mutable field) and means historical stock at any point in time is always queryable.
- **`withSum()` not `sum()` in loops** — list pages use Eloquent's `withSum()` to compute aggregate stock in a single query rather than N+1 per-product queries.
- **`reason` validated against `lookups` table** — `StockAdjustmentRequest` validates `reason` using `exists:lookups,code,type,stock_reason`. New reasons added to the lookups table are automatically valid without code changes.
- **POS integration hook** — `StockRepository::recordMovement()` is the single write path for all stock changes. Phase 4 (POS) will call this same method when completing a sale, passing `reference_type = Sale` and the sale ID, keeping the ledger consistent regardless of source.
