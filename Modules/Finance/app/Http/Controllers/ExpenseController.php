<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Auth\Models\Lookup;
use Modules\Finance\Events\ExpenseLogged;
use Modules\Finance\Http\Requests\StoreExpenseRequest;
use Modules\Finance\Models\Expense;
use Modules\Finance\Repositories\Contracts\ExpenseRepositoryInterface;

class ExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseRepositoryInterface $expenses
    ) {}

    public function index(Request $request): View
    {
        $filters = array_filter(
            $request->only(['category', 'date_from', 'date_to']),
            fn ($v) => $v !== '' && $v !== null
        );

        $expenses = $this->expenses->paginate($filters, 25);
        $categories = Lookup::ofType('expense_category');

        // Running total for current filter
        $periodTotal = null;
        if (! empty($filters['date_from']) || ! empty($filters['date_to'])) {
            $from = $filters['date_from'] ?? '2000-01-01';
            $to = $filters['date_to'] ?? now()->toDateString();
            $periodTotal = $this->expenses->totalsForPeriod($from, $to)['total'];
        }

        return view('finance::expenses.index', compact('expenses', 'categories', 'filters', 'periodTotal'));
    }

    public function create(): View
    {
        $categories = Lookup::ofType('expense_category');

        return view('finance::expenses.create', compact('categories'));
    }

    public function store(StoreExpenseRequest $request): RedirectResponse
    {
        $expense = $this->expenses->create([
            'category' => $request->category,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'note' => $request->note,
            'created_by' => auth()->id(),
        ]);

        // Fire ExpenseLogged → RecalculateMonthlyProfitSnapshot (queued)
        ExpenseLogged::dispatch($expense);

        return redirect()->route('admin.finance.expenses')
            ->with('success', 'Expense logged successfully.');
    }

    public function edit(Expense $expense): View
    {
        $categories = Lookup::ofType('expense_category');

        return view('finance::expenses.edit', compact('expense', 'categories'));
    }

    public function update(StoreExpenseRequest $request, Expense $expense): RedirectResponse
    {
        $this->expenses->update($expense, [
            'category' => $request->category,
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'note' => $request->note,
        ]);

        return redirect()->route('admin.finance.expenses')
            ->with('success', 'Expense updated successfully.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $this->expenses->delete($expense);

        return redirect()->route('admin.finance.expenses')
            ->with('success', 'Expense deleted successfully.');
    }
}
