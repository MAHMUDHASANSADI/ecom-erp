@extends('auth::layouts.admin')

@section('title', 'Navigation Menu')
@section('page-title', 'Navigation Menu')

@section('breadcrumb')
    <li class="breadcrumb-item active">Navigation</li>
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-bars mr-2"></i>Menu Structure</h3>
        <div class="card-tools">
            <a href="{{ route('admin.navigation.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add Item
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Label</th>
                    <th>Route</th>
                    <th>Permission</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                {{-- Parent row --}}
                <tr class="table-light">
                    <td>
                        <i class="{{ $item->icon ?? 'fas fa-circle' }} mr-1 text-muted"></i>
                        <strong>{{ $item->label }}</strong>
                        @if($item->children->count())
                            <span class="badge badge-secondary ml-1">{{ $item->children->count() }} sub-items</span>
                        @endif
                    </td>
                    <td>{{ $item->route_name ? '<code>'.$item->route_name.'</code>' : '<span class="text-muted">—</span>' }}</td>
                    <td>{{ $item->permission_required ? '<code>'.$item->permission_required.'</code>' : '<span class="text-muted">none</span>' }}</td>
                    <td>{{ $item->sort_order }}</td>
                    <td>
                        @if($item->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Hidden</span>
                        @endif
                    </td>
                    <td class="text-right text-nowrap">
                        <a href="{{ route('admin.navigation.edit', $item) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.navigation.destroy', $item) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                    data-confirm="Delete &quot;{{ $item->label }}&quot;? Sub-items will become top-level.">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                {{-- Child rows --}}
                @foreach($item->children as $child)
                <tr>
                    <td class="pl-5">
                        <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-1" style="font-size:.7rem;"></i>
                        <i class="{{ $child->icon ?? 'far fa-circle' }} mr-1 text-muted"></i>
                        {{ $child->label }}
                    </td>
                    <td>{{ $child->route_name ? '<code>'.$child->route_name.'</code>' : '<span class="text-muted">—</span>' }}</td>
                    <td>{{ $child->permission_required ? '<code>'.$child->permission_required.'</code>' : '<span class="text-muted">none</span>' }}</td>
                    <td>{{ $child->sort_order }}</td>
                    <td>
                        @if($child->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Hidden</span>
                        @endif
                    </td>
                    <td class="text-right text-nowrap">
                        <a href="{{ route('admin.navigation.edit', $child) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.navigation.destroy', $child) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                    data-confirm="Delete &quot;{{ $child->label }}&quot;?">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No navigation items found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
