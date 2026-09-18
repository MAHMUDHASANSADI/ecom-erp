@extends('auth::layouts.admin')

@section('title', 'Point of Sale')
@section('page-title', 'Point of Sale')

@section('breadcrumb')
    <li class="breadcrumb-item active">POS</li>
@endsection

@push('styles')
<style>
    .pos-product-card {
        cursor: pointer;
        transition: transform .1s, box-shadow .1s;
        border: 2px solid transparent;
    }
    .pos-product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,.15);
        border-color: #007bff;
    }
    .pos-product-card.out-of-stock {
        opacity: .5;
        cursor: not-allowed;
    }
    #cart-table tbody tr td { vertical-align: middle; }
    #checkout-panel { position: sticky; top: 70px; }
    .cart-qty-btn { width:28px; height:28px; padding:0; line-height:1; }
</style>
@endpush

@section('content')
<div class="row">

    {{-- LEFT: Product search + grid --}}
    <div class="col-lg-7 col-md-6">

        {{-- Search bar --}}
        <div class="input-group mb-3">
            <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <input type="text" id="productSearch"
                   class="form-control form-control-lg"
                   placeholder="Search product name or scan SKU barcode…"
                   autocomplete="off" autofocus>
            <div class="input-group-append">
                <button class="btn btn-outline-secondary" id="clearSearch" type="button">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        {{-- Search results --}}
        <div id="searchResults" class="row" style="min-height:100px;">
            <div class="col-12 text-center text-muted py-5" id="searchPlaceholder">
                <i class="fas fa-search fa-2x mb-2 d-block"></i>
                Type a product name or scan a barcode to search
            </div>
        </div>
    </div>

    {{-- RIGHT: Cart + checkout --}}
    <div class="col-lg-5 col-md-6" id="checkout-panel">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-shopping-cart mr-2"></i>
                    Cart <span class="badge badge-primary ml-1" id="cartCount">0</span>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-sm btn-outline-danger" id="clearCart">
                        <i class="fas fa-trash mr-1"></i> Clear
                    </button>
                </div>
            </div>

            {{-- Cart table --}}
            <div class="card-body p-0" style="max-height:340px; overflow-y:auto;">
                <table class="table table-sm mb-0" id="cart-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Product</th>
                            <th class="text-center" style="width:110px;">Qty</th>
                            <th class="text-right" style="width:90px;">Total</th>
                            <th style="width:30px;"></th>
                        </tr>
                    </thead>
                    <tbody id="cartBody">
                        <tr id="emptyCartRow">
                            <td colspan="4" class="text-center text-muted py-3">Cart is empty</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="card-footer p-2">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="font-weight-bold">Total:</span>
                    <span class="font-weight-bold h4 mb-0 text-success" id="cartTotal">
                        {{ $currencySymbol }}0.00
                    </span>
                </div>

                {{-- Complete sale form --}}
                <form id="saleForm" method="POST" action="{{ route('admin.pos.sales.store') }}">
                    @csrf
                    <input type="hidden" name="payment_method" id="paymentMethodInput">
                    <div id="itemsContainer"></div>

                    <div class="form-group mb-2">
                        <label class="mb-1" style="font-size:.85rem;">Payment Method</label>
                        <div class="d-flex flex-wrap" id="paymentButtons">
                            @foreach($paymentMethods as $method)
                            <button type="button"
                                    class="btn btn-outline-secondary btn-sm mr-1 mb-1 payment-btn"
                                    data-code="{{ $method->code }}">
                                @if($method->code === 'cash')
                                    <i class="fas fa-money-bill-wave mr-1"></i>
                                @elseif($method->code === 'card')
                                    <i class="fas fa-credit-card mr-1"></i>
                                @else
                                    <i class="fas fa-truck mr-1"></i>
                                @endif
                                {{ $method->label }}
                            </button>
                            @endforeach
                        </div>
                        <div id="paymentError" class="text-danger small" style="display:none;">
                            Please select a payment method.
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-block btn-lg" id="completeSaleBtn" disabled>
                        <i class="fas fa-check-circle mr-2"></i> Complete Sale
                    </button>
                </form>
            </div>
        </div>

        {{-- Quick links --}}
        <div class="d-flex justify-content-between mt-2">
            <a href="{{ route('admin.pos.daily-summary') }}" class="btn btn-sm btn-outline-info">
                <i class="fas fa-chart-bar mr-1"></i> Daily Summary
            </a>
            @can('view_inventory')
            <a href="{{ route('admin.inventory.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-boxes mr-1"></i> Stock Levels
            </a>
            @endcan
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CURRENCY = @json($currencySymbol);
const SEARCH_URL = @json(route('admin.pos.product-search'));

