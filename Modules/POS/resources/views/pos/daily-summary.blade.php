@extends('auth::layouts.admin')

@section('title', 'Daily Summary')
@section('page-title', 'Daily Sales Summary')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pos.index') }}">POS</a></li>
    <li class="breadcrumb-item active">Daily Summary</li>
@endsection

@section('content')

{{-- Date picker --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.pos.daily-summary') }}" class="form-inline">
            <label class="mr-2">Date:</label>
            <div class="input-group input-group-sm mr-2">
                <input type="date" name="date" value="{{ $date }}" class="form-control">
                <div class="input-group-append">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <a href="{{ route('admin.pos.daily-summary', ['date' => today()->toDateString()]) }}"
               class="btn btn-sm btn-outline-secondary">Today</a>
        </form>
    </div>
</div>

{{-- Summary stats --}}
<div class="row">
    <div class="col-md-4">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Revenue</span>
                <span class="info-box-number">
                    {{ $currencySymbol }}{{ number_format($summary['total_revenue'], 2) }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Transactions</span>
                <span class="info-box-number">{{ $summary['total_transactions'] }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Average Sale Value</span>
                <span class="info-box-number">
                    @if($summary['total_transactions'] > 0)
                        {{ $currencySymbol }}{{ number_format($summary['total_revenue'] / $summary['total_transactions'], 2) }}
                    @else
                        {{ $currencySymbol }}0.00
                    @endif
                </span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Payment breakdown --}}
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-credit-card mr-2"></i>By Payment Method</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Method</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary['by_payment_method'] as $method => $amount)
                        <tr>
                            <td class="text-capitalize">{{ $method }}</td>
                            <td class="text-right font-weight-bold">
                                {{ $currencySymbol }}{{ number_format($amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-3">No sales for this date.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Transaction list --}}
    <div class="col-md-8">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-2"></i>Transactions
                    <span class="badge badge-secondary ml-1">{{ $recentSales->total() }}</span>
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.pos.index') }}" class="btn btn-success btn-sm">
                        <i class="fas fa-plus mr-1"></i> New Sale
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Sale #</th>
                            <th>Time</th>
                            <th>Cashier</th>
                            <th>Payment</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Receipt</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentSales as $sale)
                        <tr>
                            <td><strong>{{ $sale->sale_number }}</strong></td>
                            <td>{{ $sale->created_at->format('H:i') }}</td>
                            <td>{{ $sale->cashier->name ?? '—' }}</td>
                            <td>
                                <span class="badge badge-secondary text-capitalize">
                                    {{ $sale->payment_method }}
                                </span>
                            </td>
                            <td class="text-right font-weight-bold text-success">
                                {{ $currencySymbol }}{{ number_format($sale->total, 2) }}
                            </td>
                            <td class="text-right">
                                <a href="{{ route('admin.pos.sales.receipt', $sale) }}"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-receipt"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No transactions for this date.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($recentSales->hasPages())
            <div class="card-footer">
                {{ $recentSales->appends(['date' => $date])->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
