# Phase 2 — Catalog & Category Management

## Overview

Phase 2 introduces the product catalog — the core data layer that every other module depends on. POS needs products to sell, Inventory tracks stock per product, the Storefront displays them publicly, and Finance reports on their cost price. Getting the catalog right here means later phases just read from a stable, well-structured data source.

---

## Module Structure

```
Modules/
└── Catalog/
    ├── app/
    │   ├── Http/
    │   │   ├── Controllers/   CategoryController, ProductController
    │   │   └── Requests/      StoreCategoryRequest, UpdateCategoryRequest,
    │   │                      StoreProductRequest, UpdateProductRequest
    │   ├── Models/            Category, Product
    │   ├── Providers/         CatalogServiceProvider  ← binds repository interfaces
    │   └── Repositories/
    │       ├── Contracts/     CategoryRepositoryInterface, ProductRepositoryInterface
    │       └── Eloquent/      CategoryRepository, ProductRepository
    ├── database/
    │   ├── migrations/        create_categories_table, create_products_table
    │   └── seeders/           CatalogDatabaseSeeder
    ├── resources/views/
    │   ├── categories/        index.blade.php, create.blade.php, edit.blade.php
    │   └── products/          index.blade.php, create.blade.php, edit.blade.php, show.blade.php
    └── routes/
        └── web.php
```

---

## Database Tables

### `categories`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | Auto-increment |
| `parent_category_id` | bigint FK nullable | Self-referencing → `categories.id`; `nullOnDelete` |
| `name` | varchar(100) | |
| `slug` | varchar(120) | Unique — used in storefront URLs |
| `is_active` | boolean | Default true |
| `created_at / updated_at` | timestamps | |

**Indexes:** `parent_category_id`, `slug`

### `products`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | Auto-increment |
| `category_id` | bigint FK | → `categories.id`; `restrictOnDelete` (cannot delete a category with products) |
| `name` | varchar(150) | |
| `sku` | varchar(50) | Unique |
| `price` | decimal(10,2) | Selling price |
| `cost_price` | decimal(10,2) | Used for profit calculation in Finance module |
| `tax_class` | varchar(50) nullable | e.g. standard, zero |
| `description` | text nullable | |
| `image_path` | varchar(255) nullable | Relative path under `storage/public/products/` |
| `is_active` | boolean | Default true — controls storefront visibility |
| `meta` | json nullable | Extension point for future custom fields without a migration |
| `created_at / updated_at` | timestamps | |

**Indexes:** `category_id`, `is_active`

---

## Repository Pattern

Controllers and any future services type-hint the **interface**, never the Eloquent class directly. This keeps business logic testable and decouples the storage layer.

### `CategoryRepositoryInterface`
```php
allNested(): Collection       // Top-level with children eager-loaded — for admin index
allFlat(): Collection         // All categories flat — for dropdowns
findById(int $id): ?Category
findBySlug(string $slug): ?Category
create(array $data): Category
update(Category $category, array $data): Category
delete(Category $category): void
```

### `ProductRepositoryInterface`
```php
paginate(array $filters, int $perPage): LengthAwarePaginator
findById(int $id): ?Product
findBySku(string $sku): ?Product
create(array $data): Product
update(Product $product, array $data): Product
delete(Product $product): void
```

Bindings registered in `CatalogServiceProvider::register()`:
```php
$this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
$this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
```

---

## Category Features

### Nested categories
- Two levels supported in the UI (top-level and one sub-level), unlimited in the database
- `parent_category_id` is nullable — null means top-level
- `full_name` accessor returns `"Electronics > Smartphones"` for child categories

### Slug generation
`Category::generateSlug(string $name, ?int $excludeId)` produces a unique URL-safe slug. If `electronics` already exists, the next one becomes `electronics-1`, then `electronics-2`, and so on. The `$excludeId` parameter prevents the current record from conflicting with itself on update.

### Delete guards
- Cannot delete a category that has products assigned — must reassign products first
- Cannot delete a category that has sub-categories — must delete or reassign children first

---

## Product Features

