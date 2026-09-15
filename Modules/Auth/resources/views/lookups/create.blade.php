@extends('auth::layouts.admin')

@section('title', 'Add Lookup Value')
@section('page-title', 'Add Lookup Value')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.lookups.index') }}">Lookups</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>New Lookup Value</h3>
            </div>
            <form method="POST" action="{{ route('admin.lookups.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Type <span class="text-danger">*</span>
                            <small class="text-muted">(e.g. payment_method, order_status)</small>
                        </label>
                        <input type="text" name="type" value="{{ old('type') }}"
                               class="form-control @error('type') is-invalid @enderror"
                               list="existing-types" placeholder="e.g. order_status" required>
                        <datalist id="existing-types">
                            @foreach($types as $t)
                                <option value="{{ $t }}">
                            @endforeach
                        </datalist>
                        @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Code <span class="text-danger">*</span>
                            <small class="text-muted">(machine-readable, e.g. pending)</small>
                        </label>
                        <input type="text" name="code" value="{{ old('code') }}"
                               class="form-control @error('code') is-invalid @enderror"
                               placeholder="e.g. pending" required>
                        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Label <span class="text-danger">*</span>
                            <small class="text-muted">(displayed in UI, e.g. Pending)</small>
                        </label>
                        <input type="text" name="label" value="{{ old('label') }}"
                               class="form-control @error('label') is-invalid @enderror"
                               placeholder="e.g. Pending" required>
                        @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group">
                        <label>Sort Order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                               class="form-control" min="0" style="max-width:120px;">
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active"
                                   name="is_active" value="1" {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Create
                    </button>
                    <a href="{{ route('admin.lookups.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
