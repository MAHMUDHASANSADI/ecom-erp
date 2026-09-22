@extends('auth::layouts.admin')

@section('title', 'Profit Report')
@section('page-title', 'Profit Report')

@section('breadcrumb')
    <li class="breadcrumb-item active">Profit Report</li>
@endsection

@section('content')

{{-- Period picker --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.finance.profit') }}" class="form-inline">
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
            <a href="{{ route('admin.finance.profit.pdf', ['month' => $month, 'year' => $year]) }}"
               class="btn btn-sm btn-outline-danger">
                <i class="fas fa-file-pdf mr-1"></i> PDF
            </a>
        </form>
    </div>
</div>

@php $monthName = DateTime::createFromFormat('!m', $month)->format('F'); @endphp

{{-- Headline cards --}}
<div class="row">
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-arrow-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Revenue</span>
                <span class="info-box-number">{{ $currencySymbol }}{{ number_format($revenue, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-warning">
            <span class="info-box-icon"><i class="fas fa-box"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Cost of Goods</span>
                <span class="info-box-number">{{ $currencySymbol }}{{ number_format($cogs, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-receipt"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Expenses</span>
                <span class="info-box-number">{{ $currencySymbol }}{{ number_format($totalExpenses, 2) }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box {{ $netProfit >= 0 ? 'bg-success' : 'bg-danger' }}">
            <span class="info-box-icon"><i class="fas fa-chart-line"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Net Profit</span>
                <span class="info-box-number">{{ $currencySymbol }}{{ number_format($netProfit, 2) }}</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    {{-- P&L breakdown --}}
    <div class="col-md-5">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-balance-scale mr-2"></i>
                    P&amp;L — {{ $monthName }} {{ $year }}
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <tbody>
                        <tr class="table-success">
                            <td>Revenue</td>
                            <td class="text-right font-weight-bold">
                                {{ $currencySymbol }}{{ number_format($revenue, 2) }}
                            </td>
                        </tr>
                        <tr class="table-warning">
                            <td>
                                Cost of Goods Sold
                                <small class="text-muted d-block">cost_price × qty for all sold items</small>
                            </td>
                            <td class="text-right font-weight-bold text-warning">
                                − {{ $currencySymbol }}{{ number_format($cogs, 2) }}
                            </td>
                        </tr>
                        <tr class="table-light">
                            <td><strong>Gross Profit</strong> <small class="text-muted">({{ $grossMarginPct }}% margin)</small></td>
                            <td class="text-right font-weight-bold {{ $grossProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $currencySymbol }}{{ number_format($grossProfit, 2) }}
                            </td>
                        </tr>
                        <tr class="table-danger">
                            <td>
                                Operating Expenses
                                <small class="text-muted d-block">logged manual expenses</small>
                            </td>
                            <td class="text-right font-weight-bold text-danger">
                                − {{ $currencySymbol }}{{ number_format($totalExpenses, 2) }}
                            </td>
                        </tr>
                        <tr class="{{ $netProfit >= 0 ? 'table-success' : 'table-danger' }}">
                            <td><strong>Net Profit</strong></td>
                            <td class="text-right h5 mb-0 font-weight-bold {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $currencySymbol }}{{ number_format($netProfit, 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Expense breakdown --}}
        @if(!empty($expenseByCategory))
        <div class="card card-outline card-secondary mt-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tags mr-2"></i>Expenses by Category</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    @foreach($expenseByCategory as $cat => $amt)
                    <tr>
                        <td><span class="badge badge-secondary">{{ $cat }}</span></td>
                        <td class="text-right font-weight-bold text-danger">
                            {{ $currencySymbol }}{{ number_format($amt, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </table>
            </div>
        </div>
        @endif
    </div>

    {{-- 12-month trend --}}
    <div class="col-md-7">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area mr-2"></i>{{ $year }} Monthly Trend
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Month</th>
                            <th class="text-right">Revenue</th>
                            <th class="text-right">Expenses</th>
                            <th class="text-right">Net</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $yearNetTotal = 0; $yearRevTotal = 0; @endphp
                        @foreach(range(1,12) as $m)
                        @php
                            $rev = $yearlyMonthlyRevenue[$m] ?? 0;
                            $exp = $yearlyMonthlyExpenses[$m] ?? 0;
                            $net = $rev - $exp;
                            $yearNetTotal += $net;
                            $yearRevTotal += $rev;
                        @endphp
                        <tr class="{{ $m == $month ? 'table-primary font-weight-bold' : '' }}">
                            <td>{{ DateTime::createFromFormat('!m', $m)->format('M') }}</td>
                            <td class="text-right">{{ $currencySymbol }}{{ number_format($rev, 2) }}</td>
                            <td class="text-right text-danger">{{ $currencySymbol }}{{ number_format($exp, 2) }}</td>
                            <td class="text-right {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $currencySymbol }}{{ number_format($net, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light font-weight-bold">
                            <td>YTD</td>
                            <td class="text-right text-success">{{ $currencySymbol }}{{ number_format($yearRevTotal, 2) }}</td>
                            <td class="text-right text-danger">
                                {{ $currencySymbol }}{{ number_format(array_sum($yearlyMonthlyExpenses), 2) }}
                            </td>
                            <td class="text-right {{ $yearNetTotal >= 0 ? 'text-success' : 'text-danger' }}">
                                {{ $currencySymbol }}{{ number_format($yearNetTotal, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
