<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Auth\Models\Setting;
use Modules\Catalog\Models\Product;
use Modules\Finance\Models\Expense;
use Modules\Inventory\Repositories\Contracts\StockRepositoryInterface;
use Modules\POS\Models\Sale;
use Modules\Storefront\Models\Order;

class DashboardController extends Controller
{
    public function __construct(
        private readonly StockRepositoryInterface $stock
    ) {}

    public function index(): View
    {
        $user = auth()->user();
        $threshold = (int) Setting::getValue('low_stock_threshold', 5);
        $currencySymbol = Setting::getValue('currency_symbol', '$');

        // -----------------------------------------------------------------------
        // Stat cards — only query what the current user can see
        // -----------------------------------------------------------------------

        $todaySalesCount = null;
        $todayRevenue = null;
        $lowStockCount = null;
        $pendingOrdersCount = null;

        if ($user->can('operate_pos') || $user->can('view_finance_reports')) {
            $todaySalesCount = Sale::completed()->today()->count();
            $todayRevenue = (float) Sale::completed()->today()->sum('total');
        }

        if ($user->can('view_inventory')) {
            $lowStockCount = $this->stock->lowStockProducts($threshold)->count();
        }

        if ($user->can('view_orders')) {
            $pendingOrdersCount = Order::pending()->count();
        }

        // -----------------------------------------------------------------------
        // Recent sales (last 8) — POS + online
        // -----------------------------------------------------------------------

        $recentSales = null;
        if ($user->can('view_finance_reports') || $user->can('operate_pos')) {
            $recentSales = Sale::with('cashier')
                ->completed()
                ->orderByDesc('created_at')
                ->limit(8)
                ->get();
        }

        // -----------------------------------------------------------------------
        // Low stock products list (top 6 worst)
        // -----------------------------------------------------------------------

        $lowStockProducts = null;
        if ($user->can('view_inventory')) {
            $lowStockProducts = Product::withSum('stockMovements as current_stock', 'quantity_change')
                ->having('current_stock', '<=', $threshold)
                ->orHavingNull('current_stock')
                ->orderBy('current_stock')
                ->limit(6)
                ->get()
                ->each(fn ($p) => $p->current_stock = (int) ($p->current_stock ?? 0));
        }

        // -----------------------------------------------------------------------
        // Pending orders list (latest 5)
        // -----------------------------------------------------------------------

        $pendingOrders = null;
        if ($user->can('view_orders')) {
            $pendingOrders = Order::pending()
                ->orderByDesc('created_at')
                ->limit(5)
                ->get();
        }

        // -----------------------------------------------------------------------
        // Monthly revenue chart data (last 6 months)
        // -----------------------------------------------------------------------

        $monthlyChartData = null;
        if ($user->can('view_finance_reports')) {
            $months = collect(range(5, 0))->map(fn ($offset) => now()->subMonths($offset));

            $revenueByMonth = Sale::completed()
                ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
                ->select(
                    DB::raw('YEAR(created_at) as yr'),
                    DB::raw('MONTH(created_at) as mo'),
                    DB::raw('SUM(total) as revenue')
                )
                ->groupBy('yr', 'mo')
                ->get()
                ->keyBy(fn ($r) => "{$r->yr}-{$r->mo}");

            $monthlyChartData = $months->map(fn ($date) => [
                'label' => $date->format('M'),
                'revenue' => (float) ($revenueByMonth["{$date->year}-{$date->month}"]->revenue ?? 0),
            ])->values()->toArray();

            // This month's expenses for P&L mini card
            $thisMonthExpenses = (float) Expense::whereYear('expense_date', now()->year)
                ->whereMonth('expense_date', now()->month)
                ->sum('amount');

            $thisMonthRevenue = (float) Sale::completed()
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum('total');
        }

        // -----------------------------------------------------------------------
        // Total products / categories counts
        // -----------------------------------------------------------------------

        $totalProducts = $user->can('view_products') ? Product::active()->count() : null;

        return view('auth::dashboard', compact(
            'todaySalesCount',
            'todayRevenue',
            'lowStockCount',
            'pendingOrdersCount',
            'recentSales',
            'lowStockProducts',
            'pendingOrders',
            'monthlyChartData',
            'threshold',
            'currencySymbol',
            'totalProducts',
            'thisMonthRevenue',
            'thisMonthExpenses',
        ));
    }
}
