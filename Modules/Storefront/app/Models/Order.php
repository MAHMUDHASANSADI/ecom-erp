<?php

namespace Modules\Storefront\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\POS\Models\Sale;

class Order extends Model
{
    protected $fillable = [
        'sale_id',
        'customer_name',
        'email',
        'address',
        'status',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'meta' => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param Builder $query */
    public function scopeByStatus($query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /** @param Builder $query */
    public function scopePending($query): Builder
    {
        return $query->where('status', 'pending');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Total order value — sum of (unit_price × quantity) across all items.
     */
    public function getTotalAttribute(): float
    {
        return round(
            $this->items->sum(fn ($item) => $item->unit_price * $item->quantity),
            2
        );
    }
}
