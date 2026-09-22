<?php

namespace Modules\Storefront\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Storefront\Models\Order;
use Modules\Storefront\Models\OrderItem;
use Modules\Storefront\Repositories\Contracts\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function createWithItems(array $orderData, array $items): Order
    {
        return DB::transaction(function () use ($orderData, $items): Order {
            $order = Order::create($orderData);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return $order->load('items.product');
        });
    }

    public function findById(int $id): ?Order
    {
        return Order::with('items.product', 'sale')->find($id);
    }

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Order::with('items')->orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('customer_name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage);
    }

    public function updateStatus(Order $order, string $status, ?int $saleId = null): Order
    {
        $data = ['status' => $status];

        if ($saleId !== null) {
            $data['sale_id'] = $saleId;
        }

        $order->update($data);

        return $order->fresh('items.product', 'sale');
    }

    public function countByStatus(string $status): int
    {
        return Order::where('status', $status)->count();
    }
}
