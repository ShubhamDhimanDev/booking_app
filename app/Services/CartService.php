<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Country;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * One cart per visitor per country. Guests are identified by a session token;
 * logged-in users by user_id. A guest cart is merged into the user's cart the
 * first time the user touches the cart after logging in.
 */
class CartService
{
    public const MAX_QTY = 10;

    public function __construct(protected Request $request)
    {
    }

    public function current(Country $country, bool $create = false): ?Cart
    {
        $user = $this->request->user();
        $session = $this->request->hasSession() ? $this->request->session() : null;
        $token = $session?->get('cart_token');

        $guest = $token
            ? Cart::where('session_token', $token)->whereNull('user_id')->where('country_id', $country->id)->first()
            : null;

        if ($user) {
            $cart = Cart::where('user_id', $user->id)->where('country_id', $country->id)->first();

            if ($guest && $cart) {
                $this->merge($guest, $cart);
            } elseif ($guest) {
                $guest->update(['user_id' => $user->id, 'session_token' => null]);
                $cart = $guest;
            }

            return $cart ?? ($create ? Cart::create(['user_id' => $user->id, 'country_id' => $country->id]) : null);
        }

        if ($guest || ! $create) {
            return $guest;
        }

        $token = $token ?: Str::random(40);
        $session->put('cart_token', $token);

        return Cart::create(['session_token' => $token, 'country_id' => $country->id]);
    }

    /** Number of units in the visitor's cart (never creates a cart). */
    public function count(Country $country): int
    {
        $cart = $this->current($country);

        return $cart ? (int) $cart->items()->sum('quantity') : 0;
    }

    /** Returns an error/notice message when the quantity had to be reduced, otherwise null. */
    public function add(Cart $cart, Product $product, int $qty = 1): ?string
    {
        $item = $cart->items()->where('product_id', $product->id)->first();

        return $this->setQuantity($cart, $product, ($item?->quantity ?? 0) + max(1, $qty));
    }

    /** Sets the line quantity (capped by MAX_QTY and tracked stock). A quantity of 0 removes the line. */
    public function setQuantity(Cart $cart, Product $product, int $qty): ?string
    {
        if ($qty <= 0) {
            $cart->items()->where('product_id', $product->id)->delete();

            return null;
        }

        $notice = null;
        $limit = self::MAX_QTY;
        if ($product->track_stock) {
            $limit = min($limit, $product->stock_qty);
        }

        if ($limit <= 0) {
            return $product->name . ' is out of stock.';
        }
        if ($qty > $limit) {
            $qty = $limit;
            $notice = 'Only ' . $limit . ' of ' . $product->name . ' can be ordered.';
        }

        $cart->items()->updateOrCreate(['product_id' => $product->id], ['quantity' => $qty]);

        return $notice;
    }

    public function remove(Cart $cart, int $productId): void
    {
        $cart->items()->where('product_id', $productId)->delete();
    }

    public function clear(Cart $cart): void
    {
        $cart->items()->delete();
        $cart->update(['promo_code' => null]);
    }

    protected function merge(Cart $from, Cart $into): void
    {
        foreach ($from->items()->with('product')->get() as $item) {
            if ($item->product) {
                $existing = $into->items()->where('product_id', $item->product_id)->first();
                $this->setQuantity($into, $item->product, ($existing?->quantity ?? 0) + $item->quantity);
            }
        }
        if (! $into->promo_code && $from->promo_code) {
            $into->update(['promo_code' => $from->promo_code]);
        }
        $from->delete();
    }
}
