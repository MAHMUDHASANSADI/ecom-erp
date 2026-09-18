<?php

namespace Modules\POS\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    protected $fillable = [
        'sale_number',
        'channel',
        'total',
        'payment_method',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class, 'sale_id');
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param Builder $query */
    public function scopeToday($query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    /** @param Builder $query */
    public function scopeForDate($query, string $date): Builder
    {
        return $query->whereDate('created_at', $date);
    }

    /** @param Builder $query */
    public function scopeCompleted($query): Builder
    {
        return $query->where('status', 'completed');
    }

    /** @param Builder $query */
    public function scopePosChannel($query): Builder
    {
        return $query->where('channel', 'pos');
    }
}
