{{-- Shared form fields for expense create/edit --}}
@php $old = fn(string $key, $default = '') => old($key, $expense?->$key ?? $default); @endphp

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Category <span class="text-danger">*</span></label>
            <select name="category"
                    class="form-control @error('category') is-invalid @enderror" required>
                <option value="">— Select category —</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->code }}"
                            {{ $old('category') === $cat->code ? 'selected' : '' }}>
                        {{ $cat->label }}
                    </option>
                @endforeach
            </select>
            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Date <span class="text-danger">*</span></label>
            <input type="date" name="expense_date"
                   value="{{ $old('expense_date', now()->toDateString()) }}"
                   class="form-control @error('expense_date') is-invalid @enderror" required>
            @error('expense_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>

<div class="form-group">
    <label>Amount <span class="text-danger">*</span></label>
    <div class="input-group" style="max-width:220px;">
        <div class="input-group-prepend">
            <span class="input-group-text">$</span>
        </div>
        <input type="number" name="amount"
               value="{{ $old('amount') }}"
               class="form-control @error('amount') is-invalid @enderror"
               step="0.01" min="0.01" placeholder="0.00" required>
    </div>
    @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
</div>

<div class="form-group mb-0">
    <label>Note <small class="text-muted">(optional)</small></label>
    <textarea name="note" rows="2"
              class="form-control @error('note') is-invalid @enderror"
              placeholder="e.g. Monthly rent payment">{{ $old('note') }}</textarea>
    @error('note')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>
