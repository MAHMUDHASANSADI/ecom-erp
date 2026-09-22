# Phase 5 — Storefront (Public-Facing)

## Overview

Phase 5 delivers the public-facing online store. Visitors browse products by category or search, add items to a session-based cart, and check out as a guest — no account required. On order placement an email confirmation is queued. The admin side gives staff a full order management panel where order status can be updated through to completion.

---

## Module Structure

```
Modules/
└── Storefront/
    ├── app/
    │   ├── Emails/            OrderConfirmationMail (Mailable + ShouldQueue)
    │   ├── Events/            OrderPlaced
    │   ├── Http/
    │   │   ├── Controllers/   StorefrontController, CartController,
    │   │   │                  CheckoutController, OrderController (admin)
    │   │   └── Requests/      GuestCheckoutRequest
    │   ├── Listeners/         SendOrderConfirmationEmail (queued)
    │   ├── Models/            Order, OrderItem
    │   ├── Providers/         StorefrontServiceProvider, EventServiceProvider, RouteServiceProvider
    │   └── Repositories/
    │       ├── Contracts/     OrderRepositoryInterface
    │       └── Eloquent/      OrderRepository
    ├── database/
    │   ├── migrations/        create_orders_table, create_order_items_table
    │   └── seeders/           StorefrontDatabaseSeeder
    ├── resources/views/
    │   ├── layouts/           shop.blade.php  ← public storefront shell
    │   ├── shop/              index.blade.php, show.blade.php
    │   ├── cart/              index.blade.php
    │   ├── checkout/          index.blade.php, confirmation.blade.php
    │   ├── orders/            index.blade.php, show.blade.php (admin)
    │   └── emails/            order-confirmation.blade.php
    └── routes/
        └── web.php
```

---

## Database Tables

### `orders`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `sale_id` | bigint FK nullable | → `sales.id`, `nullOnDelete` — set once admin processes the order |
| `customer_name` | varchar(150) | |
| `email` | varchar(150) | |
| `address` | text | |
| `status` | varchar(20) | `lookups.code` where `type = order_status` |
| `meta` | json nullable | extension point |
| `created_at / updated_at` | timestamps | |

**Indexes:** `status`, `email`.

### `order_items`

| Column | Type | Notes |
|---|---|---|
| `id` | bigint PK | |
| `order_id` | bigint FK | → `orders.id`, cascade delete |
| `product_id` | bigint FK | → `products.id`, restrict delete |
| `quantity` | integer | |
| `unit_price` | decimal(10,2) | Price snapshot at time of order |

**Index:** composite `(order_id, product_id)`.

> `unit_price` is a snapshot — if a product's price changes later, historical orders still show the correct price.

> `sale_id` is `null` until an admin processes the order into a sale. When they do, a `Sale` record is created with `channel = 'online'`, linking the two records. In v1 this is a manual admin action; the system design anticipates it but the automated flow is v2.

---

## Repository Pattern

### `OrderRepositoryInterface`

```php
createWithItems(array $orderData, array $items): Order    // DB transaction
findById(int $id): ?Order
paginate(array $filters, int $perPage): LengthAwarePaginator
updateStatus(Order $order, string $status, ?int $saleId): Order
countByStatus(string $status): int
```

Bound in `StorefrontServiceProvider::register()`:
```php
$this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
```

---

## Event / Listener: OrderPlaced

Per the system design, `OrderPlaced` fires after a successful checkout. Its only listener is queued — email delivery must not block the redirect to the confirmation page.

```
OrderPlaced
    → SendOrderConfirmationEmail (queued, ShouldQueue)
          Mail::to($order->email)->send(new OrderConfirmationMail($order))
          MAIL_MAILER=log in dev — writes to storage/logs/laravel.log
```

`OrderConfirmationMail` is itself a `ShouldQueue` Mailable. The email view (`emails/order-confirmation.blade.php`) is a plain HTML layout with the order item table and delivery details.

---

## Session Cart

The cart is stored entirely in the PHP session under key `storefront_cart`. No database writes happen until checkout. Structure:

```php
session('storefront_cart') = [
    '{product_id}' => [
        'product_id' => int,
        'name'       => string,
        'price'      => float,
        'image_url'  => string|null,
        'quantity'   => int,
        'stock'      => int,   // snapshot at add time — enforces max qty
    ],
    ...
]
```

`CartController` provides static helpers `getCart()` and `clearCart()` so `CheckoutController` can read and wipe the cart without importing session mechanics directly.

---

## Checkout Flow

```
1. Guest visits /shop — browses products, adds to cart
2. /cart — reviews items, updates quantities, removes items
3. /checkout — fills customer_name, email, address
4.   → POST /checkout
5.      OrderRepository::createWithItems() [DB transaction]
6.      CartController::clearCart()
7.      OrderPlaced::dispatch($order)
8.   → redirect to /checkout/confirmation/{order}
9. (background) SendOrderConfirmationEmail queued job fires
```

