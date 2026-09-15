<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Catalog\Http\Requests\StoreCategoryRequest;
use Modules\Catalog\Http\Requests\UpdateCategoryRequest;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Repositories\Contracts\CategoryRepositoryInterface;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryRepositoryInterface $categories
    ) {}

    public function index(): View
    {
        $categories = $this->categories->allNested();

        return view('catalog::categories.index', compact('categories'));
    }

    public function create(): View
    {
        $parents = $this->categories->allFlat()
            ->whereNull('parent_category_id')
            ->sortBy('name')
            ->pluck('name', 'id');

        return view('catalog::categories.create', compact('parents'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $slug = Category::generateSlug($request->name);

        $this->categories->create([
            'name' => $request->name,
            'slug' => $slug,
            'parent_category_id' => $request->parent_category_id ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        $parents = $this->categories->allFlat()
            ->whereNull('parent_category_id')
            ->where('id', '!=', $category->id)
            ->sortBy('name')
            ->pluck('name', 'id');

        return view('catalog::categories.edit', compact('category', 'parents'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $slug = Category::generateSlug($request->name, $category->id);

        $this->categories->update($category, [
            'name' => $request->name,
            'slug' => $slug,
            'parent_category_id' => $request->parent_category_id ?: null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->count() > 0) {
            return back()->with('error', "Cannot delete \"{$category->name}\" — it has {$category->products()->count()} product(s) assigned. Reassign them first.");
        }

        if ($category->children()->count() > 0) {
            return back()->with('error', "Cannot delete \"{$category->name}\" — it has sub-categories. Delete or reassign them first.");
        }

        $this->categories->delete($category);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully.');
    }
}
