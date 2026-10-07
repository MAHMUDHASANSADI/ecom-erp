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
        .shop-navbar { background: #ffffff; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0; transition: all 0.3s ease; }
        .shop-navbar .navbar-brand { color: #1a202c !important; font-weight: 800; font-size: 1.4rem; }
        .shop-navbar .nav-link { color: #4a5568 !important; font-weight: 500; padding: 0.5rem 1rem !important; transition: color 0.2s; }
        .shop-navbar .nav-link:hover { color: #3182ce !important; }
        .search-box { border-radius: 50px; overflow: hidden; border: 1px solid #e2e8f0; transition: border-color 0.2s; }
        .search-box:focus-within { border-color: #3182ce; }
        .search-box input:focus { box-shadow: none; background: #fff !important; }
        .cart-badge { background: #e53e3e; color: #fff; border-radius: 50%; padding: 2px 6px; font-size: 0.65rem; font-weight: bold; }
        .text-primary { color: #3182ce !important; }
        .hover-white:hover { color: #fff !important; }

        /* Product card */
        .product-card { transition: transform .2s ease, box-shadow .2s ease; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; height: 100%; background: #ffffff; }
        .product-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,.08); }
        .product-card img { height: 200px; object-fit: cover; width: 100%; }
        .product-card .no-img { height: 200px; background: #edf2f7; display:flex; align-items:center; justify-content:center; color:#a0aec0; font-size:2.5rem; }
        .product-card .card-body { padding: 1.25rem; }
        .product-card .price { font-size: 1.2rem; font-weight: 700; color: #1a202c; }
        .product-card .category-badge { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

        /* Category sidebar */
        .category-sidebar .list-group-item { border-radius: 8px !important; font-size: .95rem; font-weight: 500; border: none; margin-bottom: 2px; color: #4a5568; }
        .category-sidebar .list-group-item:hover { background: #edf2f7; color: #1a202c; }
        .category-sidebar .list-group-item.active { background: #3182ce; color: #fff; }
        .category-child { padding-left: 2rem !important; font-size: .85rem; }

        /* Breadcrumb */
        .shop-breadcrumb { background: transparent; padding: .5rem 0; font-size: .85rem; font-weight: 500; }

        /* Checkout steps */
        .checkout-step { text-align: center; }
        .checkout-step .step-num { width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: .85rem; transition: background 0.2s, color 0.2s; }
        .checkout-step.active .step-num { background: #3182ce; color: #fff; }
        .checkout-step.done .step-num { background: #48bb78; color: #fff; }
        .checkout-step:not(.active):not(.done) .step-num { background: #e2e8f0; color: #718096; }
    </style>

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">

{{-- Top Navbar --}}
<nav class="navbar navbar-expand-lg shop-navbar shadow-sm sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ route('storefront.products.index') }}">
            <i class="fas fa-store mr-2 text-primary"></i>
            <span style="letter-spacing: -0.5px;">{{ \Modules\Auth\Models\Setting::getValue('app_name', config('app.name')) }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#shopNav">
            <i class="fas fa-bars" style="color: #4a5568;"></i>
        </button>

        <div class="collapse navbar-collapse" id="shopNav">
            <ul class="navbar-nav mr-auto pl-lg-3">
                @php $topCategories = \Modules\Catalog\Models\Category::whereNull('parent_category_id')->get(); @endphp
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="categoriesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Categories
                    </a>
                    <div class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="categoriesDropdown">
                        <a class="dropdown-item" href="{{ route('storefront.products.index') }}">All Products</a>
                        <div class="dropdown-divider"></div>
                        @foreach($topCategories as $cat)
                            <a class="dropdown-item" href="{{ route('storefront.products.category', $cat->slug) }}">{{ $cat->name }}</a>
                        @endforeach
                    </div>
                </li>
                <li class="nav-item"><a class="nav-link" href="{{ route('storefront.about') }}">About Us</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('storefront.contact') }}">Contact Us</a></li>
            </ul>

            {{-- Search --}}
            <form class="form-inline mx-auto my-2 my-lg-0" method="GET" action="{{ route('storefront.products.index') }}" style="max-width:350px; width:100%;">
                <div class="input-group w-100 search-box">
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-0 bg-light px-3" placeholder="Search products…">
                    <div class="input-group-append">
                        <button class="btn btn-light bg-light border-0 text-muted px-3" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </form>

            <ul class="navbar-nav ml-auto align-items-center">
                @php $cartCount = array_sum(array_column(session('storefront_cart', []), 'quantity')); @endphp
                <li class="nav-item mr-2">
                    <a class="nav-link position-relative" href="{{ route('storefront.cart.index') }}">
                        <i class="fas fa-shopping-cart fa-lg"></i>
                        @if($cartCount > 0)
                            <span class="cart-badge position-absolute" style="top: -2px; right: 0px;">{{ $cartCount }}</span>
                        @endif
                    </a>
                </li>
                @auth
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-primary btn-sm px-3 ml-2" href="{{ route('admin.dashboard') }}" style="border-radius: 50px;">
                        <i class="fas fa-cog mr-1"></i> Admin
                    </a>
                </li>
                @endauth
            </ul>
        </div>
    </div>
</nav>

<main class="flex-grow-1">
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
</main>

{{-- Footer --}}
<footer class="mt-auto pt-5 pb-4" style="background:#1a202c; color:rgba(255,255,255,.6); font-size:.85rem; border-top: 4px solid #3182ce;">
    <div class="container">
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <h5 class="text-white font-weight-bold mb-3"><i class="fas fa-store mr-2 text-primary"></i>{{ \Modules\Auth\Models\Setting::getValue('app_name', config('app.name')) }}</h5>
                <p>Your one-stop shop for premium products. Delivering excellence and reliability straight to your doorstep.</p>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="text-white text-uppercase font-weight-bold mb-3" style="letter-spacing: 1px;">Quick Links</h6>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('storefront.about') }}" class="text-reset text-decoration-none hover-white">About Us</a></li>
                    <li class="mb-2"><a href="{{ route('storefront.contact') }}" class="text-reset text-decoration-none hover-white">Contact Us</a></li>
                    <li class="mb-2"><a href="{{ route('storefront.products.index') }}" class="text-reset text-decoration-none hover-white">Shop Now</a></li>
                </ul>
            </div>
            <div class="col-md-4 mb-3">
                <h6 class="text-white text-uppercase font-weight-bold mb-3" style="letter-spacing: 1px;">Contact</h6>
                <p class="mb-1"><i class="fas fa-envelope mr-2"></i>support@example.com</p>
                <p class="mb-1"><i class="fas fa-phone mr-2"></i>+1 (555) 123-4567</p>
            </div>
        </div>
        <hr style="border-color: rgba(255,255,255,.1);">
        <div class="text-center mt-3 text-muted">
            <p class="mb-0">
                &copy; {{ date('Y') }} {{ \Modules\Auth\Models\Setting::getValue('app_name', config('app.name')) }}. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

@stack('scripts')
</body>
</html>
