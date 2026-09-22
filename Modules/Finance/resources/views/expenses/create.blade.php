@extends('auth::layouts.admin')

@section('title', 'Log Expense')
@section('page-title', 'Log Expense')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.expenses') }}">Expenses</a></li>
    <li class="breadcrumb-item active">Log</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>New Expense</h3>
            </div>
            <form method="POST" action="{{ route('admin.finance.expenses.store') }}">
                @csrf
                <div class="card-body">
                    @include('finance::expenses._form', ['expense' => null])
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-1"></i> Log Expense
                    </button>
                    <a href="{{ route('admin.finance.expenses') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
