<?php

namespace Modules\Finance\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Finance\Models\Expense;

interface ExpenseRepositoryInterface
{
    /**
     * Paginated expense list with optional filters.
     *
     * @param  array{category?: string, date_from?: string, date_to?: string}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function findById(int $id): ?Expense;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Expense;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Expense $expense, array $data): Expense;

    public function delete(Expense $expense): void;

    /**
     * Total expenses for a date range, optionally grouped by category.
     *
     * @return array{total: float, by_category: array<string, float>}
     */
    public function totalsForPeriod(string $from, string $to): array;

    /**
     * Monthly totals for a given year.
     *
     * @return array<int, float> month_number => total
     */
    public function monthlyTotals(int $year): array;
}
