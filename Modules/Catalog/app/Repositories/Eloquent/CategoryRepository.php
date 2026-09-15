<?php

namespace Modules\Catalog\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function allNested(): Collection
    {
        return Category::with('children')
            ->whereNull('parent_category_id')
            ->orderBy('name')
            ->get();
    }

    public function allFlat(): Collection
    {
        return Category::orderBy('name')->get();
    }

    public function findById(int $id): ?Category
    {
        return Category::find($id);
    }

    public function findBySlug(string $slug): ?Category
    {
        return Category::where('slug', $slug)->first();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(Category $category): void
    {
        $category->delete();
    }
}
