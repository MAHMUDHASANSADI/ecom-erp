<?php

namespace Modules\Storefront\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Catalog\Models\Product;

/**
 * Session-based cart. Cart structure stored in session:
 *
 * cart => [
 *   {product_id} => [
 *     'product_id'  => int,
 *     'name'        => string,
 *     'price'       => float,
 *     'image_url'   => string|null,
 *     'quantity'    => int,
 *     'stock'       => int,
 *   ],
 *   ...
 * ]
 */
class CartController extends Controller
{
    private const SESSION_KEY = 'storefront_cart';

    public function index(): View
    {
        $cart = session(self::SESSION_KEY, []);

        return view('storefront::cart.index', compact('cart'));
    }

    public function add(Request $request): RedirectResponse|JsonResponse
    {
        $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $product = Product::active()->findOrFail($request->product_id);
        $currentStock = $product->current_stock;

        $cart = session(self::SESSION_KEY, []);
        $key = (string) $product->id;

        $existingQty = $cart[$key]['quantity'] ?? 0;
        $newQty = $existingQty + (int) $request->quantity;

        if ($newQty > $currentStock) {
            $newQty = $currentStock;
        }

        if ($newQty <= 0) {
            return back()->with('error', 'This product is out of stock.');
        }

        $cart[$key] = [
            'product_id' => $product->id,
            'name' => $product->name,
            'price' => (float) $product->price,
            'image_url' => $product->image_url,
            'quantity' => $newQty,
            'stock' => $currentStock,
        ];

        session([self::SESSION_KEY => $cart]);

        return back()->with('success', "\"{$product->name}\" added to cart.");
    }

    public function update(Request $request, int $productId): RedirectResponse
    {
        $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $cart = session(self::SESSION_KEY, []);
        $key = (string) $productId;

        if ((int) $request->quantity <= 0) {
            unset($cart[$key]);
        } elseif (isset($cart[$key])) {
            $cart[$key]['quantity'] = min((int) $request->quantity, $cart[$key]['stock']);
        }

        session([self::SESSION_KEY => $cart]);

        return back()->with('success', 'Cart updated.');
    }

    public function remove(int $productId): RedirectResponse
    {
        $cart = session(self::SESSION_KEY, []);
        unset($cart[(string) $productId]);
        session([self::SESSION_KEY => $cart]);

        return back()->with('success', 'Item removed from cart.');
    }

    public function clear(): RedirectResponse
    {
        session()->forget(self::SESSION_KEY);

        return back()->with('success', 'Cart cleared.');
    }

    // -------------------------------------------------------------------------
    // Static helper for other controllers
    // -------------------------------------------------------------------------

    public static function getCart(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public static function clearCart(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}
