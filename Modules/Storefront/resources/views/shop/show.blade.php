@extends('storefront::layouts.shop')

@section('title', $product->name)

@section('content')
<div class="container mt-4">

    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb shop-breadcrumb px-0">
            <li class="breadcrumb-item"><a href="{{ route('storefront.products.index') }}">Shop</a></li>
            @if($product->category)
                <li class="breadcrumb-item">
                    <a href="{{ route('storefront.products.category', $product->category->slug) }}">
                        {{ $product->category->name }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="row">
        {{-- Product image --}}
        <div class="col-md-5 mb-4">
            <div class="card border-0" style="border-radius:12px; overflow:hidden;">
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                         class="img-fluid" style="max-height:420px; object-fit:contain; background:#f7fafc; padding:1rem;">
                @else
                    <div class="bg-light d-flex align-items-center justify-content-center"
                         style="height:320px; border-radius:12px;">
                        <i class="fas fa-image text-muted" style="font-size:4rem;"></i>
                    </div>
                @endif
            </div>
        </div>

        {{-- Product info --}}
        <div class="col-md-7">
            <span class="badge badge-secondary mb-2">{{ $product->category->full_name ?? '' }}</span>
            <h2 class="font-weight-bold mb-1">{{ $product->name }}</h2>
            <p class="text-muted mb-2" style="font-size:.85rem;">SKU: <code>{{ $product->sku }}</code></p>

            <h3 class="text-success font-weight-bold mb-3">${{ number_format($product->price, 2) }}</h3>

            @php $currentStock = $product->current_stock; @endphp
            @if($currentStock <= 0)
                <div class="alert alert-danger py-2">
                    <i class="fas fa-times-circle mr-1"></i> Out of stock — check back soon.
                </div>
            @elseif($currentStock <= 5)
                <div class="alert alert-warning py-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Only <strong>{{ $currentStock }}</strong> left in stock!
                </div>
            @else
                <div class="alert alert-success py-2">
                    <i class="fas fa-check-circle mr-1"></i> In stock
                </div>
            @endif

            @if($product->description)
                <p class="text-muted mb-3" style="line-height:1.7;">{{ $product->description }}</p>
            @endif

            @if($currentStock > 0)
            <form method="POST" action="{{ route('storefront.cart.add') }}" class="d-flex align-items-center">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="input-group mr-3" style="max-width:120px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text">Qty</span>
                    </div>
                    <input type="number" name="quantity" value="1" min="1"
                           max="{{ $currentStock }}" class="form-control text-center">
                </div>
                <button type="submit" class="btn btn-dark btn-lg px-4">
                    <i class="fas fa-cart-plus mr-1"></i> Add to Cart
                </button>
            </form>

            <a href="{{ route('storefront.cart.index') }}" class="btn btn-outline-secondary mt-2">
                <i class="fas fa-shopping-cart mr-1"></i> View Cart
            </a>
            @endif
        </div>
    </div>

    {{-- Related products --}}
    @if($related->isNotEmpty())
    <div class="mt-5">
        <h5 class="font-weight-bold mb-3">Related Products</h5>
        <div class="row">
            @foreach($related as $rel)
            <div class="col-sm-6 col-md-3 mb-4">
                <div class="card product-card">
                    <a href="{{ route('storefront.products.show', $rel) }}">
                        @if($rel->image_url)
                            <img src="{{ $rel->image_url }}" alt="{{ $rel->name }}" loading="lazy">
                        @else
                            <div class="no-img"><i class="fas fa-image"></i></div>
                        @endif
                    </a>
                    <div class="card-body">
                        <p class="mb-1 font-weight-semibold" style="font-size:.875rem;">{{ $rel->name }}</p>
                        <span class="price" style="font-size:.95rem;">${{ number_format($rel->price, 2) }}</span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>
@endsection
