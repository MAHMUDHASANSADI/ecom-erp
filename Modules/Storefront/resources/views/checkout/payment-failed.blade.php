@extends('storefront::layouts.shop')

@section('title', 'Payment Failed')

@section('content')
<div class="container mt-5" style="max-width:600px;">
    <div class="text-center mb-4">
        <i class="fas fa-times-circle text-danger" style="font-size:4rem;"></i>
        <h3 class="font-weight-bold mt-3">Payment Failed</h3>
        <p class="text-muted">
            Your payment could not be processed.
            Your order <strong>#{{ $order->id }}</strong> has been placed but is awaiting payment.
        </p>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-5 text-muted">Order #</dt>
                <dd class="col-7"><strong>{{ $order->id }}</strong></dd>

                <dt class="col-5 text-muted">Status</dt>
                <dd class="col-7">
                    <span class="badge badge-danger">Payment Failed</span>
                </dd>

                <dt class="col-5 text-muted">Amount</dt>
                <dd class="col-7">${{ number_format($order->total, 2) }}</dd>
            </dl>
        </div>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-center" style="gap:.75rem;">
        {{-- Retry with SSLCommerz --}}
        <a href="{{ route('storefront.payment.retry', $order) }}"
           class="btn btn-success btn-lg px-4">
            <i class="fas fa-redo mr-1"></i> Retry Payment
        </a>

        {{-- Switch to COD --}}
        <form method="POST" action="{{ route('storefront.payment.switch-to-cod', $order) }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary btn-lg px-4">
                <i class="fas fa-truck mr-1"></i> Switch to Cash on Delivery
            </button>
        </form>

        <a href="{{ route('storefront.products.index') }}" class="btn btn-outline-dark btn-lg px-4">
            <i class="fas fa-arrow-left mr-1"></i> Continue Shopping
        </a>
    </div>

    <p class="text-center text-muted mt-4" style="font-size:.85rem;">
        Need help? Contact us and quote order #{{ $order->id }}.
    </p>
</div>
@endsection
