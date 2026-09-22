@extends('auth::layouts.admin')

@section('title', 'Orders')
@section('page-title', 'Online Orders')

@section('breadcrumb')
    <li class="breadcrumb-item active">Orders</li>
@endsection

@section('content')

{{-- Pending badge banner --}}
@if($pendingCount > 0)
<div class="alert alert-warning alert-dismissible fade show">
    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
    <i class="fas fa-clock mr-2"></i>
    <strong>{{ $pendingCount }} order(s)</strong> are waiting to be processed.
</div>
@endif

{{-- Filter bar --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="form-inline flex-wrap">
            <div class="input-group input-group-sm mr-2 mb-1" style="min-width:220px;">
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                       class="form-control" placeholder="Search name or email…">
                <div class="input-group-append">
                    <button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>

            <select name="status" class="form-control form-control-sm mr-2 mb-1" style="min-width:160px;">
                <option value="">All statuses</option>
                @foreach($statuses as $s)
                    <option value="{{ $s->code }}" {{ ($filters['status'] ?? '') === $s->code ? 'selected' : '' }}>
                        {{ $s->label }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-secondary mb-1">
                <i class="fas fa-times mr-1"></i> Clear
            </a>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-shopping-bag mr-2"></i>All Orders
            <span class="badge badge-secondary ml-1">{{ $orders->total() }}</span>
        </h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>#</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Items</th>
                    <th class="text-right">Total</th>
                    <th>Status</th>
                    <th>Placed</th>
                    <th class="text-right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                <tr>
                    <td><strong>{{ $order->id }}</strong></td>
                    <td>{{ $order->customer_name }}</td>
                    <td><a href="mailto:{{ $order->email }}">{{ $order->email }}</a></td>
                    <td>
                        <span class="badge badge-secondary">
                            {{ $order->items->count() }} item(s)
                        </span>
                    </td>
                    <td class="text-right font-weight-bold">
                        ${{ number_format($order->total, 2) }}
                    </td>
                    <td>
                        @php
                            $statusColors = [
                                'pending'    => 'warning',
                                'processing' => 'info',
                                'completed'  => 'success',
                                'cancelled'  => 'danger',
                                'refunded'   => 'secondary',
                            ];
                        @endphp
                        <span class="badge badge-{{ $statusColors[$order->status] ?? 'secondary' }}">
                            {{ ucfirst($order->status) }}
                        </span>
                    </td>
                    <td>{{ $order->created_at->format('d M Y H:i') }}</td>
                    <td class="text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">No orders found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($orders->hasPages())
    <div class="card-footer">
        {{ $orders->appends($filters)->links() }}
    </div>
    @endif
</div>
@endsection
