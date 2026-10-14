@extends('storefront::layouts.shop')

@section('title', 'Checkout')

@section('content')
<div class="container mt-4" style="max-width:900px;">

    <h4 class="font-weight-bold mb-4"><i class="fas fa-lock mr-2"></i>Checkout</h4>

    <div class="row">

        {{-- Left: Customer details + payment method --}}
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
                                   placeholder="e.g. Sadi Rahman" required autofocus>
                            @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group">
                            <label>Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                   value="{{ old('email') }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="e.g. sadi@example.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="text-muted">Order confirmation will be sent here.</small>
                        </div>

                        <div class="form-group">
                            <label>Delivery Address <span class="text-danger">*</span></label>
                            <textarea name="address" rows="3"
                                      class="form-control @error('address') is-invalid @enderror"
                                      placeholder="House/Road, Area, District" required>{{ old('address') }}</textarea>
                            @error('address')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Payment method selector --}}
                        <div class="form-group mb-3">
                            <label class="d-block font-weight-bold mb-2">
                                <i class="fas fa-credit-card mr-1"></i> Payment Method
                                <span class="text-danger">*</span>
                            </label>
                            @error('payment_method')
                                <div class="text-danger small mb-2">{{ $message }}</div>
                            @enderror

                            {{-- SSLCommerz --}}
                            <div class="payment-option border rounded p-3 mb-2 cursor-pointer"
                                 id="opt-sslcommerz"
                                 onclick="selectPayment('sslcommerz')"
                                 style="cursor:pointer; transition:border-color .2s;">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="rounded-circle border d-flex align-items-center justify-content-center"
                                             style="width:20px;height:20px;" id="radio-sslcommerz">
                                        </div>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="font-weight-bold">Pay Online</div>
                                        <small class="text-muted">
                                            Credit/Debit Card, bKash, Nagad, Rocket &amp; all bank cards
                                        </small>
                                    </div>
                                    <div class="ml-2 d-flex align-items-center" style="gap:4px;">
                                        <span class="badge" style="background:#e8f5e9;color:#1b5e20;font-size:.7rem;">bKash</span>
                                        <span class="badge" style="background:#fff3e0;color:#e65100;font-size:.7rem;">Nagad</span>
                                        <span class="badge" style="background:#e3f2fd;color:#0d47a1;font-size:.7rem;">VISA</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Cash on Delivery --}}
                            <div class="payment-option border rounded p-3 cursor-pointer"
                                 id="opt-cod"
                                 onclick="selectPayment('cod')"
                                 style="cursor:pointer; transition:border-color .2s;">
                                <div class="d-flex align-items-center">
                                    <div class="mr-3">
                                        <div class="rounded-circle border d-flex align-items-center justify-content-center"
                                             style="width:20px;height:20px;" id="radio-cod">
                                        </div>
                                    </div>
                                    <div>
                                        <div class="font-weight-bold">Cash on Delivery</div>
                                        <small class="text-muted">Pay in cash when your order arrives</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden input --}}
                            <input type="hidden" name="payment_method" id="paymentMethodInput"
                                   value="{{ old('payment_method', 'sslcommerz') }}">
                        </div>

                        <button type="submit" class="btn btn-dark btn-block btn-lg" id="placeOrderBtn">
                            <i class="fas fa-lock mr-1"></i>
                            <span id="btnText">Proceed to Payment</span>
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

            {{-- SSLCommerz security badge --}}
            <div class="text-center mt-3">
                <small class="text-muted">
                    <i class="fas fa-shield-alt text-success mr-1"></i>
                    Payments secured by <strong>SSLCommerz</strong>
                </small>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialise with the previously selected / default value
    selectPayment(document.getElementById('paymentMethodInput').value || 'sslcommerz');

    function selectPayment(method) {
        document.getElementById('paymentMethodInput').value = method;

        // Reset both options
        ['sslcommerz', 'cod'].forEach(m => {
            const opt = document.getElementById('opt-' + m);
            const radio = document.getElementById('radio-' + m);
            opt.style.borderColor = '#dee2e6';
            opt.style.background = '';
            radio.innerHTML = '';
            radio.style.background = '';
        });

        // Highlight selected
        const selected = document.getElementById('opt-' + method);
        const selectedRadio = document.getElementById('radio-' + method);
        selected.style.borderColor = '#343a40';
        selected.style.background = '#f8f9fa';
        selectedRadio.innerHTML = '<div style="width:10px;height:10px;border-radius:50%;background:#343a40;"></div>';

        // Update button text
        const btnText = document.getElementById('btnText');
        if (method === 'sslcommerz') {
            btnText.textContent = 'Proceed to Payment';
        } else {
            btnText.textContent = 'Place Order';
        }
    }
</script>
@endpush
