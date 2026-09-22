# Phase 6 — Finance & Reporting

## Overview

Phase 6 delivers the financial layer of the platform. It covers two concerns: **expense tracking** (manual, category-tagged entries) and **reporting** (sales summaries, profit calculations, and exports). All financial data is derived from real records — sales come from the `sales` + `sale_items` tables built in Phase 4, cost of goods comes from `products.cost_price`, and expenses come from the new `expenses` table. No separate financial ledger table is maintained; reports query source data directly.

Phase 6 also replaces the placeholder dashboard with a fully live dashboard — permission-gated stat cards, a 6-month revenue chart, recent sales, low-stock alerts, and pending orders.

---

## Module Structure

```
Modules/
└── Finance/
    ├── app/
    │   ├── Events/            ExpenseLogged
    │   ├── Http/
    │   │   ├── Controllers/   ExpenseController, ReportController
    │   │   └── Requests/      StoreExpenseRequest
    │   ├── Listeners/         RecalculateMonthlyProfitSnapshot (queued)
    │   ├── Models/            Expense
    │   ├── Providers/         FinanceServiceProvider, EventServiceProvider, RouteServiceProvider
    │   └── Repositories/
    │       ├── Contracts/     ExpenseRepositoryInterface
    │       └── Eloquent/      ExpenseRepository
    ├── database/
    │   ├── migrations/        create_expenses_table
    │   └── seeders/           FinanceDatabaseSeeder
    ├── resources/views/
    │   ├── expenses/          index.blade.php, create.blade.php, edit.blade.php, _form.blade.php
    │   └── reports/
    │       ├── sales.blade.php
    │       ├── profit.blade.php
    │       └── pdf/
    │           ├── sales.blade.php
    │           └── profit.blade.php
    └── routes/
        └── web.php
```

**Also updated in Phase 6:**
- `Modules/Auth/app/Http/Controllers/DashboardController.php` — live data queries replacing placeholder `—` values
- `Modules/Auth/resources/views/dashboard.blade.php` — complete rewrite with real stats, chart, tables, and quick action links

---

## Database Table: `expenses`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | Auto-increment |
| `category` | varchar(100) | `lookups.code` where `type = expense_category` |
| `amount` | decimal(10,2) | |
| `note` | text nullable | Free-text description |
| `expense_date` | date | Date the expense occurred (not necessarily today) |
| `created_by` | bigint FK | → `users.id`, restrict delete |
| `created_at / updated_at` | timestamps | |

**Indexes:** `expense_date`, `category`, `created_by`.

> There is no separate financial ledger table. Sales revenue is read directly from `sales` (completed status). COGS is computed by joining `sale_items → products` and multiplying `quantity × cost_price`. Expenses are the only new table.

---

## Repository Pattern

### `ExpenseRepositoryInterface`

```php
paginate(array $filters, int $perPage): LengthAwarePaginator
findById(int $id): ?Expense
create(array $data): Expense
update(Expense $expense, array $data): Expense
delete(Expense $expense): void

totalsForPeriod(string $from, string $to): array{
    total: float,
    by_category: array<string, float>
}

monthlyTotals(int $year): array<int, float>   // month_number => total, all 12 months filled
```

Bound in `FinanceServiceProvider::register()`:
```php
$this->app->bind(ExpenseRepositoryInterface::class, ExpenseRepository::class);
```

---

## Event / Listener: ExpenseLogged

Per the system design, `ExpenseLogged` fires after every expense is created. Its listener is queued.

```
ExpenseLogged
    → RecalculateMonthlyProfitSnapshot (queued, ShouldQueue)
          ActivityLog::record($expense, 'expense_logged', properties: [...])
          [Extension point — future: recalculate and persist monthly snapshot table]
```

The listener logs to `activity_log` in v1. The design anticipates a future `monthly_profit_snapshots` table that caches pre-computed P&L figures to avoid querying millions of rows on every report load.

---

## Permissions

| Permission | Grants |
|---|---|
| `view_finance_reports` | Sales report, profit report, expense list, all CSV/PDF exports |
| `manage_expenses` | Create, edit, delete expenses |

Both permissions are seeded in Phase 1 and assigned to Owner and Manager roles.

---

## Routes

```
GET  /admin/finance/sales                  admin.finance.sales          [view_finance_reports]
GET  /admin/finance/profit                 admin.finance.profit         [view_finance_reports]
GET  /admin/finance/sales/export/csv       admin.finance.sales.csv      [view_finance_reports]
GET  /admin/finance/expenses/export/csv    admin.finance.expenses.csv   [view_finance_reports]
GET  /admin/finance/sales/export/pdf       admin.finance.sales.pdf      [view_finance_reports]
GET  /admin/finance/profit/export/pdf      admin.finance.profit.pdf     [view_finance_reports]

GET  /admin/finance/expenses               admin.finance.expenses       [view_finance_reports]
GET  /admin/finance/expenses/create        admin.finance.expenses.create [manage_expenses]
POST /admin/finance/expenses               admin.finance.expenses.store  [manage_expenses]
GET  /admin/finance/expenses/{id}/edit     admin.finance.expenses.edit   [manage_expenses]
PUT  /admin/finance/expenses/{id}          admin.finance.expenses.update [manage_expenses]
DELETE /admin/finance/expenses/{id}        admin.finance.expenses.destroy [manage_expenses]
```

Route names match the navigation items seeded in Phase 1 (`admin.finance.sales`, `admin.finance.expenses`, `admin.finance.profit`).

