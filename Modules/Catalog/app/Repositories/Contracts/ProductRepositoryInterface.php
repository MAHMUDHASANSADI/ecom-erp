<?php

namespace Modules\Catalog\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Catalog\Models\Product;

interface ProductRepositoryInterface
{
    /**
     * Paginated product list with optional search and category filter.
     *
     * @param  array{search?: string, category_id?: int, is_active?: bool}  $filters
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function findBySku(string $sku): ?Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product;

    public function delete(Product $product): void;
}
