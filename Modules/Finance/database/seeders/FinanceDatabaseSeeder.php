<?php

namespace Modules\Finance\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Finance\Models\Expense;

class FinanceDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::first();

        if (! $owner) {
            return;
        }

        // Demo expenses — only seed if table is empty (idempotent)
        if (Expense::count() > 0) {
            return;
        }

        $expenses = [
            ['category' => 'rent',       'amount' => 1500.00, 'note' => 'Monthly store rent',         'expense_date' => now()->startOfMonth()->toDateString()],
            ['category' => 'utilities',  'amount' => 180.00,  'note' => 'Electricity & water',        'expense_date' => now()->startOfMonth()->toDateString()],
            ['category' => 'salaries',   'amount' => 3200.00, 'note' => 'Staff salaries',             'expense_date' => now()->startOfMonth()->toDateString()],
            ['category' => 'supplies',   'amount' => 95.00,   'note' => 'Office stationery',          'expense_date' => now()->subDays(10)->toDateString()],
            ['category' => 'marketing',  'amount' => 250.00,  'note' => 'Social media ads',           'expense_date' => now()->subDays(5)->toDateString()],
        ];

        foreach ($expenses as $data) {
            Expense::create(array_merge($data, ['created_by' => $owner->id]));
        }
    }
}
