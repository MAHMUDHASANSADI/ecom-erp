@extends('auth::layouts.admin')

@section('title', 'Add Product')
@section('page-title', 'Add Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row">
        {{-- Main details --}}
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-box mr-2"></i>Product Details</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="e.g. iPhone 15 Pro" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" value="{{ old('sku') }}"
                                       class="form-control @error('sku') is-invalid @enderror"
                                       placeholder="e.g. IPH-15-PRO" required>
                                @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category_id"
                                        class="form-control @error('category_id') is-invalid @enderror" required>
                                    <option value="">— Select category —</option>
                                    @foreach($categories as $id => $name)
                                        <option value="{{ $id }}" {{ old('category_id') == $id ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Selling Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="price" value="{{ old('price') }}"
                                           class="form-control @error('price') is-invalid @enderror"
                                           step="0.01" min="0" placeholder="0.00" required>
                                </div>
                                @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Cost Price <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">$</span>
                                    </div>
                                    <input type="number" name="cost_price" value="{{ old('cost_price', 0) }}"
                                           class="form-control @error('cost_price') is-invalid @enderror"
                                           step="0.01" min="0" placeholder="0.00" required>
                                </div>
                                @error('cost_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tax Class</label>
                                <input type="text" name="tax_class" value="{{ old('tax_class') }}"
                                       class="form-control" placeholder="e.g. standard">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Product description…">{{ old('description') }}</textarea>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar: image + status --}}
        <div class="col-md-4">
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Image</h3></div>
                <div class="card-body">
                    <div id="imagePreviewWrap" class="text-center mb-3" style="display:none!important;">
                        <img id="imagePreview" src="" alt="Preview"
                             class="img-fluid img-thumbnail" style="max-height:180px;">
                    </div>
                    <div class="form-group mb-0">
                        <div class="custom-file">
                            <input type="file" class="custom-file-input @error('image') is-invalid @enderror"
                                   id="imageInput" name="image" accept="image/*">
                            <label class="custom-file-label" for="imageInput">Choose image…</label>
                        </div>
                        @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        <small class="text-muted">JPG, PNG, WebP — max 2MB</small>
                    </div>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Status</h3></div>
                <div class="card-body">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active"
                               name="is_active" value="1"
                               {{ old('is_active', '1') == '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">
                            Active (visible in storefront)
                        </label>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-save mr-1"></i> Create Product
                    </button>
                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-block mt-1">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    document.getElementById('imageInput').addEventListener('change', function () {
        const file = this.files[0];
        if (!file) { return; }
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('imagePreview').src = e.target.result;
            document.getElementById('imagePreviewWrap').style.removeProperty('display');
        };
        reader.readAsDataURL(file);
        this.nextElementSibling.textContent = file.name;
    });
</script>
@endpush
