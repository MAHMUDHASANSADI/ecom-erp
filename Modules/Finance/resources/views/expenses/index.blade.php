@extends('auth::layouts.admin')

@section('title', 'Expenses')
@section('page-title', 'Expenses')

@section('breadcrumb')
    <li class="breadcrumb-item active">Expenses</li>
@endsection

@section('content')

{{-- Filter bar --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.finance.expenses') }}" class="form-inline flex-wrap">
            <select name="category" class="form-control form-control-sm mr-2 mb-1" style="min-width:180px;">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->code }}" {{ ($filters['category'] ?? '') === $cat->code ? 'selected' : '' }}>
                        {{ $cat->label }}
                    </option>
                @endforeach
            </select>

            <div class="input-group input-group-sm mr-2 mb-1" style="max-width:160px;">
                <div class="input-group-prepend"><span class="input-group-text">From</span></div>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
            </div>

            <div class="input-group input-group-sm mr-2 mb-1" style="max-width:160px;">
                <div class="input-group-prepend"><span class="input-group-text">To</span></div>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
            </div>

            <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.finance.expenses') }}" class="btn btn-sm btn-secondary mb-1">
                <i class="fas fa-times mr-1"></i> Clear
            </a>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-receipt mr-2"></i>All Expenses
            <span class="badge badge-secondary ml-1">{{ $expenses->total() }}</span>
            @if($periodTotal !== null)
                <span class="badge badge-warning ml-2">Period total: ${{ number_format($periodTotal, 2) }}</span>
            @endif
        </h3>
        <div class="card-tools">
            @can('manage_expenses')
            <a href="{{ route('admin.finance.expenses.create') }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus mr-1"></i> Log Expense
            </a>
            @endcan
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Date</th>
                    <th>Category</th>
                    <th>Note</th>
                    <th class="text-right">Amount</th>
                    <th>Logged By</th>
                    @can('manage_expenses')
                    <th class="text-right">Actions</th>
                    @endcan
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td class="text-nowrap">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td><span class="badge badge-secondary">{{ $expense->category }}</span></td>
                    <td>{{ $expense->note ?: '—' }}</td>
                    <td class="text-right font-weight-bold text-danger">
                        ${{ number_format($expense->amount, 2) }}
                    </td>
                    <td>{{ $expense->creator->name ?? '—' }}</td>
                    @can('manage_expenses')
                    <td class="text-right text-nowrap">
                        <a href="{{ route('admin.finance.expenses.edit', $expense) }}" class="btn btn-sm btn-warning">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="{{ route('admin.finance.expenses.destroy', $expense) }}" class="d-inline">
                            @csrf @method('DELETE')
                            <button type="button" class="btn btn-sm btn-danger btn-delete"
                                    data-confirm="Delete this expense? This cannot be undone.">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No expenses found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())
    <div class="card-footer">
        {{ $expenses->appends($filters)->links() }}
    </div>
    @endif
</div>
@endsection
