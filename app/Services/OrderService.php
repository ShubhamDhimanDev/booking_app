<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\FollowUpInvite;
use App\Models\Order;
use App\Jobs\ProcessOrderRefundJob;
use App\Models\Payment;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\Refund;
use App\Models\User;
use App\Notifications\NewOrderAdminNotification;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderRefundedNotification;
use App\Notifications\OrderShippedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use RuntimeException;

class OrderService
{
    /** Days a free-session link stays valid after purchase. */
    public const FREE_SESSION_DAYS = 30;

    public function __construct(protected PricingService $pricing, protected PaymentGatewayManager $gateways)
    {
    }

    /**
     * Turns the cart into a pending order. Prices are recalculated here; nothing the browser sends is trusted.
     *
     * @param  array  $customer  customer_name, customer_email, customer_phone, address_line1, address_line2, city, state, postal_code
     */
    public function createFromCart(Cart $cart, array $customer, ?int $userId, array $utm = []): Order
    {
        $summary = $this->pricing->summarize($cart);

        if ($summary['count'] === 0 || $summary['has_issues']) {
            throw new RuntimeException('Your cart has items that are unavailable. Please review your cart.');
        }

        return DB::transaction(function () use ($cart, $summary, $customer, $userId, $utm) {
            // Only one open order per cart: abandon earlier attempts.
            Order::where('cart_id', $cart->id)->where('status', 'pending_payment')
                ->update(['status' => 'cancelled']);

            $order = Order::create($customer + [
                'order_number' => Order::generateNumber(),
                'status' => 'pending_payment',
                'payment_status' => 'pending',
                'user_id' => $userId,
                'cart_id' => $cart->id,
                'country_id' => $cart->country_id,
                'currency' => $summary['currency'],
                'subtotal' => $summary['subtotal'],
                'discount' => $summary['discount'],
                'total' => $summary['total'],
                'promo_code' => $summary['promo']?->code,
                'address_country' => $cart->country->name,
            ] + $utm);

            foreach ($summary['lines'] as $line) {
                $order->items()->create([
                    'product_id' => $line['product']->id,
                    'name' => $line['product']->name,
                    'sku' => $line['product']->sku,
                    'mrp' => $line['mrp'],
                    'price' => $line['price'],
                    'quantity' => $line['quantity'],
                    'line_total' => $line['line_total'],
                ]);
            }

            return $order;
        });
    }

    /**
     * Creates the gateway order and a pending payment row. INR orders use the active gateway,
     * everything else goes through PayU (Razorpay is configured for INR only).
     */
    public function initiatePayment(Order $order): Payment
    {
        $gateway = $order->currency === 'INR'
            ? $this->gateways->getActiveGateway()
            : $this->gateways->getGateway('payu');

        $data = [
            'amount' => (float) $order->total,
            'currency' => $order->currency,
            'receipt' => $order->order_number,
            // PayU echoes udf1 back in the callback and webhook; it identifies the order there.
            'booking_id' => $order->order_number,
            'product_info' => 'Order ' . $order->order_number,
            'first_name' => $this->payuSafe($order->customer_name, 60) ?: 'Customer',
            'email' => $order->customer_email,
            'phone' => preg_replace('/\D+/', '', (string) $order->customer_phone),
            'txn_id' => 'ORD' . $order->id . 'T' . time() . random_int(100, 999),
            'promo_code' => $order->promo_code,
        ];

        $response = $gateway->initiatePayment($data);

        if (empty($response['success'])) {
            throw new RuntimeException($response['error'] ?? 'Could not start the payment.');
        }

        if ($gateway->getName() === 'payu') {
            // The stock PayU service points at the booking callback; orders have their own.
            $response['surl'] = $response['furl'] = route('order.payu.callback');
        }

        return $order->payments()->create([
            'user_id' => $order->user_id,
            'provider' => $gateway->getName(),
            'gateway_order_id' => $response['order_id'] ?? $response['txnid'],
            'status' => 'pending',
            'amount' => $order->total,
            'currency' => $order->currency,
            'promo_code' => $order->promo_code,
            'metadata' => ['initiate' => $response],
        ]);
    }

