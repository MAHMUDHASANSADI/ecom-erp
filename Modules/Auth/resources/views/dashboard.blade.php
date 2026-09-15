@extends('auth::layouts.admin')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item active">Dashboard</li>
@endsection

@section('content')
<div class="row">
    {{-- Today's Sales --}}
    @can('operate_pos')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3 id="stat-sales-today">—</h3>
                <p>Today's Sales</p>
            </div>
            <div class="icon"><i class="fas fa-cash-register"></i></div>
            <a href="#" class="small-box-footer">View details <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endcan

    {{-- Revenue Today --}}
    @can('view_finance_reports')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3 id="stat-revenue-today">—</h3>
                <p>Today's Revenue</p>
            </div>
            <div class="icon"><i class="fas fa-dollar-sign"></i></div>
            <a href="#" class="small-box-footer">View reports <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endcan

    {{-- Low Stock --}}
    @can('view_inventory')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3 id="stat-low-stock">—</h3>
                <p>Low Stock Alerts</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="#" class="small-box-footer">View inventory <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endcan

    {{-- Pending Orders --}}
    @can('view_orders')
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3 id="stat-pending-orders">—</h3>
                <p>Pending Orders</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-cart"></i></div>
            <a href="#" class="small-box-footer">View orders <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    @endcan
</div>

<div class="row">
    {{-- Quick actions panel --}}
    <div class="col-md-6">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt mr-2"></i>Quick Actions</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    @can('operate_pos')
                    <div class="col-6 mb-3">
                        <a href="#" class="btn btn-block btn-outline-primary">
                            <i class="fas fa-cash-register fa-lg d-block mb-1"></i>
                            Open POS
                        </a>
                    </div>
                    @endcan
                    @can('manage_products')
                    <div class="col-6 mb-3">
                        <a href="#" class="btn btn-block btn-outline-success">
                            <i class="fas fa-plus-circle fa-lg d-block mb-1"></i>
                            Add Product
                        </a>
                    </div>
                    @endcan
                    @can('manage_inventory')
                    <div class="col-6 mb-3">
                        <a href="#" class="btn btn-block btn-outline-warning">
                            <i class="fas fa-boxes fa-lg d-block mb-1"></i>
                            Adjust Stock
                        </a>
                    </div>
                    @endcan
                    @can('manage_expenses')
                    <div class="col-6 mb-3">
                        <a href="#" class="btn btn-block btn-outline-danger">
                            <i class="fas fa-receipt fa-lg d-block mb-1"></i>
                            Log Expense
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- System info --}}
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
                            <td><strong>{{ config('app.name') }}</strong></td>
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
                            <td>{{ auth()->user()->name }} ({{ auth()->user()->roles->first()?->name ?? 'No role' }})</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
