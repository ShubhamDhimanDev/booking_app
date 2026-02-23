<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Models\AppSetting;
use App\Services\SubscriptionService;
use App\Services\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display subscription information
     */
    public function show()
    {
        $organization = auth()->user()->organization;
        $subscription = $organization->subscription;

        $plans = null;

        // If no subscription, show available plans
        if (!$subscription) {
            $plans = SubscriptionPlan::where('is_active', true)
                ->orderBy('price_monthly')
                ->get();
        }

        return view('organization.billing.subscription', compact('subscription', 'plans'));
    }

    /**
     * Show change plan form
     */
    public function changePlan()
    {
        $organization = auth()->user()->organization;
        $currentSubscription = $organization->subscription;

        if (!$currentSubscription) {
            return redirect()->route('organization.subscription');
        }

        $plans = SubscriptionPlan::where('is_active', true)
            ->where('id', '!=', $currentSubscription->subscription_plan_id)
            ->orderBy('price_monthly')
            ->get();

        return view('organization.billing.change-plan', compact('currentSubscription', 'plans'));
    }

    /**
     * Subscribe to a plan - Create payment order and redirect to checkout
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|exists:subscription_plans,id',
            'cycle' => 'required|in:monthly,yearly',
        ]);

        $organization = auth()->user()->organization;
        $plan = SubscriptionPlan::findOrFail($validated['plan']);

        // Check if organization already has an active subscription
        if ($organization->subscription) {
            return back()->with('error', 'You already have an active subscription!');
        }

        // Get default payment gateway from system settings
        $gateway = AppSetting::get('default_payment_gateway', 'razorpay');

        $amount = $validated['cycle'] === 'monthly' ? $plan->price_monthly : $plan->price_yearly;

        try {
            // Create Razorpay order for payment
            $gatewayService = PaymentGatewayFactory::make($gateway);

            $orderData = $gatewayService->createPaymentIntent(
                $organization,
                $amount,
                'INR',
                [
                    'plan_id' => $plan->id,
                    'billing_cycle' => $validated['cycle'],
                    'type' => 'subscription_payment',
                ]
            );

            // Store order data in session for checkout page
            session()->put('subscription_checkout', [
                'order_id' => $orderData['order_id'],
                'amount' => $amount,
                'plan_id' => $plan->id,
                'cycle' => $validated['cycle'],
                'gateway' => $gateway,
            ]);

            return redirect()->route('organization.subscription.checkout');

        } catch (\Exception $e) {
            Log::error('Failed to create payment order', [
                'organization_id' => $organization->id,
                'plan_id' => $plan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to initiate payment: ' . $e->getMessage());
        }
    }

    /**
     * Show checkout page
     */
    public function checkout()
    {
        $checkoutData = session('subscription_checkout');

        if (!$checkoutData) {
            return redirect()->route('organization.subscription')
                ->with('error', 'No pending subscription order found.');
        }

        $plan = SubscriptionPlan::findOrFail($checkoutData['plan_id']);
        $organization = auth()->user()->organization;

        return view('organization.billing.checkout', [
            'plan' => $plan,
            'cycle' => $checkoutData['cycle'],
            'amount' => $checkoutData['amount'],
            'orderId' => $checkoutData['order_id'],
            'gateway' => $checkoutData['gateway'],
            'organization' => $organization,
        ]);
    }

    /**
     * Handle payment success callback
     */
    public function paymentCallback(Request $request)
    {
        $validated = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $checkoutData = session('subscription_checkout');

        if (!$checkoutData) {
            return redirect()->route('organization.subscription')
                ->with('error', 'Invalid payment session.');
        }

        try {
            // Verify payment signature
            $gateway = PaymentGatewayFactory::make($checkoutData['gateway']);

            $secret = config('services.razorpay.secret');
            $expectedSignature = hash_hmac('sha256',
                $validated['razorpay_order_id'] . '|' . $validated['razorpay_payment_id'],
                $secret
            );

            if (!hash_equals($expectedSignature, $validated['razorpay_signature'])) {
                throw new \Exception('Payment signature verification failed');
            }

            $organization = auth()->user()->organization;
            $plan = SubscriptionPlan::findOrFail($checkoutData['plan_id']);

            // Payment verified - Create subscription
            $subscription = $this->subscriptionService->createSubscription(
                $organization,
                $plan,
                $checkoutData['cycle'],
                $checkoutData['gateway']
            );

            // Since payment is already received, activate subscription immediately
            $subscription->update([
                'status' => \App\Models\Subscription::STATUS_ACTIVE,
                'trial_ends_at' => null, // No trial since payment is upfront
                'gateway_metadata' => [
                    'first_payment_id' => $validated['razorpay_payment_id'],
                    'first_order_id' => $validated['razorpay_order_id'],
                ]
            ]);

            // Update organization status to active
            $organization->update([
                'status' => 'active',
                'subscribed_at' => now(),
                'trial_ends_at' => null,
            ]);

            // Clear checkout session
            session()->forget('subscription_checkout');

            Log::info('Subscription payment successful', [
                'subscription_id' => $subscription->id,
                'payment_id' => $validated['razorpay_payment_id'],
                'order_id' => $validated['razorpay_order_id'],
            ]);

            return redirect()
                ->route('organization.subscription')
                ->with('success', 'Payment successful! Your subscription is now active.');

        } catch (\Exception $e) {
            Log::error('Payment callback failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()
                ->route('organization.subscription')
                ->with('error', 'Payment verification failed: ' . $e->getMessage());
        }
    }

    /**
     * Change subscription plan
     */
    public function change(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|exists:subscription_plans,id',
        ]);

        $organization = auth()->user()->organization;
        $subscription = $organization->subscription;

        if (!$subscription) {
            return back()->with('error', 'No active subscription found!');
        }

        $newPlan = SubscriptionPlan::findOrFail($validated['plan']);

        try {
            // Update subscription via SubscriptionService
            $this->subscriptionService->changePlan($subscription, $newPlan);

            return redirect()
                ->route('organization.subscription')
                ->with('success', 'Subscription plan changed successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to change plan', [
                'subscription_id' => $subscription->id,
                'new_plan_id' => $newPlan->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to change plan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $organization = auth()->user()->organization;
        $subscription = $organization->subscription;

        if (!$subscription) {
            return back()->with('error', 'No active subscription found!');
        }

        try {
            // Cancel subscription via SubscriptionService
            $this->subscriptionService->cancelSubscription($subscription, true);

            return back()->with('success', 'Subscription will be cancelled at the end of billing period.');

        } catch (\Exception $e) {
            Log::error('Failed to cancel subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }

    /**
     * Resume cancelled subscription
     */
    public function resume(Request $request)
    {
        $organization = auth()->user()->organization;
        $subscription = $organization->subscription;

        if (!$subscription || !$subscription->cancelled_at) {
            return back()->with('error', 'No cancelled subscription found!');
        }

        try {
            // Resume subscription via SubscriptionService
            $this->subscriptionService->resumeSubscription($subscription);

            return back()->with('success', 'Subscription resumed successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to resume subscription', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Failed to resume subscription: ' . $e->getMessage());
        }
    }

    /**
     * Update payment method
     */
    public function updatePaymentMethod(Request $request)
    {
        // Implementation depends on gateway
        return back()->with('info', 'Feature coming soon!');
    }
}
