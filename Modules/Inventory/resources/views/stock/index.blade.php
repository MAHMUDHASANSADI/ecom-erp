@extends('auth::layouts.admin')

@section('title', 'Stock Levels')
@section('page-title', 'Stock Levels')

@section('breadcrumb')
    <li class="breadcrumb-item active">Inventory</li>
@endsection

@section('content')

{{-- Low-stock alert banner --}}
@if($lowStockCount > 0)
<div class="alert alert-warning alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    <i class="fas fa-exclamation-triangle mr-2"></i>
    <strong>{{ $lowStockCount }} product(s)</strong> are at or below the low-stock threshold
    ({{ $threshold }} units).
    <a href="{{ route('admin.inventory.index', ['low_stock' => 1]) }}" class="alert-link ml-1">View them</a>
</div>
@endif

{{-- Filter bar --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.inventory.index') }}" class="form-inline flex-wrap">
            <div class="input-group input-group-sm mr-2 mb-1" style="min-width:220px;">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                       class="form-control" placeholder="Search name or SKU…">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <select name="category_id" class="form-control form-control-sm mr-2 mb-1" style="min-width:180px;">
                <option value="">All categories</option>
                @foreach($categories as $id => $name)
                    <option value="{{ $id }}" {{ ($filters['category_id'] ?? '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            <div class="custom-control custom-checkbox mr-3 mb-1">
                <input type="checkbox" class="custom-control-input" id="low_stock"
                       name="low_stock" value="1" {{ !empty($filters['low_stock']) ? 'checked' : '' }}
                       onchange="this.form.submit()">
                <label class="custom-control-label" for="low_stock">Low stock only</label>
            </div>

            <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-secondary mb-1">
                <i class="fas fa-times mr-1"></i> Clear
            </a>
        </form>
    </div>
</div>

{{-- Stock table --}}
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-boxes mr-2"></i>All Products
            <span class="badge badge-secondary ml-1">{{ $products->total() }}</span>
        </h3>
        <div class="card-tools">
            @can('manage_inventory')
            <a href="{{ route('admin.inventory.adjust') }}" class="btn btn-success btn-sm">
                <i class="fas fa-plus-minus mr-1"></i> Adjust Stock
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Product</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                @php $isLow = $product->current_stock <= $threshold; @endphp
                <tr class="{{ $isLow ? 'table-warning' : '' }}">
                    <td>
                        <a href="{{ route('admin.inventory.product-history', $product) }}" class="font-weight-bold text-dark">
                            {{ $product->name }}
                        </a>
                    </td>
                    <td><code>{{ $product->sku }}</code></td>
                    <td><span class="badge badge-secondary">{{ $product->category->name ?? '—' }}</span></td>
                    <td class="text-center">
                        <span class="badge badge-{{ $product->current_stock <= 0 ? 'danger' : ($isLow ? 'warning' : 'success') }} px-3"
                              style="font-size:.9rem;">
                            {{ $product->current_stock }}
                        </span>
                    </td>
                    <td class="text-center">
                        @if($product->current_stock <= 0)
                            <span class="badge badge-danger">Out of Stock</span>
                        @elseif($isLow)
                            <span class="badge badge-warning">Low Stock</span>
                        @else
                            <span class="badge badge-success">In Stock</span>
                        @endif
                    </td>
                    <td class="text-right text-nowrap">
                        <a href="{{ route('admin.inventory.product-history', $product) }}"
                           class="btn btn-sm btn-info" title="Movement history">
                            <i class="fas fa-history"></i>
                        </a>
                        @can('manage_inventory')
                        <a href="{{ route('admin.inventory.adjust', ['product_id' => $product->id]) }}"
                           class="btn btn-sm btn-success" title="Adjust stock">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No products found.
                        @if(!empty($filters))
                            <a href="{{ route('admin.inventory.index') }}">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="card-footer">
        {{ $products->appends($filters)->links() }}
    </div>
    @endif
</div>
@endsection
