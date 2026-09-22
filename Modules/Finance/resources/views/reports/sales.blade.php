@extends('auth::layouts.admin')

@section('title', 'Sales Report')
@section('page-title', 'Sales Report')

@section('breadcrumb')
    <li class="breadcrumb-item active">Sales Report</li>
@endsection

@section('content')

{{-- Period picker --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.finance.sales') }}" class="form-inline">
            <select name="month" class="form-control form-control-sm mr-2">
                @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                        {{ DateTime::createFromFormat('!m', $m)->format('F') }}
                    </option>
                @endforeach
            </select>
            <select name="year" class="form-control form-control-sm mr-2">
                @foreach(range(now()->year, now()->year - 4) as $y)
                    <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-sm btn-primary mr-2">
                <i class="fas fa-search mr-1"></i> View
            </button>
            <a href="{{ route('admin.finance.sales.csv', ['month' => $month, 'year' => $year]) }}"
               class="btn btn-sm btn-outline-success mr-1">
                <i class="fas fa-file-csv mr-1"></i> CSV
            </a>
            <a href="{{ route('admin.finance.sales.pdf', ['month' => $month, 'year' => $year]) }}"
               class="btn btn-sm btn-outline-danger">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
        </form>
    </div>
</div>

@php
    $monthName = DateTime::createFromFormat('!m', $month)->format('F');
    $totalRevenue = round($dailySales->sum('revenue'), 2);
    $totalTransactions = $dailySales->sum('transactions');
    $avgSale = $totalTransactions > 0 ? round($totalRevenue / $totalTransactions, 2) : 0;
@endphp

{{-- Summary cards --}}
<div class="row">
    <div class="col-md-4">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-dollar-sign"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">{{ $monthName }} Revenue</span>
                <span class="info-box-number">{{ $currencySymbol }}{{ number_format($totalRevenue, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Transactions</span>
                <span class="info-box-number">{{ $totalTransactions }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-calculator"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Avg Sale Value</span>
                <span class="info-box-number">{{ $currencySymbol }}{{ number_format($avgSale, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- Daily breakdown --}}
    <div class="col-md-8">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Daily Sales — {{ $monthName }} {{ $year }}
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Date</th>
                            <th class="text-center">Transactions</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dailySales as $day)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($day->sale_date)->format('d M Y') }}</td>
                            <td class="text-center">{{ $day->transactions }}</td>
                            <td class="text-right font-weight-bold">
                                {{ $currencySymbol }}{{ number_format($day->revenue, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-4">
                                No sales recorded for {{ $monthName }} {{ $year }}.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($dailySales->isNotEmpty())
                    <tfoot>
                        <tr class="table-light font-weight-bold">
                            <td>Total</td>
                            <td class="text-center">{{ $totalTransactions }}</td>
                            <td class="text-right text-success">
                                {{ $currencySymbol }}{{ number_format($totalRevenue, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    {{-- Top products + yearly bar --}}
    <div class="col-md-4">
        <div class="card card-outline card-secondary mb-3">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-trophy mr-2"></i>Top Products
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topProducts as $tp)
                        <tr>
                            <td style="font-size:.8rem;">{{ $tp->product->name ?? '—' }}</td>
                            <td class="text-center">{{ $tp->total_qty }}</td>
                            <td class="text-right font-weight-bold">
                                {{ $currencySymbol }}{{ number_format($tp->total_revenue, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted py-3">No data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-bar mr-2"></i>{{ $year }} Monthly Revenue
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        @foreach($monthlyRevenue as $m => $rev)
                        <tr class="{{ $m == $month ? 'table-primary' : '' }}">
                            <td style="font-size:.8rem;">
                                {{ DateTime::createFromFormat('!m', $m)->format('M') }}
                            </td>
                            <td>
                                @if($rev > 0)
                                <div class="progress" style="height:14px;">
                                    @php $max = max($monthlyRevenue) ?: 1; $pct = round(($rev/$max)*100); @endphp
                                    <div class="progress-bar bg-success" style="width:{{ $pct }}%"></div>
                                </div>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold" style="font-size:.8rem; white-space:nowrap;">
                                {{ $currencySymbol }}{{ number_format($rev, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
