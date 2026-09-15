<?php

namespace Modules\Catalog\Repositories\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Catalog\Models\Category;

interface CategoryRepositoryInterface
{
    /**
     * All top-level categories with their children eager-loaded.
     *
     * @return Collection<int, Category>
     */
    public function allNested(): Collection;

    /**
     * Flat list of all categories ordered by name, for dropdowns.
     *
     * @return Collection<int, Category>
     */
    public function allFlat(): Collection;

    public function findById(int $id): ?Category;

    public function findBySlug(string $slug): ?Category;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Category;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Category $category, array $data): Category;

    public function delete(Category $category): void;
}
