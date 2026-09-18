Software Requirements Specification
E-Commerce Management Platform (Store, Inventory, POS & Finance)
Version 1.0 — Initial Release Scope
Document Status: Draft for Development
Prepared by: Sadi

1. Introduction
1.1 Purpose
This document defines the functional and non-functional requirements for Version 1 (v1) of a Laravel-based e-commerce management platform. The platform is being built as a commercial product intended for resale to multiple retail business clients. It provides a single system for managing a store's catalog, inventory, point-of-sale (POS) operations, finances, and an online storefront through one integrated admin panel.
1.2 Intended Audience
●	Development team — as the primary build reference and scope boundary.
●	Prospective clients — to understand what v1 delivers before purchase or deployment.
●	QA — as the basis for test case design.
1.3 Product Scope
The product is a single-branch, single-tenant e-commerce and store management system. Each client receives an independent deployment (own server and database). The system is not a multi-tenant SaaS in v1 — that is identified as a future direction, not a v1 requirement.
1.4 Definitions & Abbreviations
Term	Definition
SRS	Software Requirements Specification
POS	Point of Sale — in-store checkout module
RBAC	Role-Based Access Control
SKU	Stock Keeping Unit — unique product identifier
Ledger	An append-only record of stock or financial movements
v1 / v2	Version 1 (this document's scope) / Version 2 (future scope)
2. System Overview
2.1 Technology Stack
Layer	Technology
Backend framework	Laravel (PHP)
Frontend / views	Laravel Blade (server-rendered)
Database	MySQL
Authentication	Laravel's built-in session auth with role middleware
Reporting/exports	PDF export via dompdf; CSV export for reports
2.2 High-Level Architecture
The system is a single Laravel monolith. Blade views render both the admin panel and the public storefront from the same codebase, sharing models, business logic, and the database. This avoids the overhead of a separate API/frontend layer and is well suited to a 3-month v1 timeline.
2.3 Module List (v1)
●	Authentication & Role Management
●	Catalog & Category Management
●	Inventory Management
●	Point of Sale (POS)
●	Storefront (public-facing)
●	Finance & Sales Reporting
3. Functional Requirements
3.1 Authentication & Role Management
In scope for v1:
●	Staff login with email/username and password.
●	Three roles: Owner, Manager, Cashier — each with a distinct permission set.
●	Owner: full access to all modules including finance and settings.
●	Manager: catalog, inventory, POS, and reporting access; no user management.
●	Cashier: POS access only.
●	Password reset via email.
Out of scope for v1 (backlog for v2):
●	Multi-branch role scoping (role tied to a specific branch).
●	Two-factor authentication.
●	Third-party SSO login.
3.2 Catalog & Category Management
In scope for v1:
●	Create, edit, deactivate products with name, SKU, price, cost price, tax class, and description.
●	Nested product categories (category and sub-category).
●	Single product image upload.
●	Basic product search and filter by category in the admin panel.
Out of scope for v1 (backlog for v2):
●	Product variants (size/color/attribute combinations).
●	Multiple images per product / image gallery.
●	Bulk import/export of products via CSV.
3.3 Inventory Management
In scope for v1:
●	Single-location stock tracking per product.
●	Manual stock adjustment (add/remove stock with a reason code).
●	Stock movement history recorded as an append-only ledger (not just a mutable quantity field), so every increase or decrease is traceable.
●	Low-stock threshold and alert on the dashboard.
Out of scope for v1 (backlog for v2):
●	Multi-warehouse / multi-branch stock and inter-branch transfer.
●	Supplier and purchase-order management.
●	Automatic reordering.
3.4 Point of Sale (POS)
In scope for v1:
●	Single-screen checkout: search/scan product, add to cart, apply quantity.
●	Payment methods: cash and card (recorded manually, no live card gateway integration in v1).
●	Automatic stock deduction on sale completion, written to the stock ledger.
●	Printable/PDF receipt per transaction.
●	Daily sales summary view for cashier handover.
Out of scope for v1 (backlog for v2):
●	Offline mode with local queueing and background sync.
●	Split payments across multiple methods in a single transaction.
●	Integrated card payment gateway.
●	Barcode hardware scanner integration testing (software supports scan input, but hardware certification is out of scope).
3.5 Storefront (Public-Facing)
In scope for v1:
●	Public product listing page with category filter.
●	Product detail page.
●	Shopping cart and guest checkout (no customer account required).
●	Order confirmation page and email notification.
Out of scope for v1 (backlog for v2):
●	Customer account registration, login, and order history.
●	Online payment gateway integration (v1 supports cash-on-delivery / manual payment confirmation only).
●	Product reviews and ratings.
●	Wishlist.
3.6 Finance & Reporting
In scope for v1:
●	Sales ledger — every POS and online sale recorded as a financial entry.
●	Basic expense entry (manual, category-tagged).
●	Daily and monthly sales summary report.
●	Simple profit report: revenue minus cost price minus logged expenses.
●	Export reports to PDF and CSV.
Out of scope for v1 (backlog for v2):
●	Full double-entry accounting.
●	Bank reconciliation.
●	Multi-currency support.
●	Tax filing / jurisdiction-specific tax reports.
4. Non-Functional Requirements
Category	Requirement
Performance	Admin panel pages should load within 2 seconds under normal load for a single-branch dataset.
Browser support	Latest two versions of Chrome, Firefox, and Edge.
Responsiveness	Admin panel and storefront usable on tablet screen sizes; POS screen optimized for desktop/tablet.
Backup	Daily automated MySQL database backup.
Security	Passwords hashed (bcrypt); role-based route protection on every admin route; CSRF protection (Laravel default).
Availability	Single-server deployment; no high-availability requirement in v1.
Data integrity	Stock and financial changes are append-only ledger entries, not overwritten fields, to keep an auditable history.
5. Data Model Overview (Key Entities)
Full schema is produced in the System Design phase. The entities below define the v1 data boundary.
Entity	Key Fields / Notes
User	name, email, password, role_id
Role	name, permissions (Owner / Manager / Cashier)
Category	name, parent_category_id (self-referencing for nesting)
Product	name, sku, category_id, price, cost_price, tax_class, image, is_active
StockMovement	product_id, quantity_change, reason, reference_type, reference_id, created_at (append-only)
Sale	sale_number, channel (pos/online), total, payment_method, status, created_by
SaleItem	sale_id, product_id, quantity, unit_price
Expense	category, amount, note, date
Order	customer_name, email, address, status (storefront orders)
6. User Roles & Permission Matrix
Capability	Owner	Manager	Cashier
Manage products & categories	Yes	Yes	No
Adjust inventory	Yes	Yes	No
Operate POS	Yes	Yes	Yes
View finance reports	Yes	Yes	No
Manage users & roles	Yes	No	No
System settings	Yes	No	No
7. Development Timeline (3 Months)
Phase	Duration	Deliverable
Requirements & Planning	Weeks 1–2	This SRS, ER diagram, scope sign-off
System Design	Weeks 2–3	Database schema, permission map, Blade view wireframes
Development	Weeks 3–10	Auth → Catalog → Inventory → POS → Finance → Storefront
Testing	Week 11 (+ ongoing)	Unit tests, manual QA per module, UAT walkthrough
Deployment	Week 12	Server setup, migrations, seed/demo data, go-live
8. Out of Scope for v1 (Consolidated)
The following are explicitly deferred to v2 or later, and should be excluded from v1 estimation and client expectations:
●	Multi-branch / multi-warehouse operations and stock transfer.
●	Multi-tenant SaaS architecture (v1 is single-tenant, licensed per client).
●	Live payment gateway integration (online and POS).
●	Customer accounts and order history on the storefront.
●	Product variants and bulk CSV import/export.
●	Full double-entry accounting and bank reconciliation.
●	Offline-first POS with background sync.
