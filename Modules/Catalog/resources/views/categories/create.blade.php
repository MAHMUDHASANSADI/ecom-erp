@extends('auth::layouts.admin')

@section('title', 'Add Category')
@section('page-title', 'Add Category')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>New Category</h3>
            </div>
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label>Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="e.g. Electronics" required autofocus>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <small class="text-muted">Slug is auto-generated from the name.</small>
                    </div>

                    <div class="form-group">
                        <label>Parent Category
                            <small class="text-muted">(leave blank for top-level)</small>
                        </label>
                        <select name="parent_category_id"
                                class="form-control @error('parent_category_id') is-invalid @enderror">
                            <option value="">— Top-level category —</option>
                            @foreach($parents as $id => $name)
                                <option value="{{ $id }}" {{ old('parent_category_id') == $id ? 'selected' : '' }}>
                                    {{ $name }}
                                </option>
                            @endforeach
                        </select>
                        @error('parent_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="form-group mb-0">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="is_active"
                                   name="is_active" value="1"
                                   {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Create Category
                    </button>
                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
