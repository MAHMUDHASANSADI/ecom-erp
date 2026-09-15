@extends('auth::layouts.admin')

@section('title', 'Edit Lookup Value')
@section('page-title', 'Edit Lookup Value')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.lookups.index') }}">Lookups</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-2"></i>
                    Edit: <code>{{ $lookup->type }}.{{ $lookup->code }}</code>
                </h3>
            </div>
            <form method="POST" action="{{ route('admin.lookups.update', $lookup) }}">
                @csrf @method('PUT')
                <div class="card-body">
                    <div class="form-group">
                        <label>Type <span class="text-danger">*</span></label>
                        <input type="text" name="type" value="{{ old('type', $lookup->type) }}"
                               class="form-control @error('type') is-invalid @enderror"
                               list="existing-types" required>
                        <datalist id="existing-types">
                            @foreach($types as $t)
                                <option value="{{ $t }}">
                            @endforeach
                        </datalist>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Code <span class="text-danger">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $lookup->code) }}"
                               class="form-control @error('code') is-invalid @enderror" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Label <span class="text-danger">*</span></label>
                        <input type="text" name="label" value="{{ old('label', $lookup->label) }}"
                               class="form-control @error('label') is-invalid @enderror" required>
                        @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $lookup->sort_order) }}"
                               class="form-control" min="0" style="max-width:120px;">
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active"
                                   name="is_active" value="1"
                                   {{ old('is_active', $lookup->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.lookups.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
