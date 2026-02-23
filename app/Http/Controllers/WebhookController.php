<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use App\Services\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * WebhookController
 *
 * Handles webhook events from payment gateways (Razorpay, Stripe, PayPal)
 */
class WebhookController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle subscription webhooks (gateway-agnostic)
     */
    public function handleSubscriptionWebhook(Request $request, string $gateway)
    {
        try {
            // Verify webhook signature
            $this->verifyWebhookSignature($request, $gateway);

            $payload = $request->all();
            $event = $this->extractEvent($payload, $gateway);

            if (!$event) {
                return response()->json(['status' => 'error', 'message' => 'Missing event'], 400);
            }

            $subscriptionId = $this->extractSubscriptionId($payload, $gateway);

            if (!$subscriptionId) {
                return response()->json(['status' => 'error', 'message' => 'Missing subscription data'], 400);
            }

            // Find local subscription
            $subscription = Subscription::where('gateway_subscription_id', $subscriptionId)
                ->where('gateway', $gateway)
                ->first();

            if (!$subscription) {
                Log::error('Subscription not found for webhook', [
                    'event' => $event,
                    'gateway' => $gateway,
                    'subscription_id' => $subscriptionId,
                ]);
                return response()->json(['status' => 'error', 'message' => 'Subscription not found'], 404);
            }

            // Handle different events
            $this->handleEvent($event, $subscription, $payload, $gateway);

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Webhook handling failed', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify webhook signature based on gateway
     */
    protected function verifyWebhookSignature(Request $request, string $gateway): void
    {
        if ($gateway === 'razorpay') {
            $webhookSecret = config('services.razorpay.webhook_secret');

            if (!$webhookSecret) {
                Log::warning('Razorpay webhook secret not configured');
                return; // Skip verification in development
            }

            $signature = $request->header('X-Razorpay-Signature');

            if (!$signature) {
                throw new \Exception('Missing webhook signature');
            }

            $body = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $body, $webhookSecret);

            if (!hash_equals($expectedSignature, $signature)) {
                throw new \Exception('Invalid webhook signature');
            }
        }
        // Add other gateway signature verification here
    }

    /**
     * Extract event name from payload based on gateway
     */
    protected function extractEvent(array $payload, string $gateway): ?string
    {
        return match($gateway) {
            'razorpay' => $payload['event'] ?? null,
            'stripe' => $payload['type'] ?? null,
            default => null,
        };
    }

    /**
     * Extract subscription ID from payload based on gateway
     */
    protected function extractSubscriptionId(array $payload, string $gateway): ?string
    {
        return match($gateway) {
            'razorpay' => $payload['payload']['subscription']['entity']['id'] ?? null,
            'stripe' => $payload['data']['object']['id'] ?? null,
            default => null,
        };
    }

    /**
     * Handle webhook event
     */
    protected function handleEvent(string $event, Subscription $subscription, array $payload, string $gateway): void
    {
        // Normalize event names across gateways
        $normalizedEvent = $this->normalizeEvent($event, $gateway);

        switch ($normalizedEvent) {
            case 'activated':
                $this->handleActivated($subscription);
                break;

            case 'charged':
                $paymentData = $this->extractPaymentData($payload, $gateway);
                $this->handleCharged($subscription, $paymentData);
                break;

            case 'pending':
            case 'past_due':
                $this->handlePending($subscription);
                break;

            case 'cancelled':
                $this->handleCancelled($subscription);
                break;

            case 'expired':
                $this->handleExpired($subscription);
                break;

            default:
                Log::info('Unhandled webhook event', [
                    'event' => $event,
                    'normalized' => $normalizedEvent,
                    'gateway' => $gateway,
                ]);
        }
    }

    /**
     * Normalize event names across gateways
     */
    protected function normalizeEvent(string $event, string $gateway): string
    {
        return match($gateway) {
            'razorpay' => match($event) {
                'subscription.activated' => 'activated',
                'subscription.charged' => 'charged',
                'subscription.pending', 'subscription.halted' => 'pending',
                'subscription.cancelled' => 'cancelled',
                'subscription.completed' => 'expired',
                default => $event,
            },
            'stripe' => match($event) {
                'customer.subscription.created', 'customer.subscription.updated' => 'activated',
                'invoice.payment_succeeded' => 'charged',
                'invoice.payment_failed' => 'pending',
                'customer.subscription.deleted' => 'cancelled',
                default => $event,
            },
            default => $event,
        };
    }

    /**
     * Extract payment data from payload
     */
    protected function extractPaymentData(array $payload, string $gateway): array
    {
        return match($gateway) {
            'razorpay' => $payload['payload']['payment']['entity'] ?? [],
            'stripe' => $payload['data']['object'] ?? [],
            default => [],
        };
    }

    /**
     * Handle subscription activated
     */
    protected function handleActivated(Subscription $subscription): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        $subscription->organization->update([
            'status' => 'active',
            'subscribed_at' => now(),
        ]);

        Log::info('Webhook: Subscription activated', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription charged
     */
    protected function handleCharged(Subscription $subscription, array $paymentData): void
    {
        // Record payment and create invoice
        // Implementation depends on your invoice system

        Log::info('Webhook: Subscription charged', [
            'subscription_id' => $subscription->id,
            'payment_id' => $paymentData['id'] ?? null,
        ]);
    }

    /**
     * Handle subscription pending/past due
     */
    protected function handlePending(Subscription $subscription): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_PAST_DUE,
        ]);

        Log::info('Webhook: Subscription past due', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription cancelled
     */
    protected function handleCancelled(Subscription $subscription): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'ends_at' => now(),
        ]);

        $subscription->organization->update([
            'status' => 'cancelled',
        ]);

        Log::info('Webhook: Subscription cancelled', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription expired
     */
    protected function handleExpired(Subscription $subscription): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_EXPIRED,
            'ends_at' => now(),
        ]);

        Log::info('Webhook: Subscription expired', [
            'subscription_id' => $subscription->id,
        ]);
    }
}
