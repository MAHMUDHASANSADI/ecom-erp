System Design Document
E-Commerce Management Platform — v1
Architecture, Module Structure, Repository Pattern, Event/Queue Design & Database Schema
Companion document to the v1 SRS. Version 1.0 — Draft for Development.
Prepared by: Sadi

1. Architecture Overview
1.1 Approach
The system is built as a Laravel monolith using Module-based architecture (nwidart/laravel-modules) rather than a single flat app/ directory. Each business capability is a self-contained module with its own routes, controllers, models, migrations, views, and repositories. This keeps the codebase organized as it grows, and makes it possible to disable or customize a module per client without touching unrelated code — useful given this product will be resold to multiple clients with potentially different module needs.
1.2 Module Map
Modules/
├── Auth/            → login, roles, permissions
├── Catalog/         → products, categories
├── Inventory/       → stock, stock ledger, adjustments
├── POS/             → checkout, receipts, daily summary
├── Storefront/      → public product pages, cart, guest checkout
└── Finance/         → sales ledger, expenses, reports
Each module may depend on another's public interfaces (e.g. POS depends on Catalog and Inventory repositories), but never reaches directly into another module's internal classes.
1.3 Request Flow
Controller → Service (business logic / orchestration) → Repository (data access) → Eloquent Model → MySQL. Controllers stay thin; validation happens via Form Requests; orchestration that spans modules (e.g. completing a sale) lives in a Service class, not the controller.
1.4 Permission-Based Access Control
Access is controlled by discrete permissions using the spatie/laravel-permission package, rather than hand-built role/permission tables or hardcoded role-name checks. A role is a named bundle of permissions; a permission can also be granted directly to an individual user when a role-level grant is too coarse. This means adding a permission, or changing what a role can do, is a data change — not a code change.
●	Route protection: Spatie's built-in permission:manage_products middleware checks whether the authenticated user's role (or direct grant) has the required permission before the request reaches the controller.
●	Blade views: Spatie registers @can / @hasrole / @hasanypermission directives automatically, so the admin panel menu is naturally permission-gated at render time.
●	Permissions are seeded per module during migration (e.g. Catalog module seeds manage_products, view_products) and assigned to the default Owner / Manager / Cashier roles via a seeder — but role-to-permission assignment stays editable data through the package's API.
●	Owner role is seeded with all permissions by default and is the only role permitted to modify role/permission assignments.
2. Repository Pattern
2.1 Structure (per module)
Modules/Inventory/
├── Repositories/
│   ├── Contracts/StockRepositoryInterface.php
│   └── Eloquent/StockRepository.php
├── Providers/InventoryServiceProvider.php   (binds interface -> implementation)
Controllers and Services type-hint the interface, never the Eloquent implementation. This keeps business logic testable (mockable repositories in unit tests) and allows the storage layer to change later without touching callers.
2.2 Where to apply it
●	Apply to entities with real query complexity or that are written from multiple places: Product, StockMovement, Sale, Order.
●	Skip it for trivial, single-purpose lookups (e.g. a static Category::all() for a dropdown) — unnecessary abstraction there adds indirection without benefit.
2.3 Repositories planned for v1
Repository	Module	Key Responsibilities
ProductRepository	Catalog	CRUD, search/filter by category, active/inactive scoping
CategoryRepository	Catalog	Nested category retrieval
StockRepository	Inventory	Current stock lookup, ledger writes, low-stock queries
SaleRepository	POS / Finance	Create sale + items atomically, sales history queries
ExpenseRepository	Finance	CRUD, category-tagged totals
OrderRepository	Storefront	Guest order creation, status updates
3. Event Listeners & Job Queues
3.1 Principle
Events are fired synchronously for anything the current request depends on (e.g. stock must be confirmed decremented before a sale is considered complete). Anything that is a side effect and not required for the response — emails, PDF generation, notifications — is handled by a queued job listening to that event, so the user isn't kept waiting on it.
3.2 Event → Listener/Job Map
Event	Listener / Job	Sync or Queued
SaleCompleted	DeductStock (writes StockMovement)	Sync
SaleCompleted	RecordSalesLedgerEntry	Sync
SaleCompleted	GenerateReceiptPdf	Queued
StockLevelChanged	CheckLowStockThreshold	Sync
LowStockDetected	NotifyManager	Queued
OrderPlaced	SendOrderConfirmationEmail	Queued
ExpenseLogged	RecalculateMonthlyProfitSnapshot	Queued
3.3 Queue Driver
database queue driver for v1 — no Redis dependency required on client servers, keeping deployment simple. Can be upgraded to Redis per-client later if sale/order volume warrants it, without changing job code.
4. Database Schema (v1)
MySQL. All tables use an auto-incrementing bigint id primary key and standard created_at / updated_at timestamps unless noted otherwise. Money fields use decimal(10,2).
4.1 roles, permissions & pivots (package-managed via spatie/laravel-permission)
These tables are created by the package's own migrations, not hand-written. Documented here for reference only — do not hand-edit their structure.
Table	Purpose
roles	Named role, e.g. Owner, Manager, Cashier
permissions	Discrete capability, e.g. manage_products, view_finance_reports
role_has_permissions	Pivot — which permissions a role grants
model_has_roles	Polymorphic pivot — which role(s) a user has
model_has_permissions	Polymorphic pivot — permission granted directly to a specific user, bypassing role
4.2 settings
Key-value system configuration, editable at runtime through the admin panel — no deployment needed to change behavior. Supports the dynamic/extensible design goal: currency, invoice prefix, tax rules, low-stock threshold default, etc. all live here instead of being hardcoded.
Column	Type	Notes
id	bigint, PK	Auto-increment
key	varchar(100)	Unique — e.g. default_currency, low_stock_threshold
value	text	Stored as JSON or plain string depending on setting type
group	varchar(50)	Groups related settings — general, finance, pos, storefront
4.3 lookups
Generic reference-data table replacing hardcoded status/reason strings scattered across modules. New values (a new expense category, a new order status, a new payment method) are added as rows — no migration or code deploy required. This is the main mechanism behind 'fully dynamic' schema behavior.
Column	Type	Notes
id	bigint, PK	Auto-increment
type	varchar(50)	e.g. order_status, payment_method, expense_category, stock_reason
code	varchar(50)	Machine-readable value used in code, e.g. pending
label	varchar(100)	Human-readable, shown in UI, e.g. Pending
sort_order	integer	Controls display order
is_active	boolean	Default true — deactivate instead of deleting to preserve history
●	Fields that previously stored a fixed varchar (sales.status, sales.payment_method, orders.status, stock_movements.reason, expenses.category) now store a lookups.code value for that type — enforced in application validation rather than a hard DB foreign key, so the set of valid values stays editable without a migration.
4.4 users
Role assignment is handled by Spatie's model_has_roles pivot (see 4.1), not a role_id column here.
Column	Type	Notes
id	bigint, PK	Auto-increment
name	varchar(150)	
email	varchar(150)	Unique
password	varchar(255)	Hashed (bcrypt)
is_active	boolean	Default true
meta	json, nullable	Schema-less extension point for future per-user fields without a migration
4.5 categories
Self-referencing for nested categories.
Column	Type	Notes
id	bigint, PK	Auto-increment
parent_category_id	bigint, FK, nullable	References categories.id
name	varchar(100)	
slug	varchar(120)	Unique, used in storefront URLs
●	FK: categories.parent_category_id → categories.id (self-referencing)
4.6 products
Column	Type	Notes
id	bigint, PK	Auto-increment
category_id	bigint, FK	References categories.id
name	varchar(150)	
sku	varchar(50)	Unique
price	decimal(10,2)	Selling price
cost_price	decimal(10,2)	Used for profit calculation
tax_class	varchar(50)	Nullable
image_path	varchar(255)	Nullable
is_active	boolean	Default true
meta	json, nullable	Schema-less extension point — future attributes (variants, custom fields) without a migration
●	FK: products.category_id → categories.id
4.7 stock_movements
Append-only ledger — rows are never updated or deleted, only inserted. Current stock is derived by summing quantity_change per product, not stored as a separate mutable field.
Column	Type	Notes
id	bigint, PK	Auto-increment
product_id	bigint, FK	References products.id
quantity_change	integer	Positive = stock in, negative = stock out
reason	varchar(50)	Value from lookups where type = stock_reason (e.g. sale, manual_adjustment, restock)
reference_type	varchar(100)	Nullable — e.g. Sale, ManualAdjustment
reference_id	bigint	Nullable — id of the referenced record
created_by	bigint, FK	References users.id
created_at	timestamp	No updated_at — immutable row
●	FK: stock_movements.product_id → products.id
●	FK: stock_movements.created_by → users.id
4.8 sales
Represents a completed POS or storefront-originated transaction.
Column	Type	Notes
id	bigint, PK	Auto-increment
sale_number	varchar(30)	Unique, human-readable
channel	varchar(20)	Value from lookups where type = sale_channel (pos / online)
total	decimal(10,2)	
payment_method	varchar(20)	Value from lookups where type = payment_method
status	varchar(20)	Value from lookups where type = sale_status
created_by	bigint, FK	References users.id (cashier)
●	FK: sales.created_by → users.id
4.9 sale_items
Column	Type	Notes
id	bigint, PK	Auto-increment
sale_id	bigint, FK	References sales.id
product_id	bigint, FK	References products.id
quantity	integer	
unit_price	decimal(10,2)	Price at time of sale
●	FK: sale_items.sale_id → sales.id
●	FK: sale_items.product_id → products.id
4.10 orders
Storefront guest checkout — no customer account table in v1.
Column	Type	Notes
id	bigint, PK	Auto-increment
sale_id	bigint, FK, nullable	References sales.id once processed
customer_name	varchar(150)	
email	varchar(150)	
address	text	
status	varchar(20)	Value from lookups where type = order_status
meta	json, nullable	Schema-less extension point for future storefront fields without a migration
●	FK: orders.sale_id → sales.id
4.11 order_items
Column	Type	Notes
id	bigint, PK	Auto-increment
order_id	bigint, FK	References orders.id
product_id	bigint, FK	References products.id
quantity	integer	
unit_price	decimal(10,2)	Price at time of order
●	FK: order_items.order_id → orders.id
●	FK: order_items.product_id → products.id
4.12 expenses
Column	Type	Notes
id	bigint, PK	Auto-increment
category	varchar(100)	Value from lookups where type = expense_category
amount	decimal(10,2)	
note	text	Nullable
expense_date	date	
created_by	bigint, FK	References users.id
●	FK: expenses.created_by → users.id
4.13 activity_log
Generic audit trail across all entities — who changed what, when. Populated automatically via model observers/traits rather than per-module custom logging code, so new modules get auditing for free.
Column	Type	Notes
id	bigint, PK	Auto-increment
subject_type	varchar(150)	Model class name, e.g. Product, Sale
subject_id	bigint	Id of the affected record
causer_id	bigint, FK, nullable	References users.id — who made the change
description	varchar(150)	e.g. created, updated, stock adjusted
properties	json, nullable	Before/after values or extra context
created_at	timestamp	No updated_at — immutable row
●	FK: activity_log.causer_id → users.id
●	Composite index on (subject_type, subject_id) for fast per-record history lookups
4.14 Entity Relationship Summary
●	users *—* roles, roles *—* permissions (via spatie/laravel-permission pivots)
●	categories 1—* categories (self-referencing, nested)
●	categories 1—* products
●	products 1—* stock_movements
●	users 1—* stock_movements (created_by)
●	sales 1—* sale_items
●	products 1—* sale_items
●	users 1—* sales (created_by / cashier)
●	orders 1—* order_items
●	products 1—* order_items
●	orders 1—1 sales (nullable, set once an order is processed into a sale)
●	users 1—* expenses (created_by)
●	users 1—* activity_log (causer)
●	lookups is referenced conceptually (by type + code) from sales, orders, stock_movements, expenses — not a hard FK, by design
4.15 Indexing Notes
●	products: index on category_id, unique index on sku
●	stock_movements: composite index on (product_id, created_at) for fast current-stock and history queries
●	sales: index on created_by, index on channel + created_at for daily summary queries
●	categories: index on parent_category_id
●	lookups: composite unique index on (type, code)
●	activity_log: composite index on (subject_type, subject_id)
5. Design for Extensibility (ERP-Oriented Principles)
Treating this as ERP-adjacent software changes the bar: clients will ask for new statuses, new fields, new small modules over time, and the goal is for most of that to be configuration rather than a code release. The patterns below are what make the schema 'fully dynamic' rather than a fixed shape.
5.1 Principles applied in this schema
●	settings table — runtime-configurable system behavior (currency, thresholds, tax rules) without a deploy.
●	lookups table — new statuses, categories, reasons, and payment methods are rows, not enum values baked into migrations or code.
●	meta JSON columns on core entities (users, products, orders) — a place for a client-specific field to live without a schema migration, for cases too small or too client-specific to justify a real column.
●	activity_log — generic audit trail so every module gets change history without writing per-module logging.
●	Module-based architecture — a genuinely new capability (e.g. a Suppliers module, a Loyalty module) is added as a new module rather than bolted onto an existing one, keeping the core schema stable.
●	Repository pattern — storage details stay behind interfaces, so a table's internal structure can evolve without changing every caller.
5.2 Deliberate limits
Full dynamism has a cost: too much JSON/EAV-style storage makes reporting and querying harder, and too many soft-referenced lookups make data integrity easier to violate. This design keeps hard foreign keys and real columns for anything core to the domain (products, sales, stock) and reserves the dynamic mechanisms — settings, lookups, meta — for the parts that genuinely vary by client or change over time. That balance is what keeps v1 shippable in three months while leaving real room to grow.


