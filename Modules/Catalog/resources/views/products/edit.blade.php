@extends('auth::layouts.admin')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit: {{ $product->name }}</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Product Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $product->name) }}"
                               class="form-control @error('name') is-invalid @enderror" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>SKU <span class="text-danger">*</span></label>
                                <input type="text" name="sku" value="{{ old('sku', $product->sku) }}"
                                       class="form-control @error('sku') is-invalid @enderror" required>
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
                                        <option value="{{ $id }}"
                                            {{ old('category_id', $product->category_id) == $id ? 'selected' : '' }}>
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
                                    <input type="number" name="price" value="{{ old('price', $product->price) }}"
                                           class="form-control @error('price') is-invalid @enderror"
                                           step="0.01" min="0" required>
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
                                    <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}"
                                           class="form-control @error('cost_price') is-invalid @enderror"
                                           step="0.01" min="0" required>
                                </div>
                                @error('cost_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Tax Class</label>
                                <input type="text" name="tax_class" value="{{ old('tax_class', $product->tax_class) }}"
                                       class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Description</label>
                        <textarea name="description" rows="4"
                                  class="form-control">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Image</h3></div>
                <div class="card-body">
                    @if($product->image_url)
                    <div class="text-center mb-3" id="imagePreviewWrap">
                        <img id="imagePreview" src="{{ $product->image_url }}" alt="{{ $product->name }}"
                             class="img-fluid img-thumbnail" style="max-height:180px;">
                    </div>
                    @else
                    <div id="imagePreviewWrap" class="text-center mb-3" style="display:none!important;">
                        <img id="imagePreview" src="" alt="Preview"
                             class="img-fluid img-thumbnail" style="max-height:180px;">
                    </div>
                    @endif
                    <div class="custom-file">
                        <input type="file" class="custom-file-input @error('image') is-invalid @enderror"
                               id="imageInput" name="image" accept="image/*">
                        <label class="custom-file-label" for="imageInput">
                            {{ $product->image_path ? 'Replace image…' : 'Choose image…' }}
                        </label>
                    </div>
                    @error('image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <small class="text-muted">JPG, PNG, WebP — max 2MB</small>
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header"><h3 class="card-title">Status</h3></div>
                <div class="card-body">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active"
                               name="is_active" value="1"
                               {{ old('is_active', $product->is_active ? '1' : '0') == '1' ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">
                            Active (visible in storefront)
                        </label>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning btn-block">
                        <i class="fas fa-save mr-1"></i> Save Changes
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
