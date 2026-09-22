<?php

namespace Modules\Storefront\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Auth\Models\Lookup;
use Modules\Storefront\Models\Order;
use Modules\Storefront\Repositories\Contracts\OrderRepositoryInterface;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders
    ) {}

    /**
     * Admin orders list — requires view_orders permission (applied at route level).
     */
    public function index(Request $request): View
    {
        $filters = array_filter(
            $request->only(['status', 'search']),
            fn ($v) => $v !== '' && $v !== null
        );

        $orders = $this->orders->paginate($filters, 25);
        $statuses = Lookup::ofType('order_status');
        $pendingCount = $this->orders->countByStatus('pending');

        return view('storefront::orders.index', compact('orders', 'statuses', 'filters', 'pendingCount'));
    }

    /**
     * Admin order detail.
     */
    public function show(Order $order): View
    {
        $order->load('items.product', 'sale');
        $statuses = Lookup::ofType('order_status');

        return view('storefront::orders.show', compact('order', 'statuses'));
    }

    /**
     * Update order status — requires manage_orders permission.
     */
    public function update(Request $request, Order $order): RedirectResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'exists:lookups,code,type,order_status'],
        ]);

        $this->orders->updateStatus($order, $request->status);

        return back()->with('success', "Order #{$order->id} status updated to \"{$request->status}\".");
    }
}
