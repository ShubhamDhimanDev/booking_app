<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentGatewayManager;
use App\Services\PayUService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

/**
 * Payment pages and gateway confirmations for store orders. The customer-facing routes are
 * signed URLs (guests have no account); the PayU callback/webhook are verified by hash.
 */
class OrderPaymentController extends Controller
{
    public function __construct(protected OrderService $orders)
    {
    }

    /** Hands the buyer to the gateway: Razorpay checkout popup, or an auto-submitted PayU form. */
    public function pay(Request $request, Country $cmsCountry, string $orderNumber)
    {
        $order = $this->order($cmsCountry, $orderNumber);

        if ($order->isPaid()) {
            return redirect($this->thankYouUrl($order));
        }

        $payment = $order->payments()->where('status', 'pending')->latest('id')->first();
        abort_unless($payment, 404);

        return view('store.pay', [
            'country' => $cmsCountry,
            'order' => $order,
            'payment' => $payment,
            'gateway' => $payment->metadata['initiate'] ?? [],
        ]);
    }

    /** Razorpay: the browser posts the signed result of the popup here. */
    public function razorpayVerify(Request $request, Country $cmsCountry, string $orderNumber, PaymentGatewayManager $gateways)
    {
        $order = $this->order($cmsCountry, $orderNumber);

        $data = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $payment = $order->payments()->where('provider', 'razorpay')->where('gateway_order_id', $data['razorpay_order_id'])->latest('id')->first();

        if ($payment && $gateways->getGateway('razorpay')->verifyPayment($data)) {
            $this->orders->markPaid($order, 'razorpay', $data['razorpay_payment_id'], $data);

            return redirect($this->thankYouUrl($order));
        }

        $this->orders->markFailed($order, 'Razorpay signature check failed');

        return redirect(URL::signedRoute('order.failed', [$cmsCountry->slug, $order->order_number]));
    }

    /** PayU browser redirect (surl/furl). Cross-site POST, so there is no session here. */
    public function payuCallback(Request $request)
    {
        $payload = $request->all();
        $order = Order::where('order_number', (string) ($payload['udf1'] ?? ''))->with('country')->first();

        if (! $order) {
            Log::error('PayU order callback: unknown order', ['udf1' => $payload['udf1'] ?? null]);
            abort(404);
        }

        $result = $this->confirmPayu($order, $payload);

        if (! $result['ok']) {
            $this->orders->markFailed($order, $result['reason']);
        }

        return redirect($result['ok'] ? $this->thankYouUrl($order) : URL::signedRoute('order.failed', [$order->country->slug, $order->order_number]));
    }

    /** PayU server-to-server notification, called from PaymentController::payuWebhook for order payloads. */
    public function payuWebhook(array $payload)
    {
        $order = Order::where('order_number', (string) ($payload['udf1'] ?? ''))->first();
        if (! $order) {
            return response()->json(['status' => 'order_not_found'], 404);
        }

        $result = $this->confirmPayu($order, $payload);

        return $result['ok']
            ? response()->json(['status' => 'success'])
            : response()->json(['status' => 'rejected', 'reason' => $result['reason']], 400);
    }

    public function thankYou(Request $request, Country $cmsCountry, string $orderNumber)
    {
        $order = $this->order($cmsCountry, $orderNumber)->load(['items', 'freeSessionInvite']);

        return view('store.thankyou', ['country' => $cmsCountry, 'order' => $order]);
    }

    public function failed(Request $request, Country $cmsCountry, string $orderNumber)
    {
        return view('store.failed', ['country' => $cmsCountry, 'order' => $this->order($cmsCountry, $orderNumber)]);
    }

    /**
     * Accepts a PayU payload only when the hash is valid, the status is success, the txnid is one we issued
     * for this order and the amount equals the order total. Marking paid is idempotent.
     *
     * @return array{ok: bool, reason: ?string}
     */
    protected function confirmPayu(Order $order, array $payload): array
    {
        if ($order->isPaid()) {
            return ['ok' => true, 'reason' => null];
        }

        $txnId = (string) ($payload['txnid'] ?? $payload['merchantTransactionId'] ?? '');
        $payment = $txnId !== ''
            ? $order->payments()->where('provider', 'payu')->where('gateway_order_id', $txnId)->first()
            : null;

        if (! $payment) {
            return ['ok' => false, 'reason' => 'Unknown transaction'];
        }
        if (! app(PayUService::class)->verifyHash($payload, strict: true)) {
            return ['ok' => false, 'reason' => 'Invalid signature'];
        }
        if (strtolower((string) ($payload['status'] ?? '')) !== 'success') {
            return ['ok' => false, 'reason' => $payload['error_Message'] ?? $payload['field9'] ?? 'Payment was not successful'];
        }
        if (abs((float) ($payload['amount'] ?? 0) - (float) $order->total) > 0.009) {
            Log::warning('PayU order amount mismatch', ['order' => $order->order_number, 'got' => $payload['amount'] ?? null]);

            return ['ok' => false, 'reason' => 'Amount mismatch'];
        }

        $this->orders->markPaid($order, 'payu', $payload['mihpayid'] ?? null, $payload);

        return ['ok' => true, 'reason' => null];
    }

    protected function order(Country $country, string $orderNumber): Order
    {
        return Order::where('order_number', $orderNumber)->where('country_id', $country->id)->firstOrFail();
    }

    protected function thankYouUrl(Order $order): string
    {
        $order->loadMissing('country');

        return URL::signedRoute('order.thankyou', [$order->country->slug, $order->order_number]);
    }
}
