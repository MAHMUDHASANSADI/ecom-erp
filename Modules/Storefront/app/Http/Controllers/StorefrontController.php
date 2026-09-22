<?php

namespace Modules\Storefront\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Catalog\Models\Category;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Repositories\Contracts\CategoryRepositoryInterface;
use Modules\Catalog\Repositories\Contracts\ProductRepositoryInterface;

class StorefrontController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories
    ) {}

    /**
     * Public product listing with optional category filter and search.
     */
    public function index(Request $request): View
    {
        $filters = array_filter(
            $request->only(['search', 'category_id']),
            fn ($v) => $v !== '' && $v !== null
        );
        // Only show active products on the storefront
        $filters['is_active'] = true;

        $productList = $this->products->paginate($filters, 12);
        $topCategories = Category::active()->topLevel()
            ->with(['children' => fn ($q) => $q->active()])
            ->orderBy('name')
            ->get();

        $selectedCategory = ! empty($filters['category_id'])
            ? $this->categories->findById((int) $filters['category_id'])
            : null;

        return view('storefront::shop.index', compact(
            'productList', 'topCategories', 'selectedCategory', 'filters'
        ));
    }

    /**
     * Filter by category slug (e.g. /shop/electronics).
     */
    public function byCategory(string $slug): View
    {
        $category = $this->categories->findBySlug($slug);
        abort_if(! $category || ! $category->is_active, 404);

        $productList = $this->products->paginate([
            'category_id' => $category->id,
            'is_active' => true,
        ], 12);

        $topCategories = Category::active()->topLevel()
            ->with(['children' => fn ($q) => $q->active()])
            ->orderBy('name')
            ->get();

        return view('storefront::shop.index', [
            'productList' => $productList,
            'topCategories' => $topCategories,
            'selectedCategory' => $category,
            'filters' => ['category_id' => $category->id],
        ]);
    }

    /**
     * Public product detail page.
     */
    public function show(Product $product): View
    {
        abort_if(! $product->is_active, 404);
        $product->load('category');

        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->withSum('stockMovements as current_stock', 'quantity_change')
            ->limit(4)
            ->get();

        return view('storefront::shop.show', compact('product', 'related'));
    }
}
