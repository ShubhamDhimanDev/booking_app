<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\Invoice;
use App\Models\PaymentAttempt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SubscriptionService
 *
 * Handles all subscription-related operations with payment gateways
 * Gateway-agnostic implementation using PaymentGatewayInterface
 */
class SubscriptionService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(?string $gatewayName = null)
    {
        // Use specified gateway or organization's default or system default
        $gatewayName = $gatewayName ?? PaymentGatewayFactory::getDefaultGateway();
        $this->gateway = PaymentGatewayFactory::make($gatewayName);
    }

    /**
     * Set the payment gateway
     */
    public function setGateway(string $gatewayName): self
    {
        $this->gateway = PaymentGatewayFactory::make($gatewayName);
        return $this;
    }

    /**
     * Create a new subscription for an organization
     */
    public function createSubscription(
        Organization $org,
        SubscriptionPlan $plan,
        string $billingCycle = 'monthly',
        ?string $gatewayName = null
    ): Subscription {
        DB::beginTransaction();

        try {
            // Use organization's preferred gateway or specified gateway
            $gatewayName = $gatewayName ?? $org->default_payment_gateway ?? PaymentGatewayFactory::getDefaultGateway();
            $this->setGateway($gatewayName);

            // Determine amount based on billing cycle
            $amount = $billingCycle === 'yearly'
                ? $plan->price_yearly
                : $plan->price_monthly;

            // Create subscription via payment gateway
            $gatewayResponse = $this->gateway->createSubscription(
                $org,
                $plan,
                $billingCycle,
                ['start_at' => now()->addDays(14)->timestamp] // 14-day trial
            );

            // Update organization with gateway customer ID
            $gatewayCustomerIds = $org->gateway_customer_ids ?? [];
            $gatewayCustomerIds[$gatewayName] = $gatewayResponse['customer_id'];
            $org->update(['gateway_customer_ids' => $gatewayCustomerIds]);

            // Create local subscription record
            $subscription = Subscription::create([
                'organization_id' => $org->id,
                'subscription_plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'status' => Subscription::STATUS_TRIALING,
                'gateway' => $gatewayName,
                'gateway_subscription_id' => $gatewayResponse['subscription_id'],
                'gateway_customer_id' => $gatewayResponse['customer_id'],
                'gateway_plan_id' => $gatewayResponse['plan_id'],
                'gateway_metadata' => $gatewayResponse['metadata'] ?? null,
                'trial_ends_at' => now()->addDays(14),
                'current_period_start' => now(),
                'current_period_end' => $billingCycle === 'yearly' ? now()->addYear() : now()->addMonth(),
                'amount' => $amount,
                'currency' => 'INR',
                'usage_reset_at' => now()->addMonth()->startOfMonth(),
            ]);

            // Update organization
            $org->update([
                'current_plan_id' => $plan->id,
                'status' => 'trial',
                'trial_ends_at' => now()->addDays(14),
            ]);

            DB::commit();

            Log::info('Subscription created', [
                'organization_id' => $org->id,
                'subscription_id' => $subscription->id,
                'gateway' => $gatewayName,
                'gateway_subscription_id' => $gatewayResponse['subscription_id'],
            ]);

            return $subscription;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create subscription', [
                'organization_id' => $org->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create Razorpay customer
     */
    protected function createRazorpayCustomer(Organization $org)
    {
        return $this->razorpay->customer->create([
            'name' => $org->name,
            'email' => $org->billing_email ?: $org->contact_email,
            'contact' => $org->contact_phone,
            'notes' => [
                'organization_id' => $org->id,
                'created_at' => now()->toDateTimeString(),
            ]
        ]);
    }

    /**
     * Change subscription plan (upgrade/downgrade)
     */
    public function changePlan(Subscription $subscription, SubscriptionPlan $newPlan, string $billingCycle = null): Subscription
    {
        DB::beginTransaction();

        try {
            $billingCycle = $billingCycle ?: $subscription->billing_cycle;
            $this->setGateway($subscription->gateway);

            // Determine new amount
            $amount = $billingCycle === 'yearly'
                ? $newPlan->price_yearly
                : $newPlan->price_monthly;

            // Update via payment gateway
            $gatewayResponse = $this->gateway->updateSubscription($subscription, $newPlan, [
                'billing_cycle' => $billingCycle,
            ]);

            // Update local subscription
            $subscription->update([
                'subscription_plan_id' => $newPlan->id,
                'billing_cycle' => $billingCycle,
                'gateway_plan_id' => $gatewayResponse['plan_id'],
                'amount' => $amount,
            ]);

            // Update organization plan
            $subscription->organization->update([
                'current_plan_id' => $newPlan->id,
            ]);

            DB::commit();

            Log::info('Subscription plan changed', [
                'subscription_id' => $subscription->id,
                'old_plan' => $subscription->plan->name,
                'new_plan' => $newPlan->name,
            ]);

            return $subscription->fresh();

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to change subscription plan', [
                'subscription_id' => $subscription->id,
                'new_plan_id' => $newPlan->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancel subscription
     */
    public function cancelSubscription(Subscription $subscription, bool $immediately = false, string $reason = null): void
    {
        DB::beginTransaction();

        try {
            $this->setGateway($subscription->gateway);

            // Cancel via payment gateway
            $gatewayResponse = $this->gateway->cancelSubscription($subscription, !$immediately);

            if ($immediately) {
                $subscription->update([
                    'status' => Subscription::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                    'ends_at' => now(),
                    'cancellation_reason' => $reason,
                    'cancelled_by_user_id' => auth()->id(),
                ]);

                $subscription->organization->update(['status' => 'cancelled']);
            } else {
                // Cancel at period end (grace period)
                $subscription->update([
                    'status' => Subscription::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                    'ends_at' => $subscription->current_period_end,
                    'cancellation_reason' => $reason,
                    'cancelled_by_user_id' => auth()->id(),
                ]);
            }

            DB::commit();

            Log::info('Subscription cancelled', [
                'subscription_id' => $subscription->id,
                'immediately' => $immediately,
                'ends_at' => $subscription->ends_at,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Resume cancelled subscription
     */
    public function resumeSubscription(Subscription $subscription): void
    {
        DB::beginTransaction();

        try {
            // Only allow resuming if still on grace period
            if (!$subscription->isOnGracePeriod()) {
                throw new \Exception('Cannot resume subscription that has already ended');
            }

            $this->setGateway($subscription->gateway);

            // Resume via payment gateway
            $gatewayResponse = $this->gateway->resumeSubscription($subscription);

            $subscription->update([
                'status' => Subscription::STATUS_ACTIVE,
                'cancelled_at' => null,
                'ends_at' => null,
                'cancellation_reason' => null,
                'cancelled_by_user_id' => null,
            ]);

            $subscription->organization->update(['status' => 'active']);

            DB::commit();

            Log::info('Subscription resumed', [
                'subscription_id' => $subscription->id,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to resume subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle subscription activation (webhook)
     */
    public function activateSubscription(Subscription $subscription): void
    {
        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
        ]);

        $subscription->organization->update([
            'status' => 'active',
            'subscribed_at' => now(),
        ]);

        Log::info('Subscription activated', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription payment (create invoice)
     */
    public function recordSubscriptionPayment(Subscription $subscription, array $paymentData): Invoice
    {
        DB::beginTransaction();

        try {
            // Create invoice
            $invoice = Invoice::create([
                'organization_id' => $subscription->organization_id,
                'subscription_id' => $subscription->id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => now(),
                'line_items' => [
                    [
                        'description' => $subscription->plan->name . ' - ' . ucfirst($subscription->billing_cycle),
                        'quantity' => 1,
                        'unit_price' => $subscription->amount,
                        'total' => $subscription->amount,
                    ]
                ],
                'subtotal' => $subscription->amount,
                'tax_amount' => $subscription->amount * 0.18, // 18% GST
                'discount_amount' => 0,
                'total_amount' => $subscription->amount * 1.18,
                'currency' => $subscription->currency,
                'status' => Invoice::STATUS_PAID,
                'paid_at' => now(),
                'gateway' => $subscription->gateway,
                'gateway_payment_id' => $paymentData['id'] ?? null,
                'gateway_order_id' => $paymentData['order_id'] ?? null,
                'payment_metadata' => $paymentData,
                'billing_address' => $subscription->organization->billing_address,
                'billing_email' => $subscription->organization->billing_email ?: $subscription->organization->contact_email,
                'gstin' => $subscription->organization->gstin,
            ]);

            // Record payment attempt
            PaymentAttempt::create([
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'gateway' => $subscription->gateway,
                'gateway_payment_id' => $paymentData['id'] ?? null,
                'amount' => $paymentData['amount'] / 100, // Razorpay amount is in paise
                'currency' => $paymentData['currency'] ?? 'INR',
                'status' => PaymentAttempt::STATUS_CAPTURED,
                'payment_method' => $paymentData['method'] ?? null,
                'card_last4' => $paymentData['card']['last4'] ?? null,
                'card_network' => $paymentData['card']['network'] ?? null,
                'gateway_response' => $paymentData,
            ]);

            // Update subscription period
            $newPeriodEnd = $subscription->billing_cycle === 'yearly'
                ? $subscription->current_period_end->addYear()
                : $subscription->current_period_end->addMonth();

            $subscription->update([
                'current_period_start' => $subscription->current_period_end,
                'current_period_end' => $newPeriodEnd,
            ]);

            DB::commit();

            Log::info('Subscription payment recorded', [
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_amount,
            ]);

            return $invoice;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record subscription payment', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Update payment method
     */
    public function updatePaymentMethod(Subscription $subscription, string $paymentMethodId): void
    {
        // Implementation depends on Razorpay's payment method storage
        // This is a placeholder for future implementation

        Log::info('Payment method update requested', [
            'subscription_id' => $subscription->id,
            'payment_method_id' => $paymentMethodId,
        ]);
    }
}
