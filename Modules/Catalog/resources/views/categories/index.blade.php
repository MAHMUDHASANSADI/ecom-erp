@extends('auth::layouts.admin')

@section('title', 'Categories')
@section('page-title', 'Categories')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Catalog</a></li>
    <li class="breadcrumb-item active">Categories</li>
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-sitemap mr-2"></i>All Categories</h3>
        <div class="card-tools">
            @can('manage_products')
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add Category
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Sub-categories</th>
                    <th>Products</th>
                    <th>Status</th>
                    @can('manage_products')
                    <th class="text-right">Actions</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                {{-- Parent row --}}
                <tr class="table-light">
                    <td>
                        <strong><i class="fas fa-folder mr-1 text-warning"></i>{{ $category->name }}</strong>
                    </td>
                    <td><code>{{ $category->slug }}</code></td>
                    <td><span class="badge badge-secondary">{{ $category->children->count() }}</span></td>
                    <td><span class="badge badge-info">{{ $category->products->count() }}</span></td>
                    <td>
                        @if($category->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    @can('manage_products')
                    <td class="text-right text-nowrap">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                    data-confirm="Delete category &quot;{{ $category->name }}&quot;?">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endcan
                </tr>
                {{-- Child rows --}}
                @foreach($category->children->load('products') as $child)
                <tr>
                    <td class="pl-5">
                        <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-1" style="font-size:.7rem;"></i>
                        <i class="far fa-folder mr-1 text-muted"></i>{{ $child->name }}
                    </td>
                    <td><code>{{ $child->slug }}</code></td>
                    <td>—</td>
                    <td><span class="badge badge-info">{{ $child->products->count() }}</span></td>
                    <td>
                        @if($child->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    @can('manage_products')
                    <td class="text-right text-nowrap">
                        <a href="{{ route('admin.categories.edit', $child) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.categories.destroy', $child) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                    data-confirm="Delete category &quot;{{ $child->name }}&quot;?">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endcan
                </tr>
                @endforeach
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">
                        No categories yet. <a href="{{ route('admin.categories.create') }}">Add one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
