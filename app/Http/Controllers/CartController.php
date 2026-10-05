<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesStoreCountry;
use App\Models\Country;
use App\Models\Product;
use App\Models\PromoCode;
use App\Services\CartService;
use App\Services\PricingService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ResolvesStoreCountry;

    public function __construct(protected CartService $carts, protected PricingService $pricing)
    {
    }

    /** `/{country}/cart` */
    public function show(Request $request, Country $cmsCountry)
    {
        $country = $this->storeCountry($request, $cmsCountry);
        $cart = $this->carts->current($country);

        return view('store.cart', [
            'country' => $country,
            'summary' => $cart ? $this->pricing->summarize($cart) : null,
        ]);
    }

    public function add(Request $request, Country $cmsCountry)
    {
        $country = $this->storeCountry($request, $cmsCountry);
        $data = $request->validate([
            'product_id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1|max:' . CartService::MAX_QTY,
        ]);

        $product = Product::soldIn($country)
            ->with(['prices' => fn ($q) => $q->where('country_id', $country->id)])
            ->findOrFail($data['product_id']);
        $cart = $this->carts->current($country, true);
        $qty = (int) ($data['quantity'] ?? 1);
        $notice = $this->carts->add($cart, $product, $qty);

        if ($request->expectsJson()) {
            return response()->json(['count' => $this->carts->count($country), 'notice' => $notice]);
        }

        $price = $product->priceFor($country);

        return redirect()->route('cart.show', $country->slug)->with(
            ($notice ? ['alert_type' => 'warning', 'alert_message' => $notice]
                     : ['alert_type' => 'success', 'alert_message' => $product->name . ' added to your cart.'])
            + ['track_add' => ['id' => $product->sku ?: $product->id, 'name' => $product->name, 'price' => (float) $price->sale_price, 'qty' => $qty, 'currency' => $price->currency]]
        );
    }

    public function update(Request $request, Country $cmsCountry)
    {
        $country = $this->storeCountry($request, $cmsCountry);
        $data = $request->validate([
            'quantities' => 'required|array',
            'quantities.*' => 'integer|min:0|max:' . CartService::MAX_QTY,
        ]);

        $cart = $this->carts->current($country);
        abort_unless($cart, 404);

        $notices = [];
        foreach ($cart->items()->with('product')->get() as $item) {
            if ($item->product && isset($data['quantities'][$item->product_id])) {
                $notice = $this->carts->setQuantity($cart, $item->product, (int) $data['quantities'][$item->product_id]);
                if ($notice) {
                    $notices[] = $notice;
                }
            } elseif (! $item->product) {
                $item->delete();
            }
        }

        return redirect()->route('cart.show', $country->slug)->with(
            $notices ? ['alert_type' => 'warning', 'alert_message' => implode(' ', $notices)]
                     : ['alert_type' => 'success', 'alert_message' => 'Cart updated.']
        );
    }

    public function remove(Request $request, Country $cmsCountry, int $productId)
    {
        $country = $this->storeCountry($request, $cmsCountry);

        if ($cart = $this->carts->current($country)) {
            $this->carts->remove($cart, $productId);
        }

        return redirect()->route('cart.show', $country->slug);
    }

    public function applyPromo(Request $request, Country $cmsCountry)
    {
        $country = $this->storeCountry($request, $cmsCountry);
        $request->validate(['promo_code' => 'required|string|max:50']);

        $cart = $this->carts->current($country);
        abort_unless($cart, 404);

        $code = strtoupper(trim($request->promo_code));
        $summary = $this->pricing->summarize($cart);
        [$promo, , $error] = $this->pricing->promo($code, $summary['subtotal']);

        if (! PromoCode::where('code', $code)->exists()) {
            $error = 'Invalid promo code.';
        }

        if ($error) {
            return back()->with(['alert_type' => 'danger', 'alert_message' => $error]);
        }

        $cart->update(['promo_code' => $promo->code]);

        return redirect()->route('cart.show', $country->slug)->with(['alert_type' => 'success', 'alert_message' => 'Promo code ' . $promo->code . ' applied.']);
    }

    public function removePromo(Request $request, Country $cmsCountry)
    {
        $country = $this->storeCountry($request, $cmsCountry);

        $this->carts->current($country)?->update(['promo_code' => null]);

        return redirect()->route('cart.show', $country->slug);
    }
}
