@extends('auth::layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')

{{-- =====================================================================
     ROW 1 — Stat cards
     ===================================================================== --}}
<div class="row">

    @can('operate_pos')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $todaySalesCount ?? 0 }}</h3>
                <p>Today's Transactions</p>
            </div>
            <div class="icon"><i class="fas fa-cash-register"></i></div>
            <a href="{{ route('admin.pos.daily-summary') }}" class="small-box-footer">
                View summary <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endcan

    @can('view_finance_reports')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $currencySymbol }}{{ number_format($todayRevenue ?? 0, 2) }}</h3>
                <p>Today's Revenue</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            <a href="{{ route('admin.finance.sales') }}" class="small-box-footer">
                Sales report <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endcan

    @can('view_inventory')
    <div class="col-lg-3 col-6">
        <div class="small-box {{ ($lowStockCount ?? 0) > 0 ? 'bg-warning' : 'bg-secondary' }}">
            <div class="inner">
                <h3>{{ $lowStockCount ?? 0 }}</h3>
                <p>Low Stock Alerts</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="{{ route('admin.inventory.index', ['low_stock' => 1]) }}" class="small-box-footer">
                View inventory <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endcan

    @can('view_orders')
    <div class="col-lg-3 col-6">
        <div class="small-box {{ ($pendingOrdersCount ?? 0) > 0 ? 'bg-danger' : 'bg-secondary' }}">
            <div class="inner">
                <h3>{{ $pendingOrdersCount ?? 0 }}</h3>
                <p>Pending Orders</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}" class="small-box-footer">
                View orders <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endcan

    @can('view_products')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $totalProducts ?? 0 }}</h3>
                <p>Active Products</p>
            </div>
            <div class="icon"><i class="fas fa-box-open"></i></div>
            <a href="{{ route('admin.products.index') }}" class="small-box-footer">
                View catalog <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    @endcan

</div>

{{-- =====================================================================
     ROW 2 — Monthly revenue chart + Quick actions
     ===================================================================== --}}
<div class="row">

    @can('view_finance_reports')
    @if($monthlyChartData)
    <div class="col-md-8">
        <div class="card card-outline card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area mr-2"></i>Revenue — Last 6 Months
                </h3>
                <div class="card-tools">
                    <a href="{{ route('admin.finance.sales') }}" class="btn btn-sm btn-outline-success">
                        Full report
                    </a>
                </div>
            </div>
            <div class="card-body">
                <canvas id="revenueChart" height="100"></canvas>
            </div>
            @if(isset($thisMonthRevenue) && isset($thisMonthExpenses))
            <div class="card-footer d-flex justify-content-around text-center">
                <div>
                    <span class="text-muted" style="font-size:.8rem;">This Month Revenue</span>
                    <div class="font-weight-bold text-success">
                        {{ $currencySymbol }}{{ number_format($thisMonthRevenue, 2) }}
                    </div>
                </div>
                <div>
                    <span class="text-muted" style="font-size:.8rem;">This Month Expenses</span>
                    <div class="font-weight-bold text-danger">
                        {{ $currencySymbol }}{{ number_format($thisMonthExpenses, 2) }}
                    </div>
                </div>
                <div>
                    <span class="text-muted" style="font-size:.8rem;">Estimated Net</span>
                    @php $net = $thisMonthRevenue - $thisMonthExpenses; @endphp
                    <div class="font-weight-bold {{ $net >= 0 ? 'text-success' : 'text-danger' }}">
                        {{ $currencySymbol }}{{ number_format($net, 2) }}
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif
    @endcan

    {{-- Quick actions --}}
    <div class="@can('view_finance_reports') col-md-4 @else col-md-6 @endcan">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt mr-2"></i>Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @can('operate_pos')
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.pos.index') }}" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-cash-register fa-lg d-block mb-1"></i>
                            Open POS
                        </a>
                    </div>
                    @endcan
                    @can('manage_products')
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.products.create') }}" class="btn btn-block btn-outline-success">
                            <i class="fas fa-plus-circle fa-lg d-block mb-1"></i>
                            Add Product
                        </a>
                    </div>
                    @endcan
                    @can('manage_inventory')
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.inventory.adjust') }}" class="btn btn-block btn-outline-warning">
                            <i class="fas fa-boxes fa-lg d-block mb-1"></i>
                            Adjust Stock
                        </a>
                    </div>
                    @endcan
                    @can('manage_expenses')
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.finance.expenses.create') }}" class="btn btn-block btn-outline-danger">
                            <i class="fas fa-receipt fa-lg d-block mb-1"></i>
                            Log Expense
                        </a>
                    </div>
                    @endcan
                    @can('view_finance_reports')
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.finance.profit') }}" class="btn btn-block btn-outline-info">
                            <i class="fas fa-chart-line fa-lg d-block mb-1"></i>
                            Profit Report
                        </a>
                    </div>
                    @endcan
                    @can('view_orders')
                    <div class="col-6 mb-3">
                        <a href="{{ route('admin.orders.index') }}" class="btn btn-block btn-outline-secondary">
                            <i class="fas fa-shopping-bag fa-lg d-block mb-1"></i>
                            View Orders
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

</div>

{{-- =====================================================================
     ROW 3 — Recent sales + Low stock + Pending orders
     ===================================================================== --}}