> Static segments (`create`, `export/csv`, `export/pdf`) are registered before `{expense}` wildcard routes to prevent 404 on those paths.

---

## Pages

### Expense List (`/admin/finance/expenses`)

- Filterable by category, date from, date to
- Period total shown in the card header when a date filter is active
- Edit and delete buttons per row (guarded by `@can('manage_expenses')`)
- Link to Log Expense and CSV export

### Expense Form (create/edit)

- Shared `_form.blade.php` partial used by both create and edit views
- Category dropdown from `Lookup::ofType('expense_category')` — new categories added via Lookups admin without code change
- Amount field with `$` prefix, minimum `0.01`
- Date defaults to today on create
- Optional note textarea

### Sales Report (`/admin/finance/sales`)

- Month/year period picker (defaults to current month)
- Three headline stat cards: Monthly Revenue, Transactions, Average Sale Value
- Daily sales table: date, transaction count, revenue
- Top 10 products by revenue for the period (join through `sale_items`)
- 12-month bar chart (current year) with progress bars
- CSV and PDF export buttons

### Profit Report (`/admin/finance/profit`)

- Month/year period picker
- Four headline stat cards: Revenue, COGS, Expenses, Net Profit
- Full P&L table:
  - Revenue (completed sales)
  - minus Cost of Goods Sold (`sale_items.quantity × products.cost_price`)
  - = Gross Profit (with gross margin %)
  - minus Operating Expenses (from `expenses` table)
  - = Net Profit
- Expense breakdown by category
- 12-month trend table with YTD totals row
- PDF export button

### PDF — Sales (`/admin/finance/sales/export/pdf`)

Tabular layout via dompdf: sale number, date, channel, payment method, cashier, total. Grand total in footer.

### PDF — Profit (`/admin/finance/profit/export/pdf`)

P&L statement layout: income section, COGS section, gross profit, expenses by category, net profit.

---

## Profit Calculation

```
Revenue     = SUM(sales.total) WHERE status='completed' AND date IN period
COGS        = SUM(sale_items.quantity × products.cost_price)
              JOIN via sale_items.sale_id → sales (completed, in period)
              JOIN via sale_items.product_id → products
Gross Profit = Revenue − COGS
Expenses    = SUM(expenses.amount) WHERE expense_date IN period
Net Profit  = Gross Profit − Expenses
Gross Margin % = (Gross Profit / Revenue) × 100
```

This is a **simple cash-basis profit** calculation — no depreciation, no tax, no accruals. Full double-entry accounting is explicitly out of scope for v1.

---

## Dynamic Dashboard (updated in Phase 6)

`DashboardController` now injects `StockRepositoryInterface` and queries all modules server-side. Every widget is permission-gated — a Cashier sees only POS stats; an Owner sees the full dashboard.

### Stat Cards

| Card | Data Source | Permission |
|---|---|---|
| Today's Transactions | `Sale::completed()->today()->count()` | `operate_pos` |
| Today's Revenue | `Sale::completed()->today()->sum('total')` | `view_finance_reports` |
| Low Stock Alerts | `StockRepository::lowStockProducts($threshold)->count()` | `view_inventory` |
| Pending Orders | `Order::pending()->count()` | `view_orders` |
| Active Products | `Product::active()->count()` | `view_products` |

### Revenue Chart

6-month bar chart (Chart.js 3) showing monthly revenue. Built by grouping completed sales by `YEAR/MONTH(created_at)`. Footer shows this month's revenue, expenses, and estimated net in one line.

### Recent Sales Table

Last 8 completed sales with relative timestamp (`diffForHumans()`), payment method badge, total, and receipt link.

### Low Stock List

Top 6 products at or below `low_stock_threshold` setting, ordered by lowest stock first. Each row has a Restock shortcut linking directly to the adjust screen pre-selecting that product.

### Pending Orders List

5 most recent pending storefront orders with customer name, relative time, and a Process shortcut.

---

## Demo Data Seeded

5 expenses seeded in `FinanceDatabaseSeeder` (idempotent — skips if expenses already exist):

| Category | Amount | Note |
|---|---|---|
| rent | $1,500.00 | Monthly store rent |
| utilities | $180.00 | Electricity & water |
| salaries | $3,200.00 | Staff salaries |
| supplies | $95.00 | Office stationery |
| marketing | $250.00 | Social media ads |

---

## Key Decisions

- **No separate ledger table** — revenue is read directly from `sales`. A separate financial ledger table would require dual-write on every sale and introduce sync bugs. The `RecordSalesLedgerEntry` listener (Phase 4) is the hook point if a dedicated ledger is added in v2.
- **COGS via join** — cost is computed by joining `sale_items` to `products.cost_price` at query time rather than snapshotting cost into `sale_items`. This means if a product's cost price changes, historical COGS recalculates. For v1 with a single-tenant deployment this is acceptable; v2 would snapshot cost into `sale_items` the same way `unit_price` is already snapshotted.
- **`expense_date` not `created_at`** — expenses are entered on a specific date that may differ from when they were logged (e.g. entering last month's rent today). Reports filter on `expense_date`, not `created_at`.
- **CSV and PDF both available** — CSV for import into spreadsheet tools; PDF for printing and sharing. Both export the same period as the on-screen report.
- **`view_finance_reports` covers expense list** — even non-manage users (Managers) can view the expense list to understand costs, but cannot create/edit/delete without `manage_expenses`.
