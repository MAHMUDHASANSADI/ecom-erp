<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Auth\Models\ActivityLog;
use Modules\Auth\Models\Lookup;
use Modules\Auth\Models\Setting;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Inventory\Events\StockLevelChanged;
use Modules\Inventory\Http\Requests\StockAdjustmentRequest;
use Modules\Inventory\Repositories\Contracts\StockRepositoryInterface;

class InventoryController extends Controller
{
    public function __construct(
        private readonly StockRepositoryInterface $stock
    ) {}

    // -------------------------------------------------------------------------
    // Stock Levels Index
    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $filters = array_filter(
            $request->only(['search', 'category_id', 'low_stock']),
            fn ($v) => $v !== '' && $v !== null
        );

        $products = $this->stock->stockLevelsPaginated($filters, 25);
        $categories = Category::orderBy('name')->pluck('name', 'id');
        $threshold = (int) Setting::getValue('low_stock_threshold', 5);
        $lowStockCount = $this->stock->lowStockProducts($threshold)->count();

        return view('inventory::stock.index', compact(
            'products', 'categories', 'filters', 'threshold', 'lowStockCount'
        ));
    }

    // -------------------------------------------------------------------------
    // Manual Adjustment
    // -------------------------------------------------------------------------

    public function adjust(Request $request): View
    {
        $products = Product::active()->orderBy('name')
            ->withSum('stockMovements as current_stock', 'quantity_change')
            ->get()
            ->each(fn ($p) => $p->current_stock = (int) ($p->current_stock ?? 0));

        $reasons = Lookup::ofType('stock_reason');

        // Pre-select product if passed via query string (from stock levels page)
        $selectedProductId = $request->query('product_id');

        return view('inventory::stock.adjust', compact('products', 'reasons', 'selectedProductId'));
    }

    public function storeAdjustment(StockAdjustmentRequest $request): RedirectResponse
    {
        $quantityChange = $request->type === 'add'
            ? (int) $request->quantity
            : -(int) $request->quantity;

        $movement = $this->stock->recordMovement([
            'product_id' => $request->product_id,
            'quantity_change' => $quantityChange,
            'reason' => $request->reason,
            'reference_type' => 'ManualAdjustment',
            'notes' => $request->notes,
            'created_by' => auth()->id(),
        ]);

        $newStock = $this->stock->currentStock($request->product_id);
        $product = Product::findOrFail($request->product_id);

        // Log to activity trail
        ActivityLog::record(
            subject: $movement,
            description: 'stock_adjusted',
            properties: [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'sku' => $product->sku,
                'quantity_change' => $quantityChange,
                'new_stock' => $newStock,
                'reason' => $request->reason,
            ]
        );

        // Fire sync event → CheckLowStockThreshold listener
        StockLevelChanged::dispatch($product, $movement, $newStock);

        $direction = $quantityChange > 0 ? 'added' : 'removed';
        $abs = abs($quantityChange);

        return redirect()->route('admin.inventory.index')
            ->with('success', "Stock adjusted — {$abs} unit(s) {$direction} for \"{$product->name}\". Current stock: {$newStock}.");
    }

    // -------------------------------------------------------------------------
    // Movement History
    // -------------------------------------------------------------------------

    public function history(Request $request): View
    {
        $filters = array_filter(
            $request->only(['product_id', 'reason', 'date_from', 'date_to']),
            fn ($v) => $v !== '' && $v !== null
        );

        $movements = $this->stock->historyPaginated($filters, 30);
        $products = Product::orderBy('name')->pluck('name', 'id');
        $reasons = Lookup::ofType('stock_reason');

        return view('inventory::stock.history', compact('movements', 'products', 'reasons', 'filters'));
    }

    // -------------------------------------------------------------------------
    // Per-product movement history (linked from product show page)
    // -------------------------------------------------------------------------

    public function productHistory(Product $product): View
    {
        $movements = $this->stock->historyPaginated(['product_id' => $product->id], 20);
        $currentStock = $this->stock->currentStock($product->id);

        return view('inventory::stock.product-history', compact('product', 'movements', 'currentStock'));
    }
}
