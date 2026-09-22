@extends('auth::layouts.admin')

@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.finance.expenses') }}">Expenses</a></li>
    <li class="breadcrumb-item active">Edit</li>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-edit mr-2"></i>
                    Edit: {{ $expense->category }} — ${{ number_format($expense->amount, 2) }}
                </h3>
            </div>
            <form method="POST" action="{{ route('admin.finance.expenses.update', $expense) }}">
                @csrf @method('PUT')
                <div class="card-body">
                    @include('finance::expenses._form', ['expense' => $expense])
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save mr-1"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.finance.expenses') }}" class="btn btn-secondary ml-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
