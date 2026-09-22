@extends('storefront::layouts.shop')

@section('title', 'Your Cart')

@section('content')
<div class="container mt-4" style="max-width:820px;">

    <h4 class="font-weight-bold mb-4"><i class="fas fa-shopping-cart mr-2"></i>Your Cart</h4>

    @if(empty($cart))
        <div class="text-center py-5 text-muted">
            <i class="fas fa-cart-arrow-down fa-3x mb-3 d-block"></i>
            <p class="mb-3">Your cart is empty.</p>
            <a href="{{ route('storefront.products.index') }}" class="btn btn-dark">
                <i class="fas fa-arrow-left mr-1"></i> Continue Shopping
            </a>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px;"></th>
                            <th>Product</th>
                            <th class="text-center" style="width:140px;">Quantity</th>
                            <th class="text-right" style="width:110px;">Price</th>
                            <th class="text-right" style="width:110px;">Total</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cart as $item)
                        <tr>
                            <td>
                                @if($item['image_url'])
                                    <img src="{{ $item['image_url'] }}" alt="{{ $item['name'] }}"
                                         class="img-thumbnail" style="width:48px;height:48px;object-fit:cover;">
                                @else
                                    <div class="bg-light d-flex align-items-center justify-content-center"
                                         style="width:48px;height:48px;border-radius:4px;">
                                        <i class="fas fa-image text-muted"></i>
                                    </div>
                                @endif
                            </td>
                            <td style="vertical-align:middle;">
                                <strong>{{ $item['name'] }}</strong>
                                <br><small class="text-muted">${{ number_format($item['price'], 2) }} each</small>
                            </td>
                            <td style="vertical-align:middle;">
                                <form method="POST"
                                      action="{{ route('storefront.cart.update', $item['product_id']) }}"
                                      class="d-flex align-items-center justify-content-center">
                                    @csrf @method('PATCH')
                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}"
                                           min="0" max="{{ $item['stock'] }}"
                                           class="form-control form-control-sm text-center mr-1"
                                           style="max-width:70px;"
                                           onchange="this.form.submit()">
                                </form>
                            </td>
                            <td style="vertical-align:middle;" class="text-right">
                                ${{ number_format($item['price'], 2) }}
                            </td>
                            <td style="vertical-align:middle;" class="text-right font-weight-bold">
                                ${{ number_format($item['price'] * $item['quantity'], 2) }}
                            </td>
                            <td style="vertical-align:middle;">
                                <form method="POST"
                                      action="{{ route('storefront.cart.remove', $item['product_id']) }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger p-0 px-2"
                                            title="Remove">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="table-light">
                            <td colspan="4" class="text-right font-weight-bold">Order Total:</td>
                            <td class="text-right font-weight-bold text-success h5 mb-0">
                                ${{ number_format(collect($cart)->sum(fn($i) => $i['price'] * $i['quantity']), 2) }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="d-flex justify-content-between mt-3">
            <div>
                <a href="{{ route('storefront.products.index') }}" class="btn btn-outline-secondary mr-2">
                    <i class="fas fa-arrow-left mr-1"></i> Continue Shopping
                </a>
                <form method="POST" action="{{ route('storefront.cart.clear') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="fas fa-trash mr-1"></i> Clear cart
                    </button>
                </form>
            </div>
            <a href="{{ route('storefront.checkout.index') }}" class="btn btn-dark btn-lg">
                <i class="fas fa-lock mr-1"></i> Proceed to Checkout
            </a>
        </div>
    @endif
</div>
@endsection
