<?php

namespace Modules\Finance\Repositories\Eloquent;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Expense;
use Modules\Finance\Repositories\Contracts\ExpenseRepositoryInterface;

class ExpenseRepository implements ExpenseRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = Expense::with('creator')->orderByDesc('expense_date');

        if (! empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (! empty($filters['date_from'])) {
            $query->where('expense_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->where('expense_date', '<=', $filters['date_to']);
        }

        return $query->paginate($perPage);
    }

    public function findById(int $id): ?Expense
    {
        return Expense::with('creator')->find($id);
    }

    public function create(array $data): Expense
    {
        return Expense::create($data);
    }

    public function update(Expense $expense, array $data): Expense
    {
        $expense->update($data);

        return $expense->fresh();
    }

    public function delete(Expense $expense): void
    {
        $expense->delete();
    }

    public function totalsForPeriod(string $from, string $to): array
    {
        $expenses = Expense::whereBetween('expense_date', [$from, $to])->get();

        $byCategory = $expenses->groupBy('category')
            ->map(fn ($group) => round((float) $group->sum('amount'), 2))
            ->toArray();

        return [
            'total' => round((float) $expenses->sum('amount'), 2),
            'by_category' => $byCategory,
        ];
    }

    public function monthlyTotals(int $year): array
    {
        $rows = Expense::whereYear('expense_date', $year)
            ->select(DB::raw('MONTH(expense_date) as month'), DB::raw('SUM(amount) as total'))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill all 12 months, defaulting to 0
        $result = [];
        for ($m = 1; $m <= 12; $m++) {
            $result[$m] = round((float) ($rows[$m] ?? 0), 2);
        }

        return $result;
    }
}
