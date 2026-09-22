@extends('storefront::layouts.shop')

@section('title', 'Order Confirmed')

@section('content')
<div class="container mt-5" style="max-width:680px;">

    {{-- Success banner --}}
    <div class="text-center mb-4">
        <div class="mb-3">
            <i class="fas fa-check-circle text-success" style="font-size:4rem;"></i>
        </div>
        <h3 class="font-weight-bold">Order Placed!</h3>
        <p class="text-muted">
            Thank you, <strong>{{ $order->customer_name }}</strong>!
            A confirmation email has been sent to <strong>{{ $order->email }}</strong>.
        </p>
    </div>

    {{-- Order detail card --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <span><i class="fas fa-receipt mr-2"></i>Order #{{ $order->id }}</span>
            <span class="badge badge-warning">{{ ucfirst($order->status) }}</span>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Qty</th>
                        <th class="text-right">Unit Price</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>{{ $item->product->name ?? '—' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right font-weight-bold">${{ number_format($item->line_total, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="table-light">
                        <td colspan="3" class="text-right font-weight-bold">Order Total</td>
                        <td class="text-right font-weight-bold text-success">
                            ${{ number_format($order->total, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <div class="card-body border-top">
            <dl class="row mb-0">
                <dt class="col-4 text-muted">Delivery to</dt>
                <dd class="col-8">{{ $order->address }}</dd>
                <dt class="col-4 text-muted">Payment</dt>
                <dd class="col-8">Cash on Delivery</dd>
                <dt class="col-4 text-muted">Placed</dt>
                <dd class="col-8">{{ $order->created_at->format('d M Y H:i') }}</dd>
            </dl>
        </div>
    </div>

    <div class="text-center">
        <a href="{{ route('storefront.products.index') }}" class="btn btn-dark px-4">
            <i class="fas fa-shopping-bag mr-1"></i> Continue Shopping
        </a>
    </div>

</div>
@endsection