// ─── Cart state ───────────────────────────────────────────────────────────────
let cart = {}; // { productId: { id, name, sku, price, quantity, stock } }
let selectedPayment = null;

// ─── Product search ───────────────────────────────────────────────────────────
let searchTimer;
document.getElementById('productSearch').addEventListener('input', function () {
    clearTimeout(searchTimer);
    const q = this.value.trim();
    if (!q) { resetSearch(); return; }
    searchTimer = setTimeout(() => fetchProducts(q), 250);
});

document.getElementById('clearSearch').addEventListener('click', () => {
    document.getElementById('productSearch').value = '';
    resetSearch();
});

function resetSearch() {
    document.getElementById('searchResults').innerHTML =
        '<div class="col-12 text-center text-muted py-5" id="searchPlaceholder">' +
        '<i class="fas fa-search fa-2x mb-2 d-block"></i>Type a product name or scan a barcode to search</div>';
}

function fetchProducts(q) {
    fetch(`${SEARCH_URL}?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(products => renderProducts(products))
        .catch(() => {});
}

function renderProducts(products) {
    const container = document.getElementById('searchResults');
    if (!products.length) {
        container.innerHTML = '<div class="col-12 text-center text-muted py-4">No products found.</div>';
        return;
    }
    container.innerHTML = products.map(p => `
        <div class="col-6 col-xl-4 mb-3">
            <div class="card pos-product-card h-100 ${p.current_stock <= 0 ? 'out-of-stock' : ''}"
                 onclick="${p.current_stock > 0 ? `addToCart(${JSON.stringify(p).replace(/"/g, '&quot;')})` : ''}">
                <div class="card-body p-2 text-center">
                    ${p.image_url
                        ? `<img src="${p.image_url}" class="img-fluid mb-1" style="height:60px;object-fit:contain;">`
                        : `<div class="bg-light d-flex align-items-center justify-content-center mb-1" style="height:60px;"><i class="fas fa-box text-muted"></i></div>`
                    }
                    <p class="mb-0 font-weight-bold" style="font-size:.8rem;line-height:1.2;">${p.name}</p>
                    <small class="text-muted d-block">${p.sku}</small>
                    <span class="badge badge-${p.current_stock <= 0 ? 'danger' : (p.current_stock <= 5 ? 'warning' : 'success')} mt-1">
                        ${p.current_stock <= 0 ? 'Out of stock' : p.current_stock + ' in stock'}
                    </span>
                    <div class="font-weight-bold text-success mt-1">${CURRENCY}${parseFloat(p.price).toFixed(2)}</div>
                </div>
            </div>
        </div>
    `).join('');
}

// ─── Cart management ──────────────────────────────────────────────────────────
function addToCart(product) {
    if (cart[product.id]) {
        if (cart[product.id].quantity >= product.current_stock) {
            Swal.fire({ icon:'warning', title:'Stock limit', text:`Only ${product.current_stock} in stock.`, timer:2000, showConfirmButton:false });
            return;
        }
        cart[product.id].quantity++;
    } else {
        cart[product.id] = { ...product, quantity: 1 };
    }
    renderCart();
}

function removeFromCart(productId) {
    delete cart[productId];
    renderCart();
}

