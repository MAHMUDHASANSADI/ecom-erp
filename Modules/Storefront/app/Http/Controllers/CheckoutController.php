<?php

namespace Modules\Storefront\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Storefront\Events\OrderPlaced;
use Modules\Storefront\Http\Requests\GuestCheckoutRequest;
use Modules\Storefront\Models\Order;
use Modules\Storefront\Repositories\Contracts\OrderRepositoryInterface;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders
    ) {}

    public function index(): View|RedirectResponse
    {
        $cart = CartController::getCart();

        if (empty($cart)) {
            return redirect()->route('storefront.cart.index')
                ->with('error', 'Your cart is empty. Add some products before checking out.');
        }

        $total = collect($cart)->sum(fn ($item) => $item['price'] * $item['quantity']);

        return view('storefront::checkout.index', compact('cart', 'total'));
    }

    public function store(GuestCheckoutRequest $request): RedirectResponse
    {
        $cart = CartController::getCart();

        if (empty($cart)) {
            return redirect()->route('storefront.products.index')
                ->with('error', 'Your cart is empty.');
        }

        $items = collect($cart)->map(fn ($item) => [
            'product_id' => $item['product_id'],
            'quantity' => $item['quantity'],
            'unit_price' => $item['price'],
        ])->values()->toArray();

        $order = $this->orders->createWithItems(
            orderData: [
                'customer_name' => $request->customer_name,
                'email' => $request->email,
                'address' => $request->address,
                'status' => 'pending',
            ],
            items: $items
        );

        // Clear cart after successful order
        CartController::clearCart();

        // Fire OrderPlaced → SendOrderConfirmationEmail (queued)
        OrderPlaced::dispatch($order);

        return redirect()->route('storefront.checkout.confirmation', $order)
            ->with('success', 'Order placed successfully! A confirmation email has been sent.');
    }

    public function confirmation(Order $order): View
    {
        $order->load('items.product');

        return view('storefront::checkout.confirmation', compact('order'));
    }
}
