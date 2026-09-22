<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Shop') — {{ \Modules\Auth\Models\Setting::getValue('app_name', config('app.name')) }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous">

    <style>
        body { font-family: 'Inter', sans-serif; background: #f8f9fa; }

        /* Navbar */
        .shop-navbar { background: #1a202c; padding: 0 1rem; }
        .shop-navbar .navbar-brand { color: #fff !important; font-weight: 700; font-size: 1.2rem; }
        .shop-navbar .nav-link { color: rgba(255,255,255,.8) !important; }
        .shop-navbar .nav-link:hover { color: #fff !important; }
        .cart-badge { background: #e53e3e; color: #fff; border-radius: 50%; padding: 1px 6px; font-size: .7rem; }

        /* Product card */
        .product-card { transition: transform .15s, box-shadow .15s; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; height: 100%; }
        .product-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }
        .product-card img { height: 200px; object-fit: cover; width: 100%; }
        .product-card .no-img { height: 200px; background: #edf2f7; display:flex; align-items:center; justify-content:center; color:#a0aec0; font-size:2.5rem; }
        .product-card .card-body { padding: 1rem; }
        .product-card .price { font-size: 1.1rem; font-weight: 700; color: #2d3748; }
        .product-card .category-badge { font-size: .7rem; }

        /* Category sidebar */
        .category-sidebar .list-group-item { border-radius: 0 !important; font-size: .9rem; }
        .category-sidebar .list-group-item.active { background: #2d3748; border-color: #2d3748; }
        .category-child { padding-left: 1.5rem !important; font-size: .85rem; color: #4a5568; }

        /* Breadcrumb */
        .shop-breadcrumb { background: transparent; padding: .5rem 0; font-size: .85rem; }

        /* Checkout steps */
        .checkout-step { text-align: center; }
        .checkout-step .step-num { width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; }
        .checkout-step.active .step-num { background: #2d3748; color: #fff; }
        .checkout-step.done .step-num { background: #48bb78; color: #fff; }
        .checkout-step:not(.active):not(.done) .step-num { background: #e2e8f0; color: #718096; }
    </style>

    @stack('styles')
</head>
<body>

{{-- Top Navbar --}}
<nav class="navbar navbar-expand-lg shop-navbar">
    <a class="navbar-brand" href="{{ route('storefront.products.index') }}">
        <i class="fas fa-store mr-2"></i>
        {{ \Modules\Auth\Models\Setting::getValue('app_name', config('app.name')) }}
    </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#shopNav"
            style="border-color:rgba(255,255,255,.3);">
        <i class="fas fa-bars text-white"></i>
    </button>

    <div class="collapse navbar-collapse" id="shopNav">
        {{-- Search --}}
        <form class="form-inline mx-auto my-2 my-lg-0" method="GET" action="{{ route('storefront.products.index') }}"
              style="max-width:420px; width:100%;">
            <div class="input-group w-100">
                <input type="text" name="search"
                       value="{{ request('search') }}"
                       class="form-control"
                       placeholder="Search products…">
                <div class="input-group-append">
                    <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </div>
        </form>

        <ul class="navbar-nav ml-auto">
            @php $cartCount = array_sum(array_column(session('storefront_cart', []), 'quantity')); @endphp
            <li class="nav-item">
                <a class="nav-link" href="{{ route('storefront.cart.index') }}">
                    <i class="fas fa-shopping-cart mr-1"></i>
                    Cart
                    @if($cartCount > 0)
                        <span class="cart-badge">{{ $cartCount }}</span>
                    @endif
                </a>
            </li>
            @auth
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                    <i class="fas fa-cog mr-1"></i> Admin
                </a>
            </li>
            @endauth
        </ul>
    </div>
</nav>

{{-- Flash messages --}}
<div class="container mt-3">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show py-2">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <i class="fas fa-exclamation-circle mr-1"></i>{{ session('error') }}
        </div>
    @endif
</div>

{{-- Main content --}}
@yield('content')

{{-- Footer --}}
<footer class="mt-5 py-4" style="background:#1a202c; color:rgba(255,255,255,.6); font-size:.85rem;">
    <div class="container text-center">
        <p class="mb-0">
            &copy; {{ date('Y') }}
            {{ \Modules\Auth\Models\Setting::getValue('app_name', config('app.name')) }}.
            All rights reserved.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
</body>
</html>