function changeQty(productId, delta) {
    if (!cart[productId]) { return; }
    const newQty = cart[productId].quantity + delta;
    if (newQty <= 0) { removeFromCart(productId); return; }
    if (newQty > cart[productId].current_stock) {
        Swal.fire({ icon:'warning', title:'Stock limit', text:`Only ${cart[productId].current_stock} in stock.`, timer:2000, showConfirmButton:false });
        return;
    }
    cart[productId].quantity = newQty;
    renderCart();
}

document.getElementById('clearCart').addEventListener('click', () => {
    if (Object.keys(cart).length === 0) { return; }
    Swal.fire({ title:'Clear cart?', icon:'question', showCancelButton:true, confirmButtonText:'Yes, clear it' })
        .then(r => { if (r.isConfirmed) { cart = {}; renderCart(); } });
});

function renderCart() {
    const tbody = document.getElementById('cartBody');
    const items = Object.values(cart);
    const count = items.reduce((s, i) => s + i.quantity, 0);
    const total = items.reduce((s, i) => s + i.price * i.quantity, 0);

    document.getElementById('cartCount').textContent = count;
    document.getElementById('cartTotal').textContent = CURRENCY + total.toFixed(2);
    document.getElementById('completeSaleBtn').disabled = items.length === 0 || !selectedPayment;

    // Hidden inputs for form submission
    const container = document.getElementById('itemsContainer');
    container.innerHTML = items.map((item, idx) => `
        <input type="hidden" name="items[${idx}][product_id]" value="${item.id}">
        <input type="hidden" name="items[${idx}][quantity]" value="${item.quantity}">
        <input type="hidden" name="items[${idx}][unit_price]" value="${item.price}">
    `).join('');

    if (items.length === 0) {
        tbody.innerHTML = '<tr id="emptyCartRow"><td colspan="4" class="text-center text-muted py-3">Cart is empty</td></tr>';
        return;
    }

    tbody.innerHTML = items.map(item => `
        <tr>
            <td>
                <span style="font-size:.85rem;">${item.name}</span>
                <br><small class="text-muted">${CURRENCY}${parseFloat(item.price).toFixed(2)} each</small>
            </td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center">
                    <button type="button" class="btn btn-outline-secondary cart-qty-btn"
                            onclick="changeQty(${item.id}, -1)">−</button>
                    <span class="mx-2 font-weight-bold">${item.quantity}</span>
                    <button type="button" class="btn btn-outline-secondary cart-qty-btn"
                            onclick="changeQty(${item.id}, 1)">+</button>
                </div>
            </td>
            <td class="text-right font-weight-bold">${CURRENCY}${(item.price * item.quantity).toFixed(2)}</td>
            <td>
                <button type="button" class="btn btn-sm btn-outline-danger p-0 px-1"
                        onclick="removeFromCart(${item.id})">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        </tr>
    `).join('');
}

// ─── Payment method selection ─────────────────────────────────────────────────
document.querySelectorAll('.payment-btn').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('.payment-btn').forEach(b => {
            b.classList.remove('btn-primary', 'active');
            b.classList.add('btn-outline-secondary');
        });
        this.classList.remove('btn-outline-secondary');
        this.classList.add('btn-primary', 'active');
        selectedPayment = this.dataset.code;
        document.getElementById('paymentMethodInput').value = selectedPayment;
        document.getElementById('paymentError').style.display = 'none';
        document.getElementById('completeSaleBtn').disabled =
            Object.keys(cart).length === 0;
    });
});

// ─── Form submit guard ────────────────────────────────────────────────────────
document.getElementById('saleForm').addEventListener('submit', function (e) {
    if (!selectedPayment) {
        e.preventDefault();
        document.getElementById('paymentError').style.display = 'block';
        return;
    }
    if (Object.keys(cart).length === 0) {
        e.preventDefault();
        Swal.fire({ icon: 'warning', title: 'Empty cart', text: 'Add at least one product.' });
    }
});
</script>
@endpush
