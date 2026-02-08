<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay Payment Gateway Implementation
 *
 * Handles all Razorpay-specific payment operations including
 * subscriptions, webhooks, and payment processing.
 */
class RazorpayGateway implements PaymentGatewayInterface
{
    protected Api $api;
    protected string $webhookSecret;

    public function __construct()
    {
        $this->api = new Api(
            config('services.razorpay.key'),
            config('services.razorpay.secret')
        );
        $this->webhookSecret = config('services.razorpay.webhook_secret');
    }

    /**
     * {@inheritdoc}
     */
    public function getGatewayName(): string
    {
        return 'razorpay';
    }

    /**
     * {@inheritdoc}
     */
    public function createCustomer(Organization $organization): string
    {
        try {
            $customer = $this->api->customer->create([
                'name' => $organization->name,
                'email' => $organization->billing_email ?? $organization->contact_email,
                'contact' => $organization->contact_phone,
                'notes' => [
                    'organization_id' => $organization->id,
                    'slug' => $organization->slug,
                ],
            ]);

            return $customer->id;
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to create customer', [
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createOrGetPlan(SubscriptionPlan $plan, string $billingCycle): string
    {
        // Get plan ID from gateway_plan_ids JSON
        $gatewayPlanIds = $plan->gateway_plan_ids ?? [];

        if (isset($gatewayPlanIds['razorpay'][$billingCycle])) {
            return $gatewayPlanIds['razorpay'][$billingCycle];
        }

        // If plan doesn't exist, create it in Razorpay
        try {
            $amount = $billingCycle === 'monthly'
                ? $plan->price_monthly * 100 // Razorpay uses paise
                : $plan->price_yearly * 100;

            $period = $billingCycle === 'monthly' ? 'monthly' : 'yearly';
            $interval = 1;

            $razorpayPlan = $this->api->plan->create([
                'period' => $period,
                'interval' => $interval,
                'item' => [
                    'name' => $plan->name . ' Plan - ' . ucfirst($billingCycle),
                    'description' => $plan->description,
                    'amount' => $amount,
                    'currency' => 'INR',
                ],
                'notes' => [
                    'plan_id' => $plan->id,
                    'billing_cycle' => $billingCycle,
                ],
            ]);

            // Update plan with Razorpay plan ID
            $gatewayPlanIds['razorpay'][$billingCycle] = $razorpayPlan->id;
            $plan->update(['gateway_plan_ids' => $gatewayPlanIds]);

            return $razorpayPlan->id;
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to create plan', [
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function createSubscription(
        Organization $organization,
        SubscriptionPlan $plan,
        string $billingCycle,
        array $options = []
    ): array {
        try {
            // Get or create customer
            $gatewayCustomerIds = $organization->gateway_customer_ids ?? [];
            $customerId = $gatewayCustomerIds['razorpay'] ?? null;

            if (!$customerId) {
                $customerId = $this->createCustomer($organization);
                $gatewayCustomerIds['razorpay'] = $customerId;
                $organization->update(['gateway_customer_ids' => $gatewayCustomerIds]);
            }

            // Get or create plan
            $planId = $this->createOrGetPlan($plan, $billingCycle);

            // Create subscription
            $subscriptionData = [
                'plan_id' => $planId,
                'customer_id' => $customerId,
                'total_count' => 0, // Infinite billing cycles
                'quantity' => 1,
                'start_at' => $options['start_at'] ?? now()->addDays(14)->timestamp, // 14-day trial
                'notes' => [
                    'organization_id' => $organization->id,
                    'plan_id' => $plan->id,
                    'billing_cycle' => $billingCycle,
                ],
            ];

            // Add addons if provided
            if (isset($options['addons'])) {
                $subscriptionData['addons'] = $options['addons'];
            }

            $subscription = $this->api->subscription->create($subscriptionData);

            return [
                'subscription_id' => $subscription->id,
                'customer_id' => $customerId,
                'plan_id' => $planId,
                'status' => $subscription->status,
                'start_at' => $subscription->start_at,
                'current_start' => $subscription->current_start ?? null,
                'current_end' => $subscription->current_end ?? null,
                'metadata' => [
                    'charge_at' => $subscription->charge_at ?? null,
                    'short_url' => $subscription->short_url ?? null,
                ],
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to create subscription', [
                'organization_id' => $organization->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function updateSubscription(
        Subscription $subscription,
        SubscriptionPlan $newPlan,
        array $options = []
    ): array {
        try {
            $newPlanId = $this->createOrGetPlan($newPlan, $subscription->billing_cycle);

            $updateData = [
                'plan_id' => $newPlanId,
                'quantity' => 1,
            ];

            // Add schedule change if specified
            if (isset($options['schedule_change_at'])) {
                $updateData['schedule_change_at'] = $options['schedule_change_at'];
            }

            $razorpaySubscription = $this->api->subscription->fetch($subscription->gateway_subscription_id);
            $razorpaySubscription = $razorpaySubscription->update($updateData);

            return [
                'subscription_id' => $razorpaySubscription->id,
                'plan_id' => $newPlanId,
                'status' => $razorpaySubscription->status,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to update subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function cancelSubscription(Subscription $subscription, bool $cancelAtPeriodEnd = false): array
    {
        try {
            $razorpaySubscription = $this->api->subscription->fetch($subscription->gateway_subscription_id);

            if ($cancelAtPeriodEnd) {
                $razorpaySubscription = $razorpaySubscription->update([
                    'cancel_at_cycle_end' => 1,
                ]);
            } else {
                $razorpaySubscription->cancel();
            }

            return [
                'subscription_id' => $razorpaySubscription->id,
                'status' => $razorpaySubscription->status,
                'cancelled_at' => now()->timestamp,
                'ends_at' => $razorpaySubscription->ended_at ?? null,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to cancel subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resumeSubscription(Subscription $subscription): array
    {
        try {
            $razorpaySubscription = $this->api->subscription->fetch($subscription->gateway_subscription_id);
            $razorpaySubscription = $razorpaySubscription->resume();

            return [
                'subscription_id' => $razorpaySubscription->id,
                'status' => $razorpaySubscription->status,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to resume subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscription(string $gatewaySubscriptionId): array
    {
        try {
            $subscription = $this->api->subscription->fetch($gatewaySubscriptionId);

            return [
                'subscription_id' => $subscription->id,
                'plan_id' => $subscription->plan_id,
                'customer_id' => $subscription->customer_id,
                'status' => $subscription->status,
                'start_at' => $subscription->start_at,
                'current_start' => $subscription->current_start ?? null,
                'current_end' => $subscription->current_end ?? null,
                'ended_at' => $subscription->ended_at ?? null,
                'charge_at' => $subscription->charge_at ?? null,
                'paid_count' => $subscription->paid_count ?? 0,
                'remaining_count' => $subscription->remaining_count ?? 0,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to fetch subscription', [
                'gateway_subscription_id' => $gatewaySubscriptionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
    {
        try {
            $expectedSignature = hash_hmac('sha256', $payload, $secret);
            return hash_equals($expectedSignature, $signature);
        } catch (\Exception $e) {
            Log::error('Razorpay: Webhook signature verification failed', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function parseWebhookEvent(array $payload): array
    {
        return [
            'event' => $payload['event'] ?? 'unknown',
            'data' => $payload['payload'] ?? [],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function createPaymentIntent(
        Organization $organization,
        float $amount,
        string $currency = 'INR',
        array $metadata = []
    ): array {
        try {
            $order = $this->api->order->create([
                'amount' => $amount * 100, // Convert to paise
                'currency' => $currency,
                'receipt' => 'order_' . $organization->id . '_' . time(),
                'notes' => array_merge([
                    'organization_id' => $organization->id,
                ], $metadata),
            ]);

            return [
                'order_id' => $order->id,
                'amount' => $amount,
                'currency' => $currency,
                'receipt' => $order->receipt,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to create payment intent', [
                'organization_id' => $organization->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function refundPayment(string $gatewayPaymentId, float $amount, string $reason = ''): array
    {
        try {
            $refund = $this->api->payment->fetch($gatewayPaymentId)->refund([
                'amount' => $amount * 100, // Convert to paise
                'notes' => [
                    'reason' => $reason,
                ],
            ]);

            return [
                'refund_id' => $refund->id,
                'payment_id' => $gatewayPaymentId,
                'amount' => $amount,
                'status' => $refund->status,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to refund payment', [
                'gateway_payment_id' => $gatewayPaymentId,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPayment(string $gatewayPaymentId): array
    {
        try {
            $payment = $this->api->payment->fetch($gatewayPaymentId);

            return [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id ?? null,
                'amount' => $payment->amount / 100, // Convert from paise
                'currency' => $payment->currency,
                'status' => $payment->status,
                'method' => $payment->method ?? null,
                'created_at' => $payment->created_at,
            ];
        } catch (\Exception $e) {
            Log::error('Razorpay: Failed to fetch payment', [
                'gateway_payment_id' => $gatewayPaymentId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
