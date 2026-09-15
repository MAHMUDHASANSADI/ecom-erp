@extends('auth::layouts.admin')

@section('title', 'Edit Menu Item')
@section('page-title', 'Edit Menu Item')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.navigation.index') }}">Navigation</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-edit mr-2"></i>Edit: {{ $navigation->label }}</h3>
            </div>
            <form method="POST" action="{{ route('admin.navigation.update', $navigation) }}">
                @csrf @method('PUT')
                <div class="card-body">
                    @include('auth::navigation._form', ['item' => $navigation])
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.navigation.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
