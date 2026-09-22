@extends('storefront::layouts.shop')

@section('title', 'Checkout')

@section('content')
<div class="container mt-4" style="max-width:900px;">

    <h4 class="font-weight-bold mb-4"><i class="fas fa-lock mr-2"></i>Checkout</h4>

    <div class="row">

        {{-- Left: Customer details form --}}
        <div class="col-md-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fas fa-user mr-2"></i>Delivery Information</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('storefront.checkout.store') }}" id="checkoutForm">
                        @csrf

                        @if($errors->any())
                            <div class="alert alert-danger py-2">
                                @foreach($errors->all() as $error)
                                    <div>{{ $error }}</div>
                                @endforeach
                            </div>
                        @endif

                        <div class="form-group">
                            <label>Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name"
                                   value="{{ old('customer_name') }}"
                                   class="form-control @error('customer_name') is-invalid @enderror"
                                   placeholder="e.g. Jane Smith" required autofocus>
                            @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label>Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                   value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="e.g. jane@example.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Order confirmation will be sent here.</small>
                        </div>

                        <div class="form-group">
                            <label>Delivery Address <span class="text-danger">*</span></label>
                            <textarea name="address" rows="3"
                                      class="form-control @error('address') is-invalid @enderror"
                                      placeholder="Street, City, State, Postcode" required>{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="alert alert-info py-2 mb-3">
                            <i class="fas fa-truck mr-1"></i>
                            <strong>Payment:</strong> Cash on Delivery
                            — you pay when your order arrives.
                        </div>

                        <button type="submit" class="btn btn-dark btn-block btn-lg">
                            <i class="fas fa-check-circle mr-1"></i> Place Order
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Order summary --}}
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h6 class="mb-0"><i class="fas fa-receipt mr-2"></i>Order Summary</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm mb-0">
                        <tbody>
                            @foreach($cart as $item)
                            <tr>
                                <td>
                                    {{ $item['name'] }}
                                    <small class="text-muted d-block">× {{ $item['quantity'] }}</small>
                                </td>
                                <td class="text-right font-weight-bold text-nowrap">
                                    ${{ number_format($item['price'] * $item['quantity'], 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <td class="font-weight-bold">Total</td>
                                <td class="text-right font-weight-bold text-success">
                                    ${{ number_format($total, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer">
                    <a href="{{ route('storefront.cart.index') }}" class="btn btn-outline-secondary btn-sm btn-block">
                        <i class="fas fa-arrow-left mr-1"></i> Edit Cart
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
