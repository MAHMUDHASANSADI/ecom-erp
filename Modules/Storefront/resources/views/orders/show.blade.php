@extends('auth::layouts.admin')

@section('title', 'Order #' . $order->id)
@section('page-title', 'Order #' . $order->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.orders.index') }}">Orders</a></li>
    <li class="breadcrumb-item active">#{{ $order->id }}</li>
@endsection

@section('content')
<div class="row">

    {{-- Left: order items + customer info --}}
    <div class="col-md-8">

        {{-- Items --}}
        <div class="card card-outline card-primary mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-box mr-2"></i>Order Items</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Product</th>
                            <th>SKU</th>
                            <th class="text-center">Qty</th>
                            <th class="text-right">Unit Price</th>
                            <th class="text-right">Line Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->product->name ?? '—' }}</strong>
                            </td>
                            <td><code>{{ $item->product->sku ?? '—' }}</code></td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                            <td class="text-right font-weight-bold">
                                ${{ number_format($item->line_total, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="4" class="text-right font-weight-bold">Order Total</td>
                            <td class="text-right font-weight-bold text-success">
                                ${{ number_format($order->total, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        {{-- Customer information --}}
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user mr-2"></i>Customer Information</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3 text-muted">Name</dt>
                    <dd class="col-sm-9">{{ $order->customer_name }}</dd>

                    <dt class="col-sm-3 text-muted">Email</dt>
                    <dd class="col-sm-9">
                        <a href="mailto:{{ $order->email }}">{{ $order->email }}</a>
                    </dd>

                    <dt class="col-sm-3 text-muted">Address</dt>
                    <dd class="col-sm-9">{{ $order->address }}</dd>

                    <dt class="col-sm-3 text-muted">Payment</dt>
                    <dd class="col-sm-9">Cash on Delivery</dd>

                    <dt class="col-sm-3 text-muted">Placed</dt>
                    <dd class="col-sm-9">{{ $order->created_at->format('d M Y H:i') }}</dd>

                    @if($order->sale)
                    <dt class="col-sm-3 text-muted">Sale Ref</dt>
                    <dd class="col-sm-9">
                        <a href="{{ route('admin.pos.sales.receipt', $order->sale) }}">
                            {{ $order->sale->sale_number }}
                        </a>
                    </dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>

    {{-- Right: status management --}}
    <div class="col-md-4">

        {{-- Current status --}}
        <div class="card card-outline card-warning mb-3">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-tasks mr-2"></i>Order Status</h3>
            </div>
            <div class="card-body text-center">
                @php
                    $statusColors = [
                        'pending'    => 'warning',
                        'processing' => 'info',
                        'completed'  => 'success',
                        'cancelled'  => 'danger',
                        'refunded'   => 'secondary',
                    ];
                @endphp
                <span class="badge badge-{{ $statusColors[$order->status] ?? 'secondary' }} px-4 py-2"
                      style="font-size:1rem;">
                    {{ ucfirst($order->status) }}
                </span>
            </div>

            @can('manage_orders')
            <div class="card-footer">
                <form method="POST" action="{{ route('admin.orders.update', $order) }}">
                    @csrf @method('PUT')
                    <div class="form-group mb-2">
                        <label class="mb-1" style="font-size:.85rem;">Update Status</label>
                        <select name="status" class="form-control form-control-sm">
                            @foreach($statuses as $s)
                                <option value="{{ $s->code }}"
                                        {{ $order->status === $s->code ? 'selected' : '' }}>
                                    {{ $s->label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning btn-sm btn-block">
                        <i class="fas fa-save mr-1"></i> Save Status
                    </button>
                </form>
            </div>
            @endcan
        </div>

        {{-- Quick actions --}}
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-bolt mr-2"></i>Actions</h3>
            </div>
            <div class="card-body">
                <a href="mailto:{{ $order->email }}" class="btn btn-outline-info btn-sm btn-block mb-2">
                    <i class="fas fa-envelope mr-1"></i> Email Customer
                </a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm btn-block">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Orders
                </a>
            </div>
        </div>

    </div>
</div>
@endsection
