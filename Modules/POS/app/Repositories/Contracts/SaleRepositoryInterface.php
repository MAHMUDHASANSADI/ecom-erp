<?php

namespace Modules\POS\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\POS\Models\Sale;

interface SaleRepositoryInterface
{
    /**
     * Create a sale and its items atomically inside a DB transaction.
     *
     * @param  array{channel: string, payment_method: string, status: string, created_by: int, total: float}  $saleData
     * @param  array<int, array{product_id: int, quantity: int, unit_price: float}>  $items
     */
    public function createWithItems(array $saleData, array $items): Sale;

    public function findById(int $id): ?Sale;

    public function findBySaleNumber(string $saleNumber): ?Sale;

    /**
     * Paginated sales history.
     *
     * @param  array{date?: string, channel?: string, cashier_id?: int}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    /**
     * Daily summary: total revenue, total transactions, breakdown by payment method.
     *
     * @return array{date: string, total_revenue: float, total_transactions: int, by_payment_method: array<string, float>}
     */
    public function dailySummary(string $date): array;

    /**
     * Generate next unique sale number using the invoice_prefix setting.
     */
    public function nextSaleNumber(): string;
}
