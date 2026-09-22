# Phase 4 — Point of Sale (POS)

## Overview

Phase 4 delivers the in-store checkout experience. A cashier opens a single-screen POS, searches for products or scans a barcode SKU, builds a cart, selects a payment method, and completes the sale in one action. On completion, stock is deducted from the inventory ledger synchronously, the sale is recorded to the audit trail synchronously, and a PDF receipt job is queued as a background side-effect. The cashier is immediately redirected to a printable receipt page.

---

## Module Structure

```
Modules/
└── POS/
    ├── app/
    │   ├── Events/            SaleCompleted
    │   ├── Http/
    │   │   ├── Controllers/   PosController
    │   │   └── Requests/      CompleteSaleRequest
    │   ├── Listeners/
    │   │   ├── DeductStock            ← sync
    │   │   ├── RecordSalesLedgerEntry ← sync
    │   │   └── GenerateReceiptPdf     ← queued
    │   ├── Models/            Sale, SaleItem
    │   ├── Providers/         POSServiceProvider, EventServiceProvider, RouteServiceProvider
    │   └── Repositories/
    │       ├── Contracts/     SaleRepositoryInterface
    │       └── Eloquent/      SaleRepository
    ├── database/
    │   ├── migrations/        create_sales_table, create_sale_items_table
    │   └── seeders/           POSDatabaseSeeder
    ├── resources/views/pos/
    │   ├── checkout.blade.php      ← single-screen POS
    │   ├── receipt.blade.php       ← printable receipt
    │   └── daily-summary.blade.php ← cashier handover view
    └── routes/
        └── web.php
```

---

## Database Tables

### `sales`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | Auto-increment |
| `sale_number` | varchar(30) | Unique — generated from `invoice_prefix` setting + zero-padded sequence |
| `channel` | varchar(20) | `lookups.code` where `type = sale_channel` (pos / online) |
| `total` | decimal(10,2) | Sum of all line totals |
| `payment_method` | varchar(20) | `lookups.code` where `type = payment_method` |
| `status` | varchar(20) | `lookups.code` where `type = sale_status` |
| `created_by` | bigint FK | → `users.id` — the cashier |
| `created_at / updated_at` | timestamps | |

**Indexes:** `created_by`, composite `(channel, created_at)` for daily summary queries.

### `sale_items`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | Auto-increment |
| `sale_id` | bigint FK | → `sales.id`, cascade delete |
| `product_id` | bigint FK | → `products.id`, restrict delete |
| `quantity` | integer | |
| `unit_price` | decimal(10,2) | Price **snapshot** at time of sale — not linked to current product price |

**Index:** composite `(sale_id, product_id)`.

> `unit_price` is a snapshot, not a live reference. If the product price changes later, historical receipts still show the correct price at the time of sale.

---

## Repository Pattern

### `SaleRepositoryInterface`

```php
createWithItems(array $saleData, array $items): Sale    // DB transaction
findById(int $id): ?Sale
findBySaleNumber(string $saleNumber): ?Sale
paginate(array $filters, int $perPage): LengthAwarePaginator
dailySummary(string $date): array                       // revenue, count, breakdown
nextSaleNumber(): string                                // INV-00001, INV-00002, …
```

`createWithItems()` wraps `Sale::create()` + all `SaleItem::create()` calls in a single `DB::transaction()`. If any item fails to insert, the entire sale is rolled back.

`nextSaleNumber()` reads `invoice_prefix` from `settings` and appends a zero-padded 5-digit sequence based on the last sale number.

Bound in `POSServiceProvider::register()`:
```php
$this->app->bind(SaleRepositoryInterface::class, SaleRepository::class);
```

---

## Event / Listener Chain: SaleCompleted

Per the system design, all three listeners fire on `SaleCompleted`. The first two are **synchronous** — the HTTP response cannot be sent until stock is confirmed deducted. The third is **queued**.

```
SaleCompleted
    → DeductStock          (sync)
          for each sale item:
              StockRepository::recordMovement({
                  product_id, quantity_change: -qty,
                  reason: 'sale', reference_type: 'Sale', reference_id: sale.id
              })
              → StockLevelChanged::dispatch(product, movement, newStock)
                  → CheckLowStockThreshold (queued, from Inventory module)

    → RecordSalesLedgerEntry  (sync)
          ActivityLog::record(sale, 'sale_completed', properties: {...})
          [Phase 6 Finance hook point — Finance module will also listen here]

    → GenerateReceiptPdf   (queued, ShouldQueue)
          v1: no-op placeholder
          future: generate PDF, store to disk, email
```

