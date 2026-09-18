@extends('auth::layouts.admin')

@section('title', 'Adjust Stock')
@section('page-title', 'Adjust Stock')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
    <li class="breadcrumb-item active">Adjust Stock</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Manual Stock Adjustment</h3>
            </div>
            <form method="POST" action="{{ route('admin.inventory.store-adjustment') }}">
                @csrf
                <div class="card-body">

                    {{-- Product --}}
                    <div class="form-group">
                        <label>Product <span class="text-danger">*</span></label>
                        <select name="product_id" id="productSelect"
                                class="form-control @error('product_id') is-invalid @enderror" required>
                            <option value="">— Select a product —</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}"
                                        data-stock="{{ $product->current_stock }}"
                                        {{ (old('product_id', $selectedProductId) == $product->id) ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->sku }})
                                    — {{ $product->current_stock }} in stock
                                </option>
                            @endforeach
                        </select>
                        @error('product_id')<div class="invalid-feedback">{{ $message }}</div>@enderror

                        {{-- Current stock display --}}
                        <div id="currentStockDisplay" class="mt-2" style="display:none;">
                            <span class="text-muted">Current stock:</span>
                            <strong id="currentStockValue" class="ml-1"></strong>
                        </div>
                    </div>

                    {{-- Type --}}
                    <div class="form-group">
                        <label>Adjustment Type <span class="text-danger">*</span></label>
                        <div class="d-flex">
                            <div class="custom-control custom-radio mr-4">
                                <input type="radio" id="typeAdd" name="type" value="add"
                                       class="custom-control-input"
                                       {{ old('type', 'add') === 'add' ? 'checked' : '' }}>
                                <label class="custom-control-label text-success font-weight-bold" for="typeAdd">
                                    <i class="fas fa-plus-circle mr-1"></i> Add Stock
                                </label>
                            </div>
                            <div class="custom-control custom-radio">
                                <input type="radio" id="typeRemove" name="type" value="remove"
                                       class="custom-control-input"
                                       {{ old('type') === 'remove' ? 'checked' : '' }}>
                                <label class="custom-control-label text-danger font-weight-bold" for="typeRemove">
                                    <i class="fas fa-minus-circle mr-1"></i> Remove Stock
                                </label>
                            </div>
                        </div>
                        @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    {{-- Quantity --}}
                    <div class="form-group">
                        <label>Quantity <span class="text-danger">*</span></label>
                        <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                               class="form-control @error('quantity') is-invalid @enderror"
                               min="1" style="max-width:180px;" required>
                        @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Reason --}}
                    <div class="form-group">
                        <label>Reason <span class="text-danger">*</span></label>
                        <select name="reason" class="form-control @error('reason') is-invalid @enderror" required>
                            <option value="">— Select reason —</option>
                            @foreach($reasons as $reason)
                                <option value="{{ $reason->code }}"
                                        {{ old('reason') === $reason->code ? 'selected' : '' }}>
                                    {{ $reason->label }}
                                </option>
                            @endforeach
                        </select>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    {{-- Notes --}}
                    <div class="form-group mb-0">
                        <label>Notes <small class="text-muted">(optional)</small></label>
                        <textarea name="notes" rows="2"
                                  class="form-control @error('notes') is-invalid @enderror"
                                  placeholder="e.g. Supplier delivery, damaged during transit…">{{ old('notes') }}</textarea>
                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save mr-1"></i> Save Adjustment
                    </button>
                    <a href="{{ route('admin.inventory.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const select = document.getElementById('productSelect');
    const display = document.getElementById('currentStockDisplay');
    const value = document.getElementById('currentStockValue');

    function updateStockDisplay() {
        const opt = select.options[select.selectedIndex];
        const stock = opt ? opt.dataset.stock : null;
        if (stock !== undefined && stock !== null && select.value) {
            value.textContent = stock + ' units';
            value.className = parseInt(stock) <= 5 ? 'ml-1 text-warning font-weight-bold' : 'ml-1 text-success font-weight-bold';
            display.style.display = 'block';
        } else {
            display.style.display = 'none';
        }
    }

    select.addEventListener('change', updateStockDisplay);
    // Run on load if a product is pre-selected
    if (select.value) { updateStockDisplay(); }
</script>
@endpush
