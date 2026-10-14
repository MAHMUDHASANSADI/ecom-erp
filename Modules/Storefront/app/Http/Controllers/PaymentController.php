<?php

namespace Modules\Storefront\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Modules\Auth\Models\ActivityLog;
use Modules\Storefront\Events\OrderPlaced;
use Modules\Storefront\Models\Order;
use Modules\Storefront\Repositories\Contracts\OrderRepositoryInterface;
use Raziul\Sslcommerz\Facades\Sslcommerz;

class PaymentController extends Controller
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders
    ) {}

    // -------------------------------------------------------------------------
    // Initiate SSLCommerz payment — called from CheckoutController
    // -------------------------------------------------------------------------

    public function initiate(Order $order): RedirectResponse
    {
        $order->load('items.product');

        $transactionId = 'ORD-'.$order->id.'-'.time();

        $response = Sslcommerz::setOrder(
            amount: $order->total,
            invoiceId: $transactionId,
            invoiceDescription: 'Order #'.$order->id.' — '.$order->items->count().' item(s)'
        )
            ->setCustomer(
                name: $order->customer_name,
                email: $order->email,
                phone: '01700000000',  // guest checkout — no phone field in v1
                address: $order->address
            )
            ->setShippingInfo(
                itemsCount: $order->items->count(),
                address: $order->address
            )
            ->setExtraData(['value_a' => (string) $order->id])
            ->makePayment();

        if (! $response->success()) {
            $this->orders->updateStatus($order, 'payment_failed');

            return redirect()->route('storefront.checkout.index')
                ->with('error', 'Payment gateway is unavailable. Please try again or choose Cash on Delivery.');
        }

        // Store the transaction ID we sent so we can verify it in callbacks
        $order->update(['transaction_id' => $transactionId]);

        return redirect()->away($response->gatewayPageURL());
    }

    // -------------------------------------------------------------------------
    // Success callback — SSLCommerz redirects here after successful payment
    // -------------------------------------------------------------------------

    public function success(Request $request): View|RedirectResponse
    {
        $order = $this->resolveOrder($request->value_a);

        if (! $order) {
            return redirect()->route('storefront.products.index')
                ->with('error', 'Order not found.');
        }

        // Validate transaction server-side — prevents fraudulent callbacks
        $isValid = Sslcommerz::validatePayment(
            data: $request->all(),
            transactionId: $order->transaction_id,
            amount: $order->total
        );

        if ($isValid) {
            $order->update([
                'payment_status' => 'VALID',
                'status' => 'processing',
                'transaction_id' => $request->tran_id ?? $order->transaction_id,
                'meta' => array_merge((array) $order->meta, [
                    'val_id' => $request->val_id ?? null,
                    'bank_tran_id' => $request->bank_tran_id ?? null,
                    'card_type' => $request->card_type ?? null,
                    'store_amount' => $request->store_amount ?? null,
                ]),
            ]);

            ActivityLog::record(
                subject: $order,
                description: 'payment_received',
                causerId: null,
                properties: [
                    'tran_id' => $request->tran_id,
                    'amount' => $request->amount,
                    'card_type' => $request->card_type ?? 'unknown',
                ]
            );

            // Fire order confirmation email — cart was already cleared in CheckoutController
            OrderPlaced::dispatch($order->fresh('items.product'));

            return redirect()->route('storefront.checkout.confirmation', $order);
        }

        // Validation failed — could be a tampered callback
        $order->update(['payment_status' => 'INVALID', 'status' => 'payment_failed']);

        return redirect()->route('storefront.payment.failed', $order);
    }

    // -------------------------------------------------------------------------
    // Fail callback — payment declined or gateway error
    // -------------------------------------------------------------------------

    public function fail(Request $request): RedirectResponse
    {
        $order = $this->resolveOrder($request->value_a);

        if ($order) {
            $order->update([
                'payment_status' => 'FAILED',
                'status' => 'payment_failed',
            ]);
        }

        return redirect()->route('storefront.payment.failed', $order ?? 0);
    }

    // -------------------------------------------------------------------------
    // Cancel callback — customer clicked cancel on the gateway page
    // -------------------------------------------------------------------------

    public function cancel(Request $request): RedirectResponse
    {
        $order = $this->resolveOrder($request->value_a);

        if ($order) {
            $order->update([
                'payment_status' => 'CANCELLED',
                'status' => 'payment_failed',
            ]);
        }

        return redirect()->route('storefront.cart.index')
            ->with('info', 'Payment cancelled. Your cart has been restored — you can try again.');
    }

    // -------------------------------------------------------------------------
    // IPN — server-to-server background notification from SSLCommerz
    // -------------------------------------------------------------------------

    public function ipn(Request $request): Response
    {
        $order = $this->resolveOrder($request->value_a);

        if (! $order) {
            return response('Order not found', 404);
        }

        if ($request->status === 'VALID') {
            $isValid = Sslcommerz::validatePayment(
                data: $request->all(),
                transactionId: $order->transaction_id,
                amount: $order->total
            );

            // Only update if still pending — avoids overwriting a manual fix
            if ($isValid && $order->status === 'payment_pending') {
                $order->update([
                    'payment_status' => 'VALID',
                    'status' => 'processing',
                    'transaction_id' => $request->tran_id ?? $order->transaction_id,
                ]);
            }
        }

        return response('IPN received', 200);
    }

    // -------------------------------------------------------------------------
    // Failed payment page — with retry and switch-to-COD options
    // -------------------------------------------------------------------------

    public function failed(Order $order): View
    {
        return view('storefront::checkout.payment-failed', compact('order'));
    }

    // -------------------------------------------------------------------------
    // Retry — re-initiate SSLCommerz for a failed order
    // -------------------------------------------------------------------------

    public function retry(Order $order): RedirectResponse
    {
        // Only allow retry on payment_failed orders that used sslcommerz
        if ($order->payment_method !== 'sslcommerz') {
            return redirect()->route('storefront.products.index');
        }

        $order->update(['status' => 'payment_pending', 'payment_status' => null]);

        return $this->initiate($order);
    }

    // -------------------------------------------------------------------------
    // Switch to COD — convert a failed sslcommerz order to cash on delivery
    // -------------------------------------------------------------------------

    public function switchToCod(Order $order): RedirectResponse
    {
        $order->update([
            'payment_method' => 'cod',
            'payment_status' => null,
            'status' => 'pending',
        ]);

        // Fire order placed email now that it's confirmed as COD
        OrderPlaced::dispatch($order->fresh('items.product'));

        return redirect()->route('storefront.checkout.confirmation', $order)
            ->with('success', 'Order confirmed as Cash on Delivery.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function resolveOrder(mixed $valueA): ?Order
    {
        $orderId = (int) $valueA;

        return $orderId > 0 ? Order::find($orderId) : null;
    }
}
