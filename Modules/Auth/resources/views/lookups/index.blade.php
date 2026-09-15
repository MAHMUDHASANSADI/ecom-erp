@extends('auth::layouts.admin')

@section('title', 'Lookup Values')
@section('page-title', 'Lookup Values')

@section('breadcrumb')
    <li class="breadcrumb-item active">Lookups</li>
@endsection

@section('content')
<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title"><i class="fas fa-list-alt mr-2"></i>Reference Data</h3>
        <div class="card-tools d-flex align-items-center">
            {{-- Type filter --}}
            <form method="GET" action="{{ route('admin.lookups.index') }}" class="mr-3">
                <div class="input-group input-group-sm">
                    <select name="type" class="form-control form-control-sm" onchange="this.form.submit()">
                        <option value="">All types</option>
                        @foreach($types as $t)
                            <option value="{{ $t }}" {{ $type == $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
            <a href="{{ route('admin.lookups.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Add Value
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Type</th>
                    <th>Code</th>
                    <th>Label</th>
                    <th>Sort</th>
                    <th>Status</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lookups as $lookup)
                <tr>
                    <td><span class="badge badge-secondary">{{ $lookup->type }}</span></td>
                    <td><code>{{ $lookup->code }}</code></td>
                    <td>{{ $lookup->label }}</td>
                    <td>{{ $lookup->sort_order }}</td>
                    <td>
                        @if($lookup->is_active)
                            <span class="badge badge-success">Active</span>
                        @else
                            <span class="badge badge-secondary">Inactive</span>
                        @endif
                    </td>
                    <td class="text-right">
                        <a href="{{ route('admin.lookups.edit', $lookup) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.lookups.destroy', $lookup) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                    data-confirm="This will deactivate this lookup value.">
                                <i class="fas fa-ban"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No lookup values found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($lookups->hasPages())
    <div class="card-footer">
        {{ $lookups->links() }}
    </div>
    @endif
</div>
@endsection
