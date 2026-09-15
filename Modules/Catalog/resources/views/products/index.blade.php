@extends('auth::layouts.admin')

@section('title', 'Products')
@section('page-title', 'Products')

@section('breadcrumb')
    <li class="breadcrumb-item active">Products</li>
@endsection

@section('content')
{{-- Search / Filter bar --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.products.index') }}" class="form-inline flex-wrap">
            <div class="input-group input-group-sm mr-2 mb-1" style="min-width:220px;">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                       class="form-control" placeholder="Search name or SKU…">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>

            <select name="category_id" class="form-control form-control-sm mr-2 mb-1" style="min-width:180px;">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->full_name }}
                    </option>
                @endforeach
            </select>

            <select name="is_active" class="form-control form-control-sm mr-2 mb-1">
                <option value="">All statuses</option>
                <option value="1" {{ isset($filters['is_active']) && $filters['is_active'] == '1' ? 'selected' : '' }}>Active</option>
                <option value="0" {{ isset($filters['is_active']) && $filters['is_active'] == '0' ? 'selected' : '' }}>Inactive</option>
            </select>

            <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-sm btn-secondary mb-1">
                <i class="fas fa-times mr-1"></i> Clear
            </a>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-box-open mr-2"></i>
            All Products
            <span class="badge badge-secondary ml-1">{{ $products->total() }}</span>
        </h3>
        <div class="card-tools">
            @can('manage_products')
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add Product
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th style="width:60px;">Image</th>
                    <th>Name / SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Cost</th>
                    <th>Status</th>
                    @can('manage_products')
                    <th class="text-right">Actions</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                 class="img-thumbnail" style="width:48px;height:48px;object-fit:cover;">
                        @else
                            <div class="bg-light d-flex align-items-center justify-content-center"
                                 style="width:48px;height:48px;border-radius:4px;">
                                <i class="fas fa-image text-muted"></i>
                            </div>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.products.show', $product) }}" class="font-weight-bold text-dark">
                            {{ $product->name }}
                        </a>
                        <br><small class="text-muted"><code>{{ $product->sku }}</code></small>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $product->category->name ?? '—' }}</span>
                    </td>
                    <td>${{ number_format($product->price, 2) }}</td>
                    <td>${{ number_format($product->cost_price, 2) }}</td>
                    <td>
                        @if($product->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    @can('manage_products')
                    <td class="text-right text-nowrap">
                        {{-- Toggle active --}}
                        <form method="POST" action="{{ route('admin.products.toggle', $product) }}" class="d-inline">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    class="btn btn-sm {{ $product->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}"
                                    title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">
                                <i class="fas {{ $product->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                            </button>
                        </form>
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                    data-confirm="Delete product &quot;{{ $product->name }}&quot;? This cannot be undone.">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">
                        No products found.
                        @if(!empty($filters))
                            <a href="{{ route('admin.products.index') }}">Clear filters</a>
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
