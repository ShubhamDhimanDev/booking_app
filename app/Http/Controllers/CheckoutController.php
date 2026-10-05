<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ResolvesStoreCountry;
use App\Models\Country;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use RuntimeException;

class CheckoutController extends Controller
{
    use ResolvesStoreCountry;

    public function __construct(
        protected CartService $carts,
        protected PricingService $pricing,
        protected OrderService $orders
    ) {
    }

    /** `/{country}/checkout` */
    public function show(Request $request, Country $cmsCountry)
    {
        $country = $this->storeCountry($request, $cmsCountry);
        $summary = $this->readySummary($country);

        if (! $summary) {
            return redirect()->route('cart.show', $country->slug)->with([
                'alert_type' => 'warning',
                'alert_message' => 'Please review your cart before checking out.',
            ]);
        }

        return view('store.checkout', [
            'country' => $country,
            'summary' => $summary,
            'user' => $request->user(),
        ]);
    }

    public function place(Request $request, Country $cmsCountry)
    {
        $country = $this->storeCountry($request, $cmsCountry);

        $customer = $request->validate([
            'customer_name' => 'required|string|max:120',
            'customer_email' => 'required|email|max:190',
            'customer_phone' => 'required|string|max:32',
            'address_line1' => 'required|string|max:190',
            'address_line2' => 'nullable|string|max:190',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'required|string|max:20',
            'notes' => 'nullable|string|max:1000',
        ]);

        $cart = $this->carts->current($country);
        if (! $cart || ! $this->readySummary($country)) {
            return redirect()->route('cart.show', $country->slug)->with([
                'alert_type' => 'warning',
                'alert_message' => 'Please review your cart before checking out.',
            ]);
        }

        try {
            $order = $this->orders->createFromCart($cart, $customer, $request->user()?->id, [
                'utm_source' => session('tracking_utm_source'),
                'utm_medium' => session('tracking_utm_medium'),
                'utm_campaign' => session('tracking_utm_campaign'),
            ]);

            // A 100% promo discount needs no gateway.
            if ((float) $order->total <= 0) {
                $this->orders->markPaid($order, 'free', 'FREE_' . $order->order_number);

                return redirect(URL::signedRoute('order.thankyou', [$country->slug, $order->order_number]));
            }

            $this->orders->initiatePayment($order);
        } catch (RuntimeException $e) {
            return back()->withInput()->with(['alert_type' => 'danger', 'alert_message' => $e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('Checkout failed: ' . $e->getMessage(), ['exception' => $e]);

            return back()->withInput()->with(['alert_type' => 'danger', 'alert_message' => 'We could not start your payment. Please try again.']);
        }

        return redirect(URL::temporarySignedRoute('order.pay', now()->addHours(2), [$country->slug, $order->order_number]));
    }

    /** The cart summary, or null when it is empty or contains unavailable items. */
    protected function readySummary(Country $country): ?array
    {
        $cart = $this->carts->current($country);
        if (! $cart) {
            return null;
        }

        $summary = $this->pricing->summarize($cart);

        return $summary['count'] > 0 && ! $summary['has_issues'] ? $summary : null;
    }
}
