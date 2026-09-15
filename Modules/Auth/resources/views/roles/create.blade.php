@extends('auth::layouts.admin')

@section('title', 'Add Role')
@section('page-title', 'Add Role')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.roles.index') }}">Roles</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>New Role</h3>
            </div>
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Role Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="e.g. Supervisor" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <hr>
                    <h6 class="mb-3"><i class="fas fa-key mr-1"></i> Assign Permissions</h6>

                    <div class="row">
                        @foreach($permissions as $group => $groupPerms)
                        <div class="col-md-4 mb-3">
                            <div class="card card-body p-2 bg-light">
                                <p class="text-capitalize font-weight-bold mb-2" style="font-size:.8rem;">
                                    <i class="fas fa-layer-group mr-1 text-muted"></i>
                                    {{ str_replace('_', ' ', $group) }}
                                </p>
                                @foreach($groupPerms as $perm)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox"
                                           class="custom-control-input"
                                           id="perm_{{ $perm->id }}"
                                           name="permissions[]"
                                           value="{{ $perm->name }}"
                                           {{ in_array($perm->name, old('permissions', [])) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="perm_{{ $perm->id }}" style="font-size:.85rem;">
                                        {{ $perm->name }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Create Role
                        </button>
                        <a href="{{ route('admin.roles.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                    </div>
                    <button type="button" class="btn btn-link btn-sm text-muted" id="selectAll">
                        Select all permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.getElementById('selectAll').addEventListener('click', function () {
        const boxes = document.querySelectorAll('input[name="permissions[]"]');
        const allChecked = [...boxes].every(b => b.checked);
        boxes.forEach(b => b.checked = !allChecked);
        this.textContent = allChecked ? 'Select all permissions' : 'Deselect all permissions';
    });
</script>
@endpush
