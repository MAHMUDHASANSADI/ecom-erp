<?php

namespace Modules\Finance\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Modules\Auth\Models\ActivityLog;
use Modules\Finance\Events\ExpenseLogged;

/**
 * Queued — recalculating profit is a side effect that must not block
 * the expense save response.
 *
 * In v1 this logs the event to the activity trail as a hook point.
 * Future: persist monthly snapshot to a dedicated table for fast report queries.
 */
class RecalculateMonthlyProfitSnapshot implements ShouldQueue
{
    public function handle(ExpenseLogged $event): void
    {
        $expense = $event->expense;

        ActivityLog::record(
            subject: $expense,
            description: 'expense_logged',
            causerId: $expense->created_by,
            properties: [
                'category' => $expense->category,
                'amount' => (float) $expense->amount,
                'expense_date' => $expense->expense_date->toDateString(),
            ]
        );
    }
}
