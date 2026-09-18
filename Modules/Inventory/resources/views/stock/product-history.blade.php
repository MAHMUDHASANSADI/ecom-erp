@extends('auth::layouts.admin')

@section('title', 'Stock History — ' . $product->name)
@section('page-title', $product->name . ' — Stock History')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
    <li class="breadcrumb-item active">{{ $product->name }}</li>
@endsection

@section('content')
<div class="row mb-3">
    {{-- Product summary card --}}
    <div class="col-md-4">
        <div class="card card-outline card-primary">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 text-muted">Product</dt>
                    <dd class="col-7 font-weight-bold">{{ $product->name }}</dd>

                    <dt class="col-5 text-muted">SKU</dt>
                    <dd class="col-7"><code>{{ $product->sku }}</code></dd>

                    <dt class="col-5 text-muted">Category</dt>
                    <dd class="col-7">{{ $product->category->name ?? '—' }}</dd>

                    <dt class="col-5 text-muted">Current Stock</dt>
                    <dd class="col-7">
                        <span class="badge badge-{{ $currentStock <= 0 ? 'danger' : ($currentStock <= 5 ? 'warning' : 'success') }} px-3"
                              style="font-size:1rem;">
                            {{ $currentStock }}
                        </span>
                    </dd>
                </dl>
            </div>
            <div class="card-footer">
                @can('manage_inventory')
                <a href="{{ route('admin.inventory.adjust', ['product_id' => $product->id]) }}"
                   class="btn btn-sm btn-success btn-block">
                    <i class="fas fa-edit mr-1"></i> Adjust Stock
                </a>
                @endcan
                <a href="{{ route('admin.products.show', $product) }}"
                   class="btn btn-sm btn-outline-secondary btn-block mt-1">
                    <i class="fas fa-box mr-1"></i> View Product
                </a>
            </div>
        </div>
    </div>

    {{-- Movement history --}}
    <div class="col-md-8">
        <div class="card card-outline card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history mr-2"></i>Movement History
                    <span class="badge badge-secondary ml-1">{{ $movements->total() }}</span>
                </h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Date / Time</th>
                            <th>Reason</th>
                            <th class="text-center">Change</th>
                            <th>Notes</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                        <tr>
                            <td class="text-nowrap">{{ $movement->created_at->format('d M Y H:i') }}</td>
                            <td><span class="badge badge-secondary">{{ $movement->reason }}</span></td>
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
                            <td colspan="5" class="text-center text-muted py-4">
                                No movements recorded yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($movements->hasPages())
            <div class="card-footer">
                {{ $movements->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
