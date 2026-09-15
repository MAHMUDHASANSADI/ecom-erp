<?php

namespace Modules\Catalog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Modules\Catalog\Http\Requests\StoreProductRequest;
use Modules\Catalog\Http\Requests\UpdateProductRequest;
use Modules\Catalog\Models\Product;
use Modules\Catalog\Repositories\Contracts\CategoryRepositoryInterface;
use Modules\Catalog\Repositories\Contracts\ProductRepositoryInterface;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly CategoryRepositoryInterface $categories
    ) {}

    public function index(Request $request): View
    {
        $filters = $request->only(['search', 'category_id', 'is_active']);
        // Convert empty strings to null so repository filters work correctly
        $filters = array_filter($filters, fn ($v) => $v !== '' && $v !== null);

        $products = $this->products->paginate($filters, 20);
        $categories = $this->categories->allFlat()->sortBy('name');

        return view('catalog::products.index', compact('products', 'categories', 'filters'));
    }

    public function create(): View
    {
        $categories = $this->buildCategoryOptions();

        return view('catalog::products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->except('image');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $this->products->create($data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product): View
    {
        $product->load('category');

        return view('catalog::products.show', compact('product'));
    }

    public function edit(Product $product): View
    {
        $categories = $this->buildCategoryOptions();

        return view('catalog::products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->except('image');
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            // Remove old image if present
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }

        $this->products->update($product, $data);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $this->products->delete($product);

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $this->products->update($product, ['is_active' => ! $product->is_active]);

        $status = $product->is_active ? 'deactivated' : 'activated';

        return back()->with('success', "Product {$status} successfully.");
    }

    /**
     * Build a flat options list for the category select with parent > child indentation.
     *
     * @return array<int, string>
     */
    private function buildCategoryOptions(): array
    {
        $options = [];

        $topLevel = $this->categories->allFlat()
            ->whereNull('parent_category_id')
            ->sortBy('name');

        foreach ($topLevel as $parent) {
            $options[$parent->id] = $parent->name;

            $children = $this->categories->allFlat()
                ->where('parent_category_id', $parent->id)
                ->sortBy('name');

            foreach ($children as $child) {
                $options[$child->id] = "\xc2\xa0\xc2\xa0\xc2\xa0\xc2\xa0↳ {$child->name}";
            }
        }

        return $options;
    }
}
