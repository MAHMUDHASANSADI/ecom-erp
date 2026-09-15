@extends('auth::layouts.admin')

@section('title', 'Add Menu Item')
@section('page-title', 'Add Menu Item')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.navigation.index') }}">Navigation</a></li>
    <li class="breadcrumb-item active">Add</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>New Menu Item</h3>
            </div>
            <form method="POST" action="{{ route('admin.navigation.store') }}">
                @csrf
                <div class="card-body">
                    @include('auth::navigation._form', ['item' => null])
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Create Item
                    </button>
                    <a href="{{ route('admin.navigation.index') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