No stock is deducted at this point — v1 storefront is cash-on-delivery. Stock deduction happens when the admin processes the order into a `Sale` (manual action, linking `order.sale_id`).

---

## Permissions

| Permission | Grants |
|---|---|
| `view_orders` | Admin orders index and detail pages |
| `manage_orders` | Update order status |

Both public storefront routes (shop, cart, checkout) require **no authentication** — they use only `web` middleware.

---

## Routes

### Public (no auth)

```
GET  /shop                              storefront.products.index    — listing
GET  /shop/category/{slug}              storefront.products.category — filtered by category
GET  /shop/products/{product}           storefront.products.show     — detail
GET  /cart                              storefront.cart.index
POST /cart/add                          storefront.cart.add
PATCH /cart/{productId}                 storefront.cart.update
DELETE /cart/{productId}                storefront.cart.remove
POST /cart/clear                        storefront.cart.clear
GET  /checkout                          storefront.checkout.index
POST /checkout                          storefront.checkout.store
GET  /checkout/confirmation/{order}     storefront.checkout.confirmation
```

### Admin (authenticated)

```
GET  /admin/orders                      admin.orders.index    [view_orders]
GET  /admin/orders/{order}              admin.orders.show     [view_orders]
PUT  /admin/orders/{order}              admin.orders.update   [manage_orders]
```

`admin.orders.index` matches the nav item seeded in Phase 1 — the sidebar link becomes active automatically.

---

## Pages

### Shop Index (`/shop`)
- Two-column layout: category sidebar left, product grid right
- Category sidebar: top-level categories with children, active highlighted
- Product grid: 3-column, 12 per page, Bootstrap cards
- Each card: image, category badge, name, price, Add to Cart button
- Out-of-stock products show a badge instead of the add button
- Search form in navbar: `GET /shop?search=term`

### Product Detail (`/shop/products/{id}`)
- Full image, category breadcrumb, stock badge (In Stock / Low Stock / Out of Stock)
- Quantity selector (1 to stock limit), Add to Cart form
- Related products from same category (max 4)

### Cart (`/cart`)
- Table with image, name, qty spinner (auto-submits on change), unit price, line total
- Remove button per row, Clear cart button
- Running total in footer row
- Proceed to Checkout button

### Checkout (`/checkout`)
- Left column: customer_name, email, address form
- Right column: sticky order summary with item list and total
- Cash on Delivery notice (no payment gateway in v1)
- Submit places order and redirects to confirmation

### Order Confirmation (`/checkout/confirmation/{order}`)
- Success icon, thank-you message with customer name and email
- Full order table with quantities, unit prices, line totals, grand total
- Delivery address and payment method
- Continue Shopping link

### Admin Orders Index (`/admin/orders`)
- Pending order count banner at top
- Filter by status and search by name/email
- Table with ID, customer, email, item count, total, status badge, placed date, view button

### Admin Order Detail (`/admin/orders/{order}`)
- Items table with SKU, qty, unit price, line total
- Customer info panel
- Status update form (dropdown + save button, guarded by `@can('manage_orders')`)
- Link to related Sale receipt if `sale_id` is set
- Email Customer quick action button

---

## Order Statuses (from `lookups` table)

| Code | Label | Meaning |
|---|---|---|
| `pending` | Pending | Placed, awaiting processing |
| `processing` | Processing | Being prepared |
| `completed` | Completed | Fulfilled and delivered |
| `cancelled` | Cancelled | Cancelled by staff or customer |
| `refunded` | Refunded | Refund issued |

---

## Root URL Behaviour

`routes/web.php` updated: authenticated users → `/admin`, guests → `/shop`.

```php
Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'admin.dashboard' : 'storefront.products.index');
});
```

---

## Key Decisions

- **Session cart, not DB cart** — guest checkout means there is no user to attach a cart to. Session storage is simpler, zero-migration, and naturally garbage-collected when the session expires.
- **No stock deduction at order placement** — v1 is cash-on-delivery. Stock is only deducted when the admin confirms and processes the order into a `Sale` (which fires `SaleCompleted → DeductStock`). This avoids reserving stock for orders that may never be fulfilled.
- **`unit_price` snapshot** — copied into `order_items.unit_price` at placement time. Price changes never affect historical orders.
- **`sale_id` nullable** — the link from `orders` to `sales` is set manually when an admin processes the order. The design anticipates an automated "process order" flow in v2 that creates the `Sale` and updates `sale_id` in one action.
- **`OrderConfirmationMail` is `ShouldQueue`** — even though the Mailable itself doesn't implement it, the listener does, so the job is dispatched to the `database` queue driver and does not block the HTTP response.
