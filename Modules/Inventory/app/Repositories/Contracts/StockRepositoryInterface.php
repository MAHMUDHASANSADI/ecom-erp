<?php

namespace Modules\Inventory\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Catalog\Models\Product;
use Modules\Inventory\Models\StockMovement;

interface StockRepositoryInterface
{
    /**
     * Current stock quantity for a single product (SUM of quantity_change).
     */
    public function currentStock(int $productId): int;

    /**
     * Map of product_id → current stock for all products.
     *
     * @return array<int, int>
     */
    public function allCurrentStock(): array;

    /**
     * Paginated list of all products with their current stock and low-stock flag.
     *
     * @param  array{search?: string, category_id?: int, low_stock?: bool}  $filters
     */
    public function stockLevelsPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    /**
     * Products whose current stock is at or below the given threshold.
     *
     * @return Collection<int, Product>
     */
    public function lowStockProducts(int $threshold): Collection;

    /**
     * Write a single stock movement entry and return it.
     *
     * @param  array{product_id: int, quantity_change: int, reason: string, reference_type?: string, reference_id?: int, notes?: string, created_by: int}  $data
     */
    public function recordMovement(array $data): StockMovement;

    /**
     * Paginated movement history, optionally scoped to one product.
     *
     * @param  array{product_id?: int, reason?: string, date_from?: string, date_to?: string}  $filters
     */
    public function historyPaginated(array $filters = [], int $perPage = 30): LengthAwarePaginator;
}
