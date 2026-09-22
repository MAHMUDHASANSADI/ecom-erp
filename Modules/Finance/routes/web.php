<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\ExpenseController;
use Modules\Finance\Http\Controllers\ReportController;

Route::middleware(['auth', 'active.user'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {

        // ---------------------------------------------------------------
        // Reports — view_finance_reports permission
        // Route names match seeded nav: admin.finance.sales, admin.finance.profit
        // ---------------------------------------------------------------
        Route::middleware('permission:view_finance_reports')->group(function (): void {

            // Sales report — admin.finance.sales
            Route::get('finance/sales', [ReportController::class, 'sales'])
                ->name('finance.sales');

            // Profit report — admin.finance.profit
            Route::get('finance/profit', [ReportController::class, 'profit'])
                ->name('finance.profit');

            // CSV exports
            Route::get('finance/sales/export/csv', [ReportController::class, 'exportSalesCsv'])
                ->name('finance.sales.csv');

            Route::get('finance/expenses/export/csv', [ReportController::class, 'exportExpensesCsv'])
                ->name('finance.expenses.csv');

            // PDF exports
            Route::get('finance/sales/export/pdf', [ReportController::class, 'exportSalesPdf'])
                ->name('finance.sales.pdf');

            Route::get('finance/profit/export/pdf', [ReportController::class, 'exportProfitPdf'])
                ->name('finance.profit.pdf');
        });

        // ---------------------------------------------------------------
        // Expenses — manage_expenses permission
        // Route name matches seeded nav: admin.finance.expenses
        // Static 'create' segment must come before {expense} wildcard
        // ---------------------------------------------------------------
        Route::middleware('permission:manage_expenses')->group(function (): void {

            Route::get('finance/expenses/create', [ExpenseController::class, 'create'])
                ->name('finance.expenses.create');

            Route::post('finance/expenses', [ExpenseController::class, 'store'])
                ->name('finance.expenses.store');

            Route::get('finance/expenses/{expense}/edit', [ExpenseController::class, 'edit'])
                ->name('finance.expenses.edit');

            Route::put('finance/expenses/{expense}', [ExpenseController::class, 'update'])
                ->name('finance.expenses.update');

            Route::delete('finance/expenses/{expense}', [ExpenseController::class, 'destroy'])
                ->name('finance.expenses.destroy');
        });

        // Expense list visible to both view_finance_reports AND manage_expenses
        Route::middleware('permission:view_finance_reports')->group(function (): void {
            Route::get('finance/expenses', [ExpenseController::class, 'index'])
                ->name('finance.expenses');
        });
    });
