<?php

namespace Modules\POS\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Auth\Models\Lookup;
use Modules\Auth\Models\Setting;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Repositories\Contracts\ProductRepositoryInterface;
use Modules\POS\Events\SaleCompleted;
use Modules\POS\Http\Requests\CompleteSaleRequest;
use Modules\POS\Repositories\Contracts\SaleRepositoryInterface;

class PosController extends Controller
{
    public function __construct(
        private readonly SaleRepositoryInterface $sales,
        private readonly ProductRepositoryInterface $products
    ) {}

    // -------------------------------------------------------------------------
    // POS Checkout Screen
    // -------------------------------------------------------------------------

    public function index(): View
    {
        $paymentMethods = Lookup::ofType('payment_method');
        $currencySymbol = Setting::getValue('currency_symbol', '$');

        return view('pos::pos.checkout', compact('paymentMethods', 'currencySymbol'));
    }

    // -------------------------------------------------------------------------
    // Product Search (JSON — called by checkout JS)
    // -------------------------------------------------------------------------

    public function productSearch(Request $request): JsonResponse
    {
        $term = trim($request->query('q', ''));

        if (strlen($term) < 1) {
            return response()->json([]);
        }

        // Try exact SKU match first (barcode scan)
        $exact = $this->products->findBySku($term);

        if ($exact && $exact->is_active) {
            $results = collect([$exact]);
        } else {
            $results = Product::active()
                ->search($term)
                ->withSum('stockMovements as current_stock', 'quantity_change')
                ->orderBy('name')
                ->limit(10)
                ->get();
        }

        return response()->json(
            $results->map(fn (Product $p) => [
                'id' => $p->id,
                'name' => $p->name,
                'sku' => $p->sku,
                'price' => (float) $p->price,
                'current_stock' => (int) ($p->current_stock ?? $p->getCurrentStockAttribute()),
                'image_url' => $p->image_url,
            ])
        );
    }

    // -------------------------------------------------------------------------
    // Complete Sale
    // -------------------------------------------------------------------------

    public function store(CompleteSaleRequest $request): RedirectResponse
    {
        $items = collect($request->items);

        // Calculate total from submitted prices (validated, but recalculate server-side)
        $total = $items->sum(fn ($item) => $item['unit_price'] * $item['quantity']);

        $sale = $this->sales->createWithItems(
            saleData: [
                'sale_number' => $this->sales->nextSaleNumber(),
                'channel' => 'pos',
                'total' => round($total, 2),
                'payment_method' => $request->payment_method,
                'status' => 'completed',
                'created_by' => auth()->id(),
            ],
            items: $request->items
        );

        // Fire SaleCompleted:
        //   → DeductStock (sync) — writes stock movements per item
        //   → RecordSalesLedgerEntry (sync) — writes activity log
        //   → GenerateReceiptPdf (queued) — background side effect
        SaleCompleted::dispatch($sale);

        return redirect()->route('admin.pos.sales.receipt', $sale)
            ->with('success', "Sale {$sale->sale_number} completed successfully.");
    }

    // -------------------------------------------------------------------------
    // Receipt
    // -------------------------------------------------------------------------

    public function receipt(int $sale): View
    {
        $sale = $this->sales->findById($sale);

        abort_if(! $sale, 404);

        $receiptFooter = Setting::getValue('receipt_footer', 'Thank you for your purchase!');
        $currencySymbol = Setting::getValue('currency_symbol', '$');
        $appName = Setting::getValue('app_name', config('app.name'));

        return view('pos::pos.receipt', compact('sale', 'receiptFooter', 'currencySymbol', 'appName'));
    }

    // -------------------------------------------------------------------------
    // Daily Summary
    // -------------------------------------------------------------------------

    public function dailySummary(Request $request): View
    {
        $date = $request->query('date', today()->toDateString());
        $summary = $this->sales->dailySummary($date);

        // Recent sales for the selected date
        $recentSales = $this->sales->paginate(['date' => $date], 20);

        $currencySymbol = Setting::getValue('currency_symbol', '$');

        return view('pos::pos.daily-summary', compact('summary', 'recentSales', 'date', 'currencySymbol'));
    }
}
