<?php

namespace Modules\Finance\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'category',
        'amount',
        'note',
        'expense_date',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expense_date' => 'date',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param Builder $query */
    public function scopeInMonth($query, int $year, int $month): Builder
    {
        return $query->whereYear('expense_date', $year)
            ->whereMonth('expense_date', $month);
    }

    /** @param Builder $query */
    public function scopeInDateRange($query, string $from, string $to): Builder
    {
        return $query->whereBetween('expense_date', [$from, $to]);
    }

    /** @param Builder $query */
    public function scopeByCategory($query, string $category): Builder
    {
        return $query->where('category', $category);
    }
}
