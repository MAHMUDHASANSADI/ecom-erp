@extends('auth::layouts.admin')

@section('title', $product->name)
@section('page-title', $product->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fas fa-box mr-2"></i>{{ $product->name }}</h3>
                <div>
                    @can('manage_products')
                    <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">SKU</dt>
                    <dd class="col-sm-9"><code>{{ $product->sku }}</code></dd>

                    <dt class="col-sm-3 text-muted">Category</dt>
                    <dd class="col-sm-9">{{ $product->category->full_name ?? '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Selling Price</dt>
                    <dd class="col-sm-9"><strong>${{ number_format($product->price, 2) }}</strong></dd>

                    <dt class="col-sm-3 text-muted">Cost Price</dt>
                    <dd class="col-sm-9">${{ number_format($product->cost_price, 2) }}</dd>

                    <dt class="col-sm-3 text-muted">Margin</dt>
                    <dd class="col-sm-9">
                        @php $margin = $product->price > 0 ? (($product->price - $product->cost_price) / $product->price) * 100 : 0; @endphp
                        <span class="badge badge-{{ $margin > 20 ? 'success' : ($margin > 0 ? 'warning' : 'danger') }}">
                            {{ number_format($margin, 1) }}%
                        </span>
                    </dd>

                    <dt class="col-sm-3 text-muted">Tax Class</dt>
                    <dd class="col-sm-9">{{ $product->tax_class ?: '—' }}</dd>

                    <dt class="col-sm-3 text-muted">Status</dt>
                    <dd class="col-sm-9">
                        @if($product->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </dd>

                    @if($product->description)
                    <dt class="col-sm-3 text-muted">Description</dt>
                    <dd class="col-sm-9">{{ $product->description }}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        @if($product->image_url)
        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title">Product Image</h3></div>
            <div class="card-body text-center">
                <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                     class="img-fluid img-thumbnail" style="max-height:250px;">
            </div>
        </div>
        @endif

        <div class="card card-outline card-secondary">
            <div class="card-header"><h3 class="card-title">Stock</h3></div>
            <div class="card-body text-center">
                <h2 class="text-primary" id="stockLevel">—</h2>
                <p class="text-muted mb-0">Units in stock</p>
                <small class="text-muted">(Inventory module — Phase 3)</small>
            </div>
        </div>
    </div>
</div>
@endsection