    /**
     * Marks an order paid exactly once (callback, webhook and verify can all race). Returns true only
     * for the call that made the transition; stock, promo usage, the free-session link and the emails
     * are handled by that call alone.
     */
    public function markPaid(Order $order, string $provider, ?string $transactionId, array $meta = []): bool
    {
        $transitioned = DB::transaction(function () use ($order, $provider, $transactionId, $meta) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->isPaid()) {
                return false;
            }

            $payment = $locked->payments()->where('provider', $provider)->latest('id')->first()
                ?? $locked->payments()->latest('id')->first()
                ?? new Payment(['order_id' => $locked->id]);

            $payment->fill([
                'user_id' => $locked->user_id,
                'provider' => $provider,
                'transaction_id' => $transactionId,
                'status' => 'success',
                'amount' => $locked->total,
                'currency' => $locked->currency,
                'promo_code' => $locked->promo_code,
                'metadata' => array_merge($payment->metadata ?? [], ['confirmation' => $meta]),
            ])->save();

            $locked->update(['payment_status' => 'paid', 'status' => 'paid', 'paid_at' => now()]);

            foreach ($locked->items as $item) {
                if (! $item->product_id) {
                    continue;
                }
                $product = Product::lockForUpdate()->find($item->product_id);
                if ($product && $product->track_stock) {
                    if ($product->stock_qty < $item->quantity) {
                        Log::warning('Order oversold', ['order' => $locked->order_number, 'product_id' => $product->id]);
                    }
                    $product->update(['stock_qty' => max(0, $product->stock_qty - $item->quantity)]);
                }
            }

            if ($locked->promo_code) {
                PromoCode::where('code', $locked->promo_code)->first()?->incrementUsage();
            }
            if ($locked->cart_id) {
                Cart::whereKey($locked->cart_id)->delete();
            }

            return true;
        });

        if ($transitioned) {
            $order->refresh();
            $this->afterPaid($order);
        }

        return $transitioned;
    }

    public function markFailed(Order $order, ?string $reason = null): void
    {
        if ($order->isPaid()) {
            return;
        }

        $order->update(['payment_status' => 'failed', 'status' => 'failed']);
        $order->payments()->where('status', 'pending')->update(['status' => 'failed']);
        Log::warning('Order payment failed', ['order' => $order->order_number, 'reason' => $reason]);
    }

    /** One free-session invite per paid order, if any purchased product grants one. */
    public function createFreeSessionInvite(Order $order): ?FollowUpInvite
    {
        if ($order->freeSessionInvite()->exists()) {
            return $order->freeSessionInvite;
        }

        $order->loadMissing(['items.product', 'country']);

        $eventId = null;
        foreach ($order->items as $item) {
            if ($item->product && $item->product->grants_free_session) {
                $eventId = $item->product->free_session_event_id ?: $order->country?->free_session_event_id;
                if ($eventId) {
                    break;
                }
            }
        }

        if (! $eventId) {
            return null;
        }

        return FollowUpInvite::create([
            'order_id' => $order->id,
            'source' => 'order',
            'event_id' => $eventId,
            'user_id' => $order->user_id,
            'email' => $order->customer_email,
            'custom_price' => 0,
            'token' => FollowUpInvite::generateUniqueToken(),
            'status' => 'pending',
            'expires_at' => now()->addDays(self::FREE_SESSION_DAYS),
            'sent_at' => now(),
        ]);
    }

    /** Cancels an unused free-session link, e.g. when the order is refunded or cancelled. */
    public function cancelFreeSessionInvite(Order $order): void
    {
        $order->freeSessionInvite()->where('status', 'pending')->update(['status' => 'expired']);
    }

    /** Allowed fulfilment steps, keyed by current status. */
    public const TRANSITIONS = [
        'paid' => ['processing', 'shipped'],
        'processing' => ['shipped'],
        'shipped' => ['delivered'],
    ];

    /** Moves a paid order along the fulfilment path. Shipping records the carrier and emails the customer. */
    public function advance(Order $order, string $to, ?string $carrier = null, ?string $trackingNumber = null): void
    {
        if (! in_array($to, self::TRANSITIONS[$order->status] ?? [], true)) {
            throw new RuntimeException("An order that is {$order->status} cannot be set to {$to}.");
        }

        $changes = ['status' => $to];
        if ($to === 'shipped') {
            $changes += ['carrier' => $carrier, 'tracking_number' => $trackingNumber, 'shipped_at' => now()];
        }
        if ($to === 'delivered') {
            $changes['delivered_at'] = now();
        }
        $order->update($changes);

        if ($to === 'shipped') {
            try {
                Notification::route('mail', [$order->customer_email => $order->customer_name])
                    ->notify(new OrderShippedNotification($order));
            } catch (\Throwable $e) {
                Log::error('Shipped email failed: ' . $e->getMessage(), ['order' => $order->order_number]);
            }
        }
    }

    /** Cancels an order that was never paid. Paid orders must be refunded instead. */
    public function cancelUnpaid(Order $order): void
    {
        if ($order->isPaid()) {
            throw new RuntimeException('This order is paid. Refund it instead of cancelling.');
        }

        $order->update(['status' => 'cancelled']);
    }

    /**
     * Starts a full refund. Gateway payments go to a queued job; free (100% promo) orders are closed right away.
     */
    public function startRefund(Order $order, User $admin): ?Refund
    {
        $payment = $order->payments()->where('status', 'success')->latest('id')->first();

        if (! $order->isPaid() || ! $payment || ! in_array($order->status, ['paid', 'processing', 'shipped', 'delivered'], true)) {
            throw new RuntimeException('Only paid orders can be refunded.');
        }
        if (Refund::where('order_id', $order->id)->whereIn('status', ['pending', 'processing', 'completed'])->exists()) {
            throw new RuntimeException('This order already has a refund in progress or completed.');
        }

        if ((float) $order->total <= 0 || $payment->provider === 'free') {
            $this->completeRefund($order);

            return null;
        }

        $refund = Refund::create([
            'order_id' => $order->id,
            'payment_id' => $payment->id,
            'amount' => $order->total,
            'gateway_charges' => 0,
            'net_refund_amount' => $order->total,
            'status' => 'pending',
            'gateway' => $payment->provider,
            'initiated_by' => 'admin',
            'initiated_by_user_id' => $admin->id,
        ]);

        ProcessOrderRefundJob::dispatch($refund);

        return $refund;
    }

    /** Called once the gateway has refunded the money. Items that never shipped go back on the shelf. */
    public function completeRefund(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $locked = Order::whereKey($order->id)->lockForUpdate()->firstOrFail();
            if ($locked->status === 'refunded') {
                return;
            }

            if (! $locked->shipped_at) {
                foreach ($locked->items as $item) {
                    $product = $item->product_id ? Product::lockForUpdate()->find($item->product_id) : null;
                    if ($product && $product->track_stock) {
                        $product->increment('stock_qty', $item->quantity);
                    }
                }
            }

            $locked->update(['status' => 'refunded', 'payment_status' => 'refunded']);
            $locked->payments()->where('status', 'success')->update(['status' => 'refunded']);
            $this->cancelFreeSessionInvite($locked);
        });

        $order->refresh();

        try {
            Notification::route('mail', [$order->customer_email => $order->customer_name])
                ->notify(new OrderRefundedNotification($order));
        } catch (\Throwable $e) {
            Log::error('Refund email failed: ' . $e->getMessage(), ['order' => $order->order_number]);
        }
    }

    /** Re-sends the confirmation. An expired free-session link is reopened for another period; a used one is not. */
    public function resendConfirmation(Order $order): void
    {
        if (! $order->isPaid()) {
            throw new RuntimeException('Only paid orders have a confirmation to resend.');
        }

        $invite = $this->createFreeSessionInvite($order);
        if ($invite && ($invite->status === 'expired' || ($invite->status === 'pending' && $invite->isExpired()))) {
            $invite->update(['status' => 'pending', 'expires_at' => now()->addDays(self::FREE_SESSION_DAYS), 'sent_at' => now()]);
        }
        if ($invite && $invite->status !== 'pending') {
            $invite = null; // already used (or closed): don't mail a dead link
        }

        Notification::route('mail', [$order->customer_email => $order->customer_name])
            ->notify(new OrderPlacedNotification($order, $invite));
    }

    protected function afterPaid(Order $order): void
    {
        try {
            $invite = $this->createFreeSessionInvite($order);
        } catch (\Throwable $e) {
            Log::error('Free-session invite failed: ' . $e->getMessage(), ['order' => $order->order_number]);
            $invite = null;
        }

        try {
            Notification::route('mail', [$order->customer_email => $order->customer_name])
                ->notify(new OrderPlacedNotification($order, $invite));

            $admins = User::role(['admin', 'owner'])->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new NewOrderAdminNotification($order));
            }
        } catch (\Throwable $e) {
            Log::error('Order emails failed: ' . $e->getMessage(), ['order' => $order->order_number]);
        }
    }

    /** PayU hashes firstname verbatim, so keep it free of separators and symbols. */
    protected function payuSafe(string $value, int $max): string
    {
        return trim(mb_substr(preg_replace('/[^\p{L}\p{N} ]+/u', '', $value), 0, $max));
    }
}