`DeductStock` calls `StockRepository::recordMovement()` — the same method used by the Inventory module's manual adjustment. This keeps the stock ledger consistent regardless of whether a unit left via POS or a manual write-off.

---

## Permissions

| Permission | Grants |
|---|---|
| `operate_pos` | All POS routes — checkout, complete sale, receipt, daily summary |

All POS routes sit behind `permission:operate_pos`. Owner, Manager, and Cashier all have this permission (seeded in Phase 1).

---

## Routes

```
GET  /admin/pos                           admin.pos.index          [operate_pos]
GET  /admin/pos/product-search            admin.pos.product-search [operate_pos]
GET  /admin/pos/daily-summary             admin.pos.daily-summary  [operate_pos]
POST /admin/pos/sales                     admin.pos.sales.store    [operate_pos]
GET  /admin/pos/sales/{sale}/receipt      admin.pos.sales.receipt  [operate_pos]
```

`admin.pos.index` matches the nav item seeded in Phase 1 — the sidebar link becomes active automatically.

---

## Pages

### Checkout Screen (`/admin/pos`)

Single-page layout split into two columns:

**Left — Product search:**
- Text input with 250ms debounce — calls `/admin/pos/product-search?q=` via fetch
- Exact SKU match checked first (barcode scan path), then LIKE name/SKU search
- Results rendered as product cards showing name, SKU, price, stock badge
- Out-of-stock products shown greyed out, non-clickable
- Clicking a product adds it to the cart (or increments quantity)

**Right — Cart + checkout:**
- Cart table with per-item +/− quantity buttons and remove button
- Running total updates in real-time via JavaScript
- Payment method selector (buttons from `lookups` where `type = payment_method`)
- Complete Sale button (disabled until cart has items AND payment method is selected)
- On submit: hidden inputs populate `items[n][product_id]`, `items[n][quantity]`, `items[n][unit_price]` for each cart row

### Receipt (`/admin/pos/sales/{sale}/receipt`)

- Printable monospace-style layout (Courier New font)
- Shows: store name, sale number, date/time, cashier, payment method
- Line items with quantity × unit price = line total
- Grand total
- Footer message from `receipt_footer` setting
- `@media print` CSS hides all nav/header/sidebar elements
- Print button calls `window.print()`

### Daily Summary (`/admin/pos/daily-summary`)

- Date picker — defaults to today
- Three stat cards: Total Revenue, Total Transactions, Average Sale Value
- Payment method breakdown table (revenue per method)
- Transaction list for the selected date with receipt links
- Useful for cashier handover at end of shift

---

## Sale Number Generation

```php
// Setting: invoice_prefix = 'INV-'
// Last sale: INV-00003
// Next:      INV-00004

$prefix = Setting::getValue('invoice_prefix', 'INV-');
$next = (int) substr($last, strlen($prefix)) + 1;
return $prefix . str_pad($next, 5, '0', STR_PAD_LEFT);
```

The prefix is configurable from `/admin/settings`. Changing the prefix mid-operation starts a new sequence from 00001 for that prefix.

---

## Product Search: Barcode Scan Support

The search field handles both typed input and barcode scanner input transparently:

- A barcode scanner typically sends the barcode string followed by Enter
- The search handler calls `ProductRepository::findBySku($term)` first — exact match
- If no exact SKU match, falls back to LIKE name/SKU search
- Returns up to 10 results as JSON with `id, name, sku, price, current_stock, image_url`
- `current_stock` is computed via `withSum()` in the query — single DB call

---

## Key Decisions

- **Single transaction for sale creation** — `createWithItems()` wraps the entire sale + items insert in `DB::transaction()`. A partial sale (some items inserted, sale header not yet created) is impossible.
- **`unit_price` snapshot** — prices are copied into `sale_items.unit_price` at the moment of sale. If a product's price is updated the next day, every historical receipt still shows the correct price.
- **Stock deduction is sync, not queued** — `DeductStock` does not implement `ShouldQueue`. Stock must be physically decremented before the cashier gets a receipt. A queued deduction would leave a window where the stock level is still "old" while the sale has been recorded.
- **`GenerateReceiptPdf` is queued** — in v1 the receipt is a rendered Blade view, not a stored PDF file. The listener is the extension point for future background PDF generation and email delivery, but it does nothing in v1.
- **Finance integration hook** — `RecordSalesLedgerEntry` currently only writes to `activity_log`. Phase 6 (Finance) adds another listener on `SaleCompleted` that writes to the financial ledger. No POS code changes are needed for that — Finance registers its own listener on the same event.
