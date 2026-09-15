@extends('auth::layouts.admin')

@section('title', 'Settings')
@section('page-title', 'General Settings')

@section('breadcrumb')
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}">
    @csrf

    @if($settings->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-2"></i>
            No settings configured yet. Settings will appear here once seeded.
        </div>
    @else
        @foreach($settings as $group => $groupSettings)
        <div class="card card-outline card-primary mb-4">
            <div class="card-header">
                <h3 class="card-title text-capitalize">
                    <i class="fas fa-sliders-h mr-2"></i>{{ ucfirst($group) }} Settings
                </h3>
            </div>
            <div class="card-body">
                @foreach($groupSettings as $index => $setting)
                <div class="form-group row">
                    <label class="col-md-3 col-form-label text-capitalize">
                        {{ ucwords(str_replace('_', ' ', $setting['key'])) }}
                    </label>
                    <div class="col-md-6">
                        <input type="hidden" name="settings[{{ $setting['key'] }}][key]" value="{{ $setting['key'] }}">
                        <input type="text"
                               name="settings[{{ $setting['key'] }}][value]"
                               value="{{ old('settings.'.$setting['key'].'.value', $setting['value']) }}"
                               class="form-control"
                               placeholder="{{ $setting['key'] }}">
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Save Settings
        </button>
    @endif
</form>
@endsection
