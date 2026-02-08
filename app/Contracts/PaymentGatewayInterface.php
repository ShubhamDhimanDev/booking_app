<?php

namespace App\Contracts;

use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;

/**
 * Payment Gateway Interface
 *
 * All payment gateway implementations must implement this interface
 * to ensure consistent behavior across different payment providers.
 *
 * Supported Gateways: Razorpay, Stripe, PayPal, etc.
 */
interface PaymentGatewayInterface
{
    /**
     * Get the gateway identifier
     *
     * @return string (e.g., 'razorpay', 'stripe', 'paypal')
     */
    public function getGatewayName(): string;

    /**
     * Create a customer in the payment gateway
     *
     * @param Organization $organization
     * @return string Customer ID from gateway
     */
    public function createCustomer(Organization $organization): string;

    /**
     * Create or retrieve a subscription plan in the payment gateway
     *
     * @param SubscriptionPlan $plan
     * @param string $billingCycle ('monthly' or 'yearly')
     * @return string Plan ID from gateway
     */
    public function createOrGetPlan(SubscriptionPlan $plan, string $billingCycle): string;

    /**
     * Create a subscription in the payment gateway
     *
     * @param Organization $organization
     * @param SubscriptionPlan $plan
     * @param string $billingCycle
     * @param array $options Additional gateway-specific options
     * @return array ['subscription_id' => string, 'customer_id' => string, 'status' => string, ...]
     */
    public function createSubscription(
        Organization $organization,
        SubscriptionPlan $plan,
        string $billingCycle,
        array $options = []
    ): array;

    /**
     * Update a subscription (change plan, billing cycle, etc.)
     *
     * @param Subscription $subscription
     * @param SubscriptionPlan $newPlan
     * @param array $options
     * @return array Updated subscription data
     */
    public function updateSubscription(
        Subscription $subscription,
        SubscriptionPlan $newPlan,
        array $options = []
    ): array;

    /**
     * Cancel a subscription
     *
     * @param Subscription $subscription
     * @param bool $cancelAtPeriodEnd If true, cancel at end of billing period
     * @return array Cancellation response
     */
    public function cancelSubscription(Subscription $subscription, bool $cancelAtPeriodEnd = false): array;

    /**
     * Resume a cancelled subscription
     *
     * @param Subscription $subscription
     * @return array Resume response
     */
    public function resumeSubscription(Subscription $subscription): array;

    /**
     * Fetch subscription details from gateway
     *
     * @param string $gatewaySubscriptionId
     * @return array Subscription data from gateway
     */
    public function getSubscription(string $gatewaySubscriptionId): array;

    /**
     * Verify webhook signature
     *
     * @param string $payload Raw webhook payload
     * @param string $signature Signature from webhook header
     * @param string $secret Webhook secret
     * @return bool
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool;

    /**
     * Parse webhook payload and extract event data
     *
     * @param array $payload
     * @return array ['event' => string, 'data' => array]
     */
    public function parseWebhookEvent(array $payload): array;

    /**
     * Create a payment intent or order for one-time payments
     *
     * @param Organization $organization
     * @param float $amount
     * @param string $currency
     * @param array $metadata
     * @return array ['order_id' => string, 'amount' => float, ...]
     */
    public function createPaymentIntent(
        Organization $organization,
        float $amount,
        string $currency = 'INR',
        array $metadata = []
    ): array;

    /**
     * Refund a payment
     *
     * @param string $gatewayPaymentId
     * @param float $amount
     * @param string $reason
     * @return array Refund response
     */
    public function refundPayment(string $gatewayPaymentId, float $amount, string $reason = ''): array;

    /**
     * Get payment details from gateway
     *
     * @param string $gatewayPaymentId
     * @return array Payment data
     */
    public function getPayment(string $gatewayPaymentId): array;
}