### Image upload
- Accepts JPG, JPEG, PNG, WebP — max 2MB
- Stored via `Storage::disk('public')` in the `products/` folder
- `storage:link` must be run once (`php artisan storage:link`) — already done during setup
- On update: old image is deleted from disk before storing the new one
- On product delete: image is also deleted from disk
- `image_url` accessor returns the full public URL or `null` if no image

### Active toggle
A `PATCH /admin/products/{product}/toggle` route flips `is_active` without opening the edit form — one click from the product list.

### Search and filter
The products index accepts query parameters:
| Parameter | Behaviour |
|---|---|
| `search` | Matches against `name` or `sku` (LIKE) |
| `category_id` | Exact match on `category_id` |
| `is_active` | `1` = active only, `0` = inactive only, blank = all |

Filters persist through pagination via `->appends($filters)->links()`.

### Profit margin display
The product detail page calculates and shows gross margin percentage:
```
margin = ((price - cost_price) / price) × 100
```
Colour-coded: green > 20%, amber > 0%, red if cost exceeds price.

---

## Permissions

| Permission | Grants |
|---|---|
| `view_products` | View product and category lists and detail pages |
| `manage_products` | Create, edit, delete products and categories; toggle active status |

Route groups:
- `view_products` — index and show routes for both products and categories
- `manage_products` — all write routes (create, store, edit, update, destroy, toggle)

---

## Routes

```
GET    /admin/products                    → index   [view_products]
GET    /admin/products/{product}          → show    [view_products]
GET    /admin/products/create             → create  [manage_products]
POST   /admin/products                    → store   [manage_products]
GET    /admin/products/{product}/edit     → edit    [manage_products]
PUT    /admin/products/{product}          → update  [manage_products]
DELETE /admin/products/{product}          → destroy [manage_products]
PATCH  /admin/products/{product}/toggle   → toggle  [manage_products]

GET    /admin/categories                  → index   [view_products]
GET    /admin/categories/create           → create  [manage_products]
POST   /admin/categories                  → store   [manage_products]
GET    /admin/categories/{category}/edit  → edit    [manage_products]
PUT    /admin/categories/{category}       → update  [manage_products]
DELETE /admin/categories/{category}       → destroy [manage_products]
```

---

## Demo Data Seeded

### Categories (11 total)

```
Electronics
  ├── Smartphones
  ├── Laptops
  └── Accessories
Clothing
  ├── Men's Wear
  └── Women's Wear
Food & Beverages
  ├── Beverages
  └── Snacks
Office Supplies
```

### Products (8 total)

| SKU | Name | Price | Cost | Category |
|---|---|---|---|---|
| IPH-15-PRO | iPhone 15 Pro | $999.00 | $720.00 | Smartphones |
| SAM-S24 | Samsung Galaxy S24 | $849.00 | $600.00 | Smartphones |
| MBP-14-M3 | MacBook Pro 14" | $1,999.00 | $1,500.00 | Laptops |
| ACC-USBC-1M | USB-C Charging Cable | $19.99 | $5.00 | Accessories |
| BEV-WATER-500 | Mineral Water 500ml | $1.50 | $0.40 | Beverages |
| SNK-NUTS-200 | Mixed Nuts 200g | $6.99 | $3.00 | Snacks |
| OFF-A4-500 | A4 Paper Ream | $8.99 | $4.50 | Office Supplies |
| OFF-PEN-10 | Ballpoint Pen (Box of 10) | $4.99 | $1.50 | Office Supplies |

---

## Key Decisions

- **`restrictOnDelete` on `products.category_id`** — the database-level constraint prevents orphaned products. The application layer checks and gives a friendly error before the DB constraint fires.
- **`nullOnDelete` on `categories.parent_category_id`** — deleting a parent category demotes its children to top-level rather than cascading a delete.
- **Flat category options with indentation** — the product form `<select>` renders top-level categories followed by their children indented with `↳`, giving a clear visual hierarchy without a complex nested dropdown component.
- **No variant support in v1** — the `meta` JSON column on products is the extension point for future variant data (size, colour) without requiring a migration.
- **Repository injection via constructor** — both controllers use PHP 8 constructor property promotion: `public function __construct(private readonly ProductRepositoryInterface $products)`. The service container resolves the binding automatically.
