<?php

namespace Modules\Inventory\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Catalog\Models\Product;

class StockMovement extends Model
{
    /**
     * Append-only — no updated_at column exists.
     */
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'quantity_change',
        'reason',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity_change' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param Builder $query */
    public function scopeForProduct($query, int $productId): Builder
    {
        return $query->where('product_id', $productId);
    }

    /** @param Builder $query */
    public function scopeInDateRange($query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Whether this movement increased stock.
     */
    public function isStockIn(): bool
    {
        return $this->quantity_change > 0;
    }

    /**
     * Absolute quantity (always positive) for display.
     */
    public function getAbsoluteQuantityAttribute(): int
    {
        return abs($this->quantity_change);
    }
}
