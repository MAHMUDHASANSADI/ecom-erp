@extends('auth::layouts.admin')

@section('title', 'Permissions')
@section('page-title', 'Permission Management')

@section('breadcrumb')
    <li class="breadcrumb-item active">Permissions</li>
@endsection

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-key mr-2"></i>All Permissions</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.permissions.create') }}" class="btn btn-primary btn-sm">
                        <i class="fas fa-plus mr-1"></i> Add Permission
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @forelse($permissions as $group => $groupPerms)
                <div class="p-3 border-bottom">
                    <p class="text-uppercase text-muted mb-2" style="font-size:.75rem; letter-spacing:.08em;">
                        <i class="fas fa-layer-group mr-1"></i>
                        {{ str_replace('_', ' ', $group) }}
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($groupPerms as $perm)
                        <div class="d-flex align-items-center mb-1 mr-2">
                            <span class="badge badge-secondary px-2 py-1 mr-1" style="font-size:.8rem;">
                                {{ $perm->name }}
                                <span class="badge badge-light ml-1">{{ $perm->roles_count }} role(s)</span>
                            </span>
                            <form method="POST" action="{{ route('admin.permissions.destroy', $perm) }}" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="button"
                                        class="btn btn-sm btn-outline-danger btn-delete py-0 px-1"
                                        style="font-size:.7rem; line-height:1.4;"
                                        data-confirm="Delete permission &quot;{{ $perm->name }}&quot;? It will be removed from all roles.">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <div class="p-4 text-center text-muted">No permissions found.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>Add Permission</h3>
            </div>
            <form method="POST" action="{{ route('admin.permissions.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group mb-1">
                        <label>Permission Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="e.g. manage_suppliers">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Lowercase, underscores only. Convention: <code>action_resource</code></small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-save mr-1"></i> Create Permission
                    </button>
                </div>
            </form>
        </div>

        <div class="callout callout-warning">
            <h6><i class="fas fa-exclamation-triangle mr-1"></i> Note</h6>
            <p class="mb-0" style="font-size:.85rem;">
                Deleting a permission removes it from all roles immediately.
                Make sure no code checks for it before deleting.
            </p>
        </div>
    </div>
</div>
@endsection
