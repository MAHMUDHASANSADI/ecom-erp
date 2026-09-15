{{-- Shared form fields for navigation create/edit --}}
@php $old = fn(string $key, $default = '') => old($key, $item?->$key ?? $default); @endphp

<div class="row">
    <div class="col-md-8">
        <div class="form-group">
            <label>Label <span class="text-danger">*</span></label>
            <input type="text" name="label" value="{{ $old('label') }}"
                   class="form-control @error('label') is-invalid @enderror"
                   placeholder="e.g. Catalog" required>
            @error('label')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Sort Order</label>
            <input type="number" name="sort_order" value="{{ $old('sort_order', 0) }}"
                   class="form-control" min="0">
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Route Name
                <small class="text-muted">(e.g. admin.products.index)</small>
            </label>
            <input type="text" name="route_name" value="{{ $old('route_name') }}"
                   class="form-control @error('route_name') is-invalid @enderror"
                   placeholder="Leave blank for parent groups">
            @error('route_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Icon
                <small class="text-muted">(<a href="https://fontawesome.com/icons" target="_blank">FA class</a>, e.g. fas fa-tags)</small>
            </label>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="iconPreview">
                        <i class="{{ $old('icon', 'fas fa-circle') }}"></i>
                    </span>
                </div>
                <input type="text" name="icon" id="iconInput" value="{{ $old('icon') }}"
                       class="form-control" placeholder="fas fa-tags">
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Parent Item
                <small class="text-muted">(leave blank to make top-level)</small>
            </label>
            <select name="parent_id" class="form-control">
                <option value="">— Top-level item —</option>
                @foreach($parents as $id => $label)
                    <option value="{{ $id }}"
                        {{ (string)($old('parent_id', $item?->parent_id)) === (string)$id ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label>Permission Required
                <small class="text-muted">(hide from users without this)</small>
            </label>
            <select name="permission_required" class="form-control">
                <option value="">— Visible to all authenticated users —</option>
                @foreach($permissions as $permName => $permLabel)
                    <option value="{{ $permName }}"
                        {{ $old('permission_required') === $permName ? 'selected' : '' }}>
                        {{ $permName }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Module
                <small class="text-muted">(optional, e.g. Catalog)</small>
            </label>
            <input type="text" name="module" value="{{ $old('module') }}"
                   class="form-control" placeholder="e.g. Catalog">
        </div>
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-group">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="is_active"
                       name="is_active" value="1"
                       {{ $old('is_active', '1') == '1' ? 'checked' : '' }}>
                <label class="custom-control-label" for="is_active">Visible in sidebar</label>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('iconInput').addEventListener('input', function () {
        document.querySelector('#iconPreview i').className = this.value || 'fas fa-circle';
    });
</script>
@endpush
