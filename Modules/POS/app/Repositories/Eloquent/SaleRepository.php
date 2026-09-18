<?php

namespace Modules\POS\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Models\Setting;
use Modules\POS\Models\Sale;
use Modules\POS\Models\SaleItem;
use Modules\POS\Repositories\Contracts\SaleRepositoryInterface;

class SaleRepository implements SaleRepositoryInterface
{
    public function createWithItems(array $saleData, array $items): Sale
    {
        return DB::transaction(function () use ($saleData, $items): Sale {
            $sale = Sale::create($saleData);

            foreach ($items as $item) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                ]);
            }

            return $sale->load('items.product');
        });
    }

    public function findById(int $id): ?Sale
    {
        return Sale::with('items.product', 'cashier')->find($id);
    }

    public function findBySaleNumber(string $saleNumber): ?Sale
    {
        return Sale::with('items.product', 'cashier')
            ->where('sale_number', $saleNumber)
            ->first();
    }

    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Sale::with('cashier')->orderByDesc('created_at');

        if (! empty($filters['date'])) {
            $query->whereDate('created_at', $filters['date']);
        }

        if (! empty($filters['channel'])) {
            $query->where('channel', $filters['channel']);
        }

        if (! empty($filters['cashier_id'])) {
            $query->where('created_by', (int) $filters['cashier_id']);
        }

        return $query->paginate($perPage);
    }

    public function dailySummary(string $date): array
    {
        $sales = Sale::completed()
            ->whereDate('created_at', $date)
            ->get();

        $byPaymentMethod = $sales->groupBy('payment_method')
            ->map(fn ($group) => round((float) $group->sum('total'), 2))
            ->toArray();

        return [
            'date' => $date,
            'total_revenue' => round((float) $sales->sum('total'), 2),
            'total_transactions' => $sales->count(),
            'by_payment_method' => $byPaymentMethod,
        ];
    }

    public function nextSaleNumber(): string
    {
        $prefix = Setting::getValue('invoice_prefix', 'INV-');
        $last = Sale::where('sale_number', 'like', $prefix.'%')
            ->orderByDesc('id')
            ->value('sale_number');

        if ($last) {
            $lastNum = (int) substr($last, strlen($prefix));
            $next = $lastNum + 1;
        } else {
            $next = 1;
        }

        return $prefix.str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
