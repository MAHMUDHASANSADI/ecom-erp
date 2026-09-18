@extends('auth::layouts.admin')

@section('title', 'Movement History')
@section('page-title', 'Stock Movement History')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
    <li class="breadcrumb-item active">Movement History</li>
@endsection

@section('content')

{{-- Filter bar --}}
<div class="card card-outline card-secondary mb-3">
    <div class="card-body py-2">
        <form method="GET" action="{{ route('admin.inventory.history') }}" class="form-inline flex-wrap">

            <select name="product_id" class="form-control form-control-sm mr-2 mb-1" style="min-width:200px;">
                <option value="">All products</option>
                @foreach($products as $id => $name)
                    <option value="{{ $id }}" {{ ($filters['product_id'] ?? '') == $id ? 'selected' : '' }}>
                        {{ $name }}
                    </option>
                @endforeach
            </select>

            <select name="reason" class="form-control form-control-sm mr-2 mb-1" style="min-width:160px;">
                <option value="">All reasons</option>
                @foreach($reasons as $reason)
                    <option value="{{ $reason->code }}" {{ ($filters['reason'] ?? '') === $reason->code ? 'selected' : '' }}>
                        {{ $reason->label }}
                    </option>
                @endforeach
            </select>

            <div class="input-group input-group-sm mr-2 mb-1" style="max-width:160px;">
                <div class="input-group-prepend"><span class="input-group-text">From</span></div>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="form-control">
            </div>

            <div class="input-group input-group-sm mr-2 mb-1" style="max-width:160px;">
                <div class="input-group-prepend"><span class="input-group-text">To</span></div>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="form-control">
            </div>

            <button type="submit" class="btn btn-sm btn-primary mr-1 mb-1">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            <a href="{{ route('admin.inventory.history') }}" class="btn btn-sm btn-secondary mb-1">
                <i class="fas fa-times mr-1"></i> Clear
            </a>
        </form>
    </div>
</div>

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-history mr-2"></i>Movement Log
            <span class="badge badge-secondary ml-1">{{ $movements->total() }}</span>
        </h3>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="thead-light">
                <tr>
                    <th>Date / Time</th>
                    <th>Product</th>
                    <th>Reason</th>
                    <th class="text-center">Change</th>
                    <th>Notes</th>
                    <th>By</th>
                </tr>
            </thead>
            <tbody>
                @forelse($movements as $movement)
                <tr>
                    <td class="text-nowrap">
                        <span title="{{ $movement->created_at }}">
                            {{ $movement->created_at->format('d M Y H:i') }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('admin.inventory.product-history', $movement->product) }}"
                           class="font-weight-bold text-dark">
                            {{ $movement->product->name ?? '—' }}
                        </a>
                        <br><small class="text-muted"><code>{{ $movement->product->sku ?? '' }}</code></small>
                    </td>
                    <td>
                        <span class="badge badge-secondary">{{ $movement->reason }}</span>
                    </td>
                    <td class="text-center">
                        @if($movement->quantity_change > 0)
                            <span class="badge badge-success px-2">
                                <i class="fas fa-arrow-up mr-1"></i>+{{ $movement->quantity_change }}
                            </span>
                        @else
                            <span class="badge badge-danger px-2">
                                <i class="fas fa-arrow-down mr-1"></i>{{ $movement->quantity_change }}
                            </span>
                        @endif
                    </td>
                    <td>{{ $movement->notes ?: '—' }}</td>
                    <td>{{ $movement->creator->name ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center text-muted py-4">No movements found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($movements->hasPages())
    <div class="card-footer">
        {{ $movements->appends($filters)->links() }}
    </div>
    @endif
</div>
@endsection