<div class="row">

    {{-- Recent Sales --}}
    @can('view_finance_reports')
    @if($recentSales && $recentSales->isNotEmpty())
    <div class="col-md-6">
        <div class="card card-outline card-info">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-history mr-2"></i>Recent Sales</h3>
                <div class="card-tools">
                    <a href="{{ route('admin.finance.sales') }}" class="btn btn-sm btn-outline-info">
                        All sales
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Sale #</th>
                            <th>Time</th>
                            <th>Payment</th>
                            <th class="text-right">Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentSales as $sale)
                        <tr>
                            <td><strong>{{ $sale->sale_number }}</strong></td>
                            <td>
                                <span title="{{ $sale->created_at->format('d M Y H:i') }}">
                                    {{ $sale->created_at->diffForHumans() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-secondary text-capitalize">
                                    {{ $sale->payment_method }}
                                </span>
                            </td>
                            <td class="text-right font-weight-bold text-success">
                                {{ $currencySymbol }}{{ number_format($sale->total, 2) }}
                            </td>
                            <td>
                                <a href="{{ route('admin.pos.sales.receipt', $sale) }}"
                                   class="btn btn-xs btn-outline-secondary p-0 px-1">
                                    <i class="fas fa-receipt"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endcan

    {{-- Low Stock + Pending Orders side by side --}}
    <div class="@can('view_finance_reports') col-md-6 @else col-md-12 @endcan">
        <div class="row">

            {{-- Low stock products --}}
            @can('view_inventory')
            @if($lowStockProducts && $lowStockProducts->isNotEmpty())
            <div class="col-12 mb-3">
                <div class="card card-outline card-warning">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>
                            Low Stock
                            <span class="badge badge-warning ml-1">≤ {{ $threshold }} units</span>
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.inventory.index', ['low_stock' => 1]) }}"
                               class="btn btn-sm btn-outline-warning">
                                View all
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach($lowStockProducts as $p)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.inventory.product-history', $p) }}"
                                           class="text-dark font-weight-bold">
                                            {{ $p->name }}
                                        </a>
                                        <small class="text-muted d-block">{{ $p->sku }}</small>
                                    </td>
                                    <td class="text-right">
                                        <span class="badge badge-{{ $p->current_stock <= 0 ? 'danger' : 'warning' }} px-2">
                                            {{ $p->current_stock }} left
                                        </span>
                                    </td>
                                    @can('manage_inventory')
                                    <td class="text-right" style="width:80px;">
                                        <a href="{{ route('admin.inventory.adjust', ['product_id' => $p->id]) }}"
                                           class="btn btn-xs btn-success p-0 px-2" style="font-size:.75rem;">
                                            Restock
                                        </a>
                                    </td>
                                    @endcan
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @endcan

            {{-- Pending orders --}}
            @can('view_orders')
            @if($pendingOrders && $pendingOrders->isNotEmpty())
            <div class="col-12">
                <div class="card card-outline card-danger">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-clock mr-2 text-danger"></i>
                            Pending Orders
                        </h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.orders.index', ['status' => 'pending']) }}"
                               class="btn btn-sm btn-outline-danger">
                                View all
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                @foreach($pendingOrders as $order)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                           class="font-weight-bold text-dark">
                                            #{{ $order->id }}
                                        </a>
                                        <small class="text-muted d-block">{{ $order->customer_name }}</small>
                                    </td>
                                    <td class="text-muted text-right" style="font-size:.8rem;">
                                        {{ $order->created_at->diffForHumans() }}
                                    </td>
                                    <td class="text-right">
                                        <a href="{{ route('admin.orders.show', $order) }}"
                                           class="btn btn-xs btn-outline-danger p-0 px-2" style="font-size:.75rem;">
                                            Process
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
            @endcan

        </div>
    </div>

</div>

{{-- =====================================================================
     ROW 4 — System info
     ===================================================================== --}}
<div class="row">
    <div class="col-md-6">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>System Info</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted" width="40%">Application</td>
                            <td><strong>{{ \Modules\Auth\Models\Setting::getValue('app_name', config('app.name')) }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Laravel Version</td>
                            <td>{{ app()->version() }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">PHP Version</td>
                            <td>{{ PHP_VERSION }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Environment</td>
                            <td>
                                <span class="badge badge-{{ config('app.env') === 'production' ? 'danger' : 'warning' }}">
                                    {{ config('app.env') }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Logged in as</td>
                            <td>{{ auth()->user()->name }}
                                <span class="badge badge-secondary ml-1">
                                    {{ auth()->user()->roles->first()?->name ?? 'No role' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Queue Driver</td>
                            <td><code>{{ config('queue.default') }}</code></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
@can('view_finance_reports')
@if(!empty($monthlyChartData))
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
const labels = @json(array_column($monthlyChartData, 'label'));
const revenues = @json(array_column($monthlyChartData, 'revenue'));

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Revenue ({{ $currencySymbol }})',
            data: revenues,
            backgroundColor: 'rgba(40, 167, 69, 0.7)',
            borderColor: 'rgba(40, 167, 69, 1)',
            borderWidth: 1,
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => '{{ $currencySymbol }}' + ctx.parsed.y.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: val => '{{ $currencySymbol }}' + val.toLocaleString()
                }
            }
        }
    }
});
</script>
@endif
@endcan
@endpush
