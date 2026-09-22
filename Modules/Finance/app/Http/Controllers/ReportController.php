<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Auth\Models\Setting;
use Modules\Finance\Repositories\Contracts\ExpenseRepositoryInterface;
use Modules\POS\Models\Sale;
use Modules\POS\Models\SaleItem;

class ReportController extends Controller
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses
    ) {}

    // -------------------------------------------------------------------------
    // Sales Report — daily and monthly summaries
    // -------------------------------------------------------------------------

    public function sales(Request $request): View
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        // Daily sales totals for the selected month
        $dailySales = Sale::completed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->select(
                DB::raw('DATE(created_at) as sale_date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as transactions')
            )
            ->groupBy('sale_date')
            ->orderBy('sale_date')
            ->get();

        // Monthly totals for the selected year
        $monthlySales = Sale::completed()
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as transactions')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        // Fill all 12 months
        $monthlyRevenue = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthlyRevenue[$m] = round((float) ($monthlySales[$m] ?? 0), 2);
        }

        // Top-selling products this month
        $topProducts = SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'completed')
            ->whereYear('sales.created_at', $year)
            ->whereMonth('sales.created_at', $month)
            ->select(
                'sale_items.product_id',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.quantity * sale_items.unit_price) as total_revenue')
            )
            ->groupBy('sale_items.product_id')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->with('product:id,name,sku')
            ->get();

        $currencySymbol = Setting::getValue('currency_symbol', '$');

        return view('finance::reports.sales', compact(
            'dailySales', 'monthlyRevenue', 'topProducts',
            'month', 'year', 'currencySymbol'
        ));
    }

    // -------------------------------------------------------------------------
    // Profit Report — revenue minus cost minus expenses
    // -------------------------------------------------------------------------

    public function profit(Request $request): View
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $from = sprintf('%04d-%02d-01', $year, $month);
        $to = now()->setYear($year)->setMonth($month)->endOfMonth()->toDateString();

        // Revenue from completed sales in period
        $revenue = (float) Sale::completed()
            ->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
            ->sum('total');

        // Cost of goods sold — sum of (cost_price × quantity) for sold items
        $cogs = (float) SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        // Expenses in period
        $expenseTotals = $this->expenses->totalsForPeriod($from, $to);
        $totalExpenses = $expenseTotals['total'];
        $expenseByCategory = $expenseTotals['by_category'];

        // Gross profit = revenue − COGS
        $grossProfit = $revenue - $cogs;
        // Net profit = gross profit − expenses
        $netProfit = $grossProfit - $totalExpenses;
        // Gross margin %
        $grossMarginPct = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0;

        // 12-month view for year context
        $yearlyMonthlyRevenue = [];
        $yearlyMonthlyCogs = [];
        $yearlyMonthlyExpenses = $this->expenses->monthlyTotals($year);

        $monthlyRows = Sale::completed()
            ->whereYear('created_at', $year)
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(total) as revenue')
            )
            ->groupBy('month')
            ->pluck('revenue', 'month')
            ->toArray();

        for ($m = 1; $m <= 12; $m++) {
            $yearlyMonthlyRevenue[$m] = round((float) ($monthlyRows[$m] ?? 0), 2);
        }

        $currencySymbol = Setting::getValue('currency_symbol', '$');

        return view('finance::reports.profit', compact(
            'revenue', 'cogs', 'grossProfit', 'netProfit',
            'grossMarginPct', 'totalExpenses', 'expenseByCategory',
            'yearlyMonthlyRevenue', 'yearlyMonthlyExpenses',
            'month', 'year', 'currencySymbol'
        ));
    }

    // -------------------------------------------------------------------------
    // CSV Exports
    // -------------------------------------------------------------------------

    public function exportSalesCsv(Request $request): Response
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $sales = Sale::with('cashier')
            ->completed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at')
            ->get();

        $csv = "Sale Number,Date,Channel,Payment Method,Total,Cashier\n";

        foreach ($sales as $sale) {
            $csv .= implode(',', [
                $sale->sale_number,
                $sale->created_at->format('Y-m-d H:i'),
                $sale->channel,
                $sale->payment_method,
                number_format((float) $sale->total, 2, '.', ''),
                $sale->cashier?->name ?? '',
            ])."\n";
        }

        $filename = "sales-{$year}-{$month}.csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    public function exportExpensesCsv(Request $request): Response
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $from = sprintf('%04d-%02d-01', $year, $month);
        $to = now()->setYear($year)->setMonth($month)->endOfMonth()->toDateString();

        $expenses = $this->expenses
            ->paginate(['date_from' => $from, 'date_to' => $to], 9999)
            ->items();

        $csv = "Date,Category,Amount,Note,Logged By\n";

        foreach ($expenses as $expense) {
            $csv .= implode(',', [
                $expense->expense_date->format('Y-m-d'),
                $expense->category,
                number_format((float) $expense->amount, 2, '.', ''),
                '"'.str_replace('"', '""', $expense->note ?? '').'"',
                $expense->creator?->name ?? '',
            ])."\n";
        }

        $filename = "expenses-{$year}-{$month}.csv";

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // -------------------------------------------------------------------------
    // PDF Exports
    // -------------------------------------------------------------------------

    public function exportSalesPdf(Request $request): Response
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        $sales = Sale::with('cashier')
            ->completed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at')
            ->get();

        $totalRevenue = round((float) $sales->sum('total'), 2);
        $currencySymbol = Setting::getValue('currency_symbol', '$');
        $appName = Setting::getValue('app_name', config('app.name'));

        $pdf = Pdf::loadView('finance::reports.pdf.sales', compact(
            'sales', 'totalRevenue', 'month', 'year', 'currencySymbol', 'appName'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("sales-{$year}-{$month}.pdf");
    }

    public function exportProfitPdf(Request $request): Response
    {
        $month = (int) $request->query('month', now()->month);
        $year = (int) $request->query('year', now()->year);

        // Reuse profit data — call profit() but capture view data instead
        $from = sprintf('%04d-%02d-01', $year, $month);
        $to = now()->setYear($year)->setMonth($month)->endOfMonth()->toDateString();

        $revenue = (float) Sale::completed()
            ->whereBetween('created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
            ->sum('total');

        $cogs = (float) SaleItem::join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'completed')
            ->whereBetween('sales.created_at', ["{$from} 00:00:00", "{$to} 23:59:59"])
            ->sum(DB::raw('sale_items.quantity * products.cost_price'));

        $expenseTotals = $this->expenses->totalsForPeriod($from, $to);
        $totalExpenses = $expenseTotals['total'];
        $expenseByCategory = $expenseTotals['by_category'];
        $grossProfit = $revenue - $cogs;
        $netProfit = $grossProfit - $totalExpenses;
        $grossMarginPct = $revenue > 0 ? round(($grossProfit / $revenue) * 100, 1) : 0;

        $currencySymbol = Setting::getValue('currency_symbol', '$');
        $appName = Setting::getValue('app_name', config('app.name'));

        $pdf = Pdf::loadView('finance::reports.pdf.profit', compact(
            'revenue', 'cogs', 'grossProfit', 'netProfit', 'grossMarginPct',
            'totalExpenses', 'expenseByCategory',
            'month', 'year', 'currencySymbol', 'appName'
        ))->setPaper('a4', 'portrait');

        return $pdf->download("profit-{$year}-{$month}.pdf");
    }
}
