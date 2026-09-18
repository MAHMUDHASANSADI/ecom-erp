@extends('auth::layouts.admin')

@section('title', 'Receipt — ' . $sale->sale_number)
@section('page-title', 'Receipt')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.pos.index') }}">POS</a></li>
    <li class="breadcrumb-item active">Receipt</li>
@endsection

@push('styles')
<style>
    @media print {
        .no-print, .main-header, .main-sidebar, .main-footer, .content-header { display: none !important; }
        .content-wrapper { margin: 0 !important; padding: 0 !important; }
        .receipt-card { box-shadow: none !important; border: none !important; }
        body { background: white !important; }
    }
    .receipt-card { max-width: 420px; margin: 0 auto; font-family: 'Courier New', monospace; }
    .receipt-divider { border-top: 1px dashed #999; margin: 8px 0; }
</style>
@endpush

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">

        {{-- Action buttons --}}
        <div class="d-flex justify-content-between mb-3 no-print">
            <a href="{{ route('admin.pos.index') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> New Sale
            </a>
            <div>
                <a href="{{ route('admin.pos.daily-summary') }}" class="btn btn-outline-info mr-2">
                    <i class="fas fa-chart-bar mr-1"></i> Daily Summary
                </a>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print mr-1"></i> Print Receipt
                </button>
            </div>
        </div>

        {{-- Receipt --}}
        <div class="card receipt-card">
            <div class="card-body p-3">

                {{-- Header --}}
                <div class="text-center mb-2">
                    <h5 class="mb-0 font-weight-bold">{{ $appName }}</h5>
                    <small class="text-muted">Point of Sale Receipt</small>
                </div>

                <div class="receipt-divider"></div>

                {{-- Sale info --}}
                <div class="d-flex justify-content-between" style="font-size:.85rem;">
                    <span>Receipt #:</span>
                    <strong>{{ $sale->sale_number }}</strong>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.85rem;">
                    <span>Date:</span>
                    <span>{{ $sale->created_at->format('d M Y H:i') }}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.85rem;">
                    <span>Cashier:</span>
                    <span>{{ $sale->cashier->name ?? '—' }}</span>
                </div>
                <div class="d-flex justify-content-between" style="font-size:.85rem;">
                    <span>Payment:</span>
                    <span class="text-capitalize">{{ $sale->payment_method }}</span>
                </div>

                <div class="receipt-divider"></div>

                {{-- Items --}}
                <table class="table table-borderless table-sm mb-0" style="font-size:.85rem;">
                    <thead>
                        <tr>
                            <th class="pl-0">Item</th>
                            <th class="text-center" style="width:50px;">Qty</th>
                            <th class="text-right pr-0" style="width:80px;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sale->items as $item)
                        <tr>
                            <td class="pl-0">
                                {{ $item->product->name ?? '—' }}
                                <br><small class="text-muted">{{ $currencySymbol }}{{ number_format($item->unit_price, 2) }} each</small>
                            </td>
                            <td class="text-center">{{ $item->quantity }}</td>
                            <td class="text-right pr-0 font-weight-bold">
                                {{ $currencySymbol }}{{ number_format($item->line_total, 2) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="receipt-divider"></div>

                {{-- Total --}}
                <div class="d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold">TOTAL</span>
                    <span class="font-weight-bold h5 mb-0 text-success">
                        {{ $currencySymbol }}{{ number_format($sale->total, 2) }}
                    </span>
                </div>

                <div class="receipt-divider"></div>

                {{-- Footer --}}
                <div class="text-center mt-2" style="font-size:.8rem; color:#666;">
                    {{ $receiptFooter }}
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
