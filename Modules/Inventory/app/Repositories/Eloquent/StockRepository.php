<?php

namespace Modules\Inventory\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\Setting;
use Modules\Catalog\Models\Product;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Repositories\Contracts\StockRepositoryInterface;

class StockRepository implements StockRepositoryInterface
{
    public function currentStock(int $productId): int
    {
        return (int) StockMovement::where('product_id', $productId)
            ->sum('quantity_change');
    }

    public function allCurrentStock(): array
    {
        return StockMovement::select('product_id', DB::raw('SUM(quantity_change) as stock'))
            ->groupBy('product_id')
            ->pluck('stock', 'product_id')
            ->map(fn ($v) => (int) $v)
            ->toArray();
    }

    public function stockLevelsPaginated(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Product::with('category')
            ->withSum('stockMovements as current_stock', 'quantity_change')
            ->orderBy('name');

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('sku', 'like', "%{$term}%");
            });
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        $threshold = (int) Setting::getValue('low_stock_threshold', 5);

        if (! empty($filters['low_stock'])) {
            // HAVING current_stock <= threshold (including products with no movements = 0)
            $query->having('current_stock', '<=', $threshold)
                ->orHavingNull('current_stock');
        }

        $paginator = $query->paginate($perPage);

        // Attach threshold to each item for view use
        $paginator->getCollection()->each(function (Product $product) use ($threshold): void {
            $product->low_stock_threshold = $threshold;
            $product->current_stock = (int) ($product->current_stock ?? 0);
        });

        return $paginator;
    }

    public function lowStockProducts(int $threshold): Collection
    {
        return Product::withSum('stockMovements as current_stock', 'quantity_change')
            ->having('current_stock', '<=', $threshold)
            ->orHavingNull('current_stock')
            ->orderBy('name')
            ->get()
            ->each(function (Product $product): void {
                $product->current_stock = (int) ($product->current_stock ?? 0);
            });
    }

    public function recordMovement(array $data): StockMovement
    {
        return StockMovement::create($data);
    }

    public function historyPaginated(array $filters = [], int $perPage = 30): LengthAwarePaginator
    {
        $query = StockMovement::with(['product', 'creator'])
            ->orderByDesc('created_at');

        if (! empty($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }

        if (! empty($filters['reason'])) {
            $query->where('reason', $filters['reason']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to'].' 23:59:59');
        }

        return $query->paginate($perPage);
    }
}
