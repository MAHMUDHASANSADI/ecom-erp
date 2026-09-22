@extends('storefront::layouts.shop')

@section('title', $selectedCategory ? $selectedCategory->name : 'All Products')

@section('content')
<div class="container mt-4">
    <div class="row">

        {{-- Category sidebar --}}
        <div class="col-md-3 mb-4">
            <div class="category-sidebar">
                <h6 class="font-weight-bold mb-3 text-uppercase" style="letter-spacing:.05em; font-size:.75rem; color:#718096;">
                    <i class="fas fa-th-list mr-1"></i> Categories
                </h6>
                <div class="list-group">
                    <a href="{{ route('storefront.products.index') }}"
                       class="list-group-item list-group-item-action {{ !$selectedCategory ? 'active' : '' }}">
                        All Products
                    </a>
                    @foreach($topCategories as $cat)
                        <a href="{{ route('storefront.products.category', $cat->slug) }}"
                           class="list-group-item list-group-item-action {{ $selectedCategory?->id === $cat->id ? 'active' : '' }}">
                            {{ $cat->name }}
                        </a>
                        @foreach($cat->children as $child)
                            <a href="{{ route('storefront.products.category', $child->slug) }}"
                               class="list-group-item list-group-item-action category-child {{ $selectedCategory?->id === $child->id ? 'active' : '' }}">
                                <i class="fas fa-angle-right mr-1"></i>{{ $child->name }}
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Product grid --}}
        <div class="col-md-9">
            {{-- Header row --}}
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    @if($selectedCategory)
                        <h5 class="mb-0">{{ $selectedCategory->full_name }}</h5>
                    @elseif(!empty($filters['search']))
                        <h5 class="mb-0">Search: "{{ $filters['search'] }}"</h5>
                    @else
                        <h5 class="mb-0">All Products</h5>
                    @endif
                    <small class="text-muted">{{ $productList->total() }} product(s) found</small>
                </div>
                @if(!empty($filters['search']) || $selectedCategory)
                    <a href="{{ route('storefront.products.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times mr-1"></i> Clear filter
                    </a>
                @endif
            </div>

            @if($productList->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                    <p>No products found.</p>
                    <a href="{{ route('storefront.products.index') }}" class="btn btn-outline-primary btn-sm">View all products</a>
                </div>
            @else
                <div class="row">
                    @foreach($productList as $product)
                    <div class="col-sm-6 col-lg-4 mb-4">
                        <div class="card product-card">
                            <a href="{{ route('storefront.products.show', $product) }}">
                                @if($product->image_url)
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" loading="lazy">
                                @else
                                    <div class="no-img"><i class="fas fa-image"></i></div>
                                @endif
                            </a>
                            <div class="card-body d-flex flex-column">
                                <span class="badge badge-secondary category-badge mb-1">
                                    {{ $product->category->name ?? '' }}
                                </span>
                                <a href="{{ route('storefront.products.show', $product) }}"
                                   class="text-dark font-weight-semibold mb-1"
                                   style="text-decoration:none; line-height:1.3;">
                                    {{ $product->name }}
                                </a>
                                <div class="mt-auto pt-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="price">${{ number_format($product->price, 2) }}</span>
                                        @php $stock = $product->withSum('stockMovements as current_stock','quantity_change')->find($product->id)?->current_stock ?? $product->current_stock ?? 0; @endphp
                                        @if($stock <= 0)
                                            <span class="badge badge-danger">Out of stock</span>
                                        @else
                                            <form method="POST" action="{{ route('storefront.cart.add') }}">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <input type="hidden" name="quantity" value="1">
                                                <button type="submit" class="btn btn-sm btn-dark">
                                                    <i class="fas fa-cart-plus"></i> Add
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="mt-2">
                    {{ $productList->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
