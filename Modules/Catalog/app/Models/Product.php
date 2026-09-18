<?php

namespace Modules\Catalog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Inventory\Models\StockMovement;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'price',
        'cost_price',
        'tax_class',
        'description',
        'image_path',
        'is_active',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * All stock movement ledger entries for this product.
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class, 'product_id')
            ->orderByDesc('created_at');
    }

    /**
     * Current stock on hand — sum of all quantity_change entries.
     * Returns 0 if no movements exist yet.
     */
    public function getCurrentStockAttribute(): int
    {
        return (int) $this->stockMovements()->sum('quantity_change');
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    /** @param Builder $query */
    public function scopeActive($query): Builder
    {
        return $query->where('is_active', true);
    }

    /** @param Builder $query */
    public function scopeSearch($query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term): void {
            $q->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    /** @param Builder $query */
    public function scopeInCategory($query, int $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Return the public URL for the product image, or null if none.
     */
    public function getImageUrlAttribute(): ?string
    {
        return $this->image_path
            ? asset('storage/'.$this->image_path)
            : null;
    }
}
