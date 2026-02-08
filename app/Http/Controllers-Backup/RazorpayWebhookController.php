<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * RazorpayWebhookController
 *
 * Handles webhook events from Razorpay
 */
class RazorpayWebhookController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Handle subscription webhooks
     */
    public function handleSubscriptionWebhook(Request $request)
    {
        try {
            // Verify webhook signature
            $this->verifyWebhookSignature($request);

            $payload = $request->all();
            $event = $payload['event'] ?? null;

            if (!$event) {
                return response()->json(['status' => 'error', 'message' => 'Missing event'], 400);
            }

            $subscriptionData = $payload['payload']['subscription']['entity'] ?? null;

            if (!$subscriptionData) {
                return response()->json(['status' => 'error', 'message' => 'Missing subscription data'], 400);
            }

            // Find local subscription
            $subscription = Subscription::where('razorpay_subscription_id', $subscriptionData['id'])->first();

            if (!$subscription) {
                Log::error('Subscription not found for webhook', [
                    'event' => $event,
                    'razorpay_subscription_id' => $subscriptionData['id']
                ]);
                return response()->json(['status' => 'error', 'message' => 'Subscription not found'], 404);
            }

            // Handle different events
            switch ($event) {
                case 'subscription.activated':
                    $this->handleActivated($subscription);
                    break;

                case 'subscription.charged':
                    $this->handleCharged($subscription, $payload['payload']['payment']['entity'] ?? []);
                    break;

                case 'subscription.pending':
                    $this->handlePending($subscription);
                    break;

                case 'subscription.halted':
                    $this->handleHalted($subscription);
                    break;

                case 'subscription.cancelled':
                    $this->handleCancelled($subscription);
                    break;

                case 'subscription.completed':
                    $this->handleCompleted($subscription);
                    break;

                case 'subscription.paused':
                    $this->handlePaused($subscription);
                    break;

                case 'subscription.resumed':
                    $this->handleResumed($subscription);
                    break;

                default:
                    Log::warning('Unknown webhook event', ['event' => $event]);
            }

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify Razorpay webhook signature
     */
    protected function verifyWebhookSignature(Request $request): void
    {
        $webhookSecret = config('services.razorpay.webhook_secret');

        if (!$webhookSecret) {
            Log::warning('Webhook secret not configured');
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

    /**
     * Handle subscription.activated event
     */
    protected function handleActivated(Subscription $subscription): void
    {
        $this->subscriptionService->activateSubscription($subscription);

        Log::info('Webhook: Subscription activated', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription.charged event
     */
    protected function handleCharged(Subscription $subscription, array $paymentData): void
    {
        $this->subscriptionService->recordSubscriptionPayment($subscription, $paymentData);

        Log::info('Webhook: Subscription charged', [
            'subscription_id' => $subscription->id,
            'payment_id' => $paymentData['id'] ?? null,
        ]);
    }

    /**
     * Handle subscription.pending event
     */
    protected function handlePending(Subscription $subscription): void
    {
        $subscription->update(['status' => Subscription::STATUS_PAST_DUE]);

        Log::info('Webhook: Subscription pending', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription.halted event
     */
    protected function handleHalted(Subscription $subscription): void
    {
        $subscription->update(['status' => Subscription::STATUS_PAST_DUE]);
        $subscription->organization->update(['status' => 'suspended']);

        Log::info('Webhook: Subscription halted', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription.cancelled event
     */
    protected function handleCancelled(Subscription $subscription): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);
        $subscription->organization->update(['status' => 'cancelled']);

        Log::info('Webhook: Subscription cancelled', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription.completed event
     */
    protected function handleCompleted(Subscription $subscription): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_EXPIRED,
            'ends_at' => now(),
        ]);

        Log::info('Webhook: Subscription completed', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription.paused event
     */
    protected function handlePaused(Subscription $subscription): void
    {
        $subscription->update(['status' => Subscription::STATUS_PAST_DUE]);
        $subscription->organization->update(['status' => 'suspended']);

        Log::info('Webhook: Subscription paused', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription.resumed event
     */
    protected function handleResumed(Subscription $subscription): void
    {
        $subscription->update(['status' => Subscription::STATUS_ACTIVE]);
        $subscription->organization->update(['status' => 'active']);

        Log::info('Webhook: Subscription resumed', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle payment webhooks
     */
    public function handlePaymentWebhook(Request $request)
    {
        try {
            $this->verifyWebhookSignature($request);

            $payload = $request->all();
            $event = $payload['event'] ?? null;

            Log::info('Payment webhook received', [
                'event' => $event,
            ]);

            // Handle payment-specific events if needed
            // Most subscription payments are handled via subscription.charged

            return response()->json(['status' => 'success']);

        } catch (\Exception $e) {
            Log::error('Payment webhook handling failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
