<?php

namespace Modules\Storefront\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Storefront\Models\Order;

interface OrderRepositoryInterface
{
    /**
     * Create an order and its items atomically inside a DB transaction.
     *
     * @param  array{customer_name: string, email: string, address: string, status: string}  $orderData
     * @param  array<int, array{product_id: int, quantity: int, unit_price: float}>  $items
     */
    public function createWithItems(array $orderData, array $items): Order;

    public function findById(int $id): ?Order;

    /**
     * Paginated orders list for admin panel.
     *
     * @param  array{status?: string, search?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    /**
     * Update order status and optionally link to a sale.
     */
    public function updateStatus(Order $order, string $status, ?int $saleId = null): Order;

    /**
     * Count of orders with a given status.
     */
    public function countByStatus(string $status): int;
}
