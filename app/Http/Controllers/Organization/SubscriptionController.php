<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use App\Services\PaymentGatewayFactory;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected $paymentGatewayFactory;

    public function __construct(PaymentGatewayFactory $paymentGatewayFactory)
    {
        $this->paymentGatewayFactory = $paymentGatewayFactory;
    }

    /**
     * Display subscription information
     */
    public function show()
    {
        $organization = auth()->user()->organization;
        $subscription = $organization->activeSubscription;

        $plans = null;

        // If no subscription, show available plans
        if (!$subscription) {
            $plans = SubscriptionPlan::where('status', 'active')
                ->orderBy('monthly_price')
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
        $currentSubscription = $organization->activeSubscription;

        if (!$currentSubscription) {
            return redirect()->route('organization.subscription');
        }

        $plans = SubscriptionPlan::where('status', 'active')
            ->where('id', '!=', $currentSubscription->subscription_plan_id)
            ->orderBy('monthly_price')
            ->get();

        return view('organization.billing.change-plan', compact('currentSubscription', 'plans'));
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|exists:subscription_plans,id',
            'cycle' => 'required|in:monthly,yearly',
            'gateway' => 'required|in:razorpay,stripe,paypal',
        ]);

        $organization = auth()->user()->organization;
        $plan = SubscriptionPlan::findOrFail($validated['plan']);

        // Check if organization already has an active subscription
        if ($organization->activeSubscription) {
            return back()->with('error', 'You already have an active subscription!');
        }

        $amount = $validated['cycle'] === 'monthly' ? $plan->monthly_price : $plan->yearly_price;

        // Create subscription via payment gateway
        $gateway = $this->paymentGatewayFactory->make($validated['gateway']);

        try {
            $subscriptionData = $gateway->createSubscription([
                'plan_id' => $plan->id,
                'customer_email' => auth()->user()->email,
                'amount' => $amount,
                'billing_cycle' => $validated['cycle'],
            ]);

            // Create subscription record
            $subscription = $organization->subscriptions()->create([
                'subscription_plan_id' => $plan->id,
                'gateway' => $validated['gateway'],
                'gateway_subscription_id' => $subscriptionData['subscription_id'],
                'gateway_customer_id' => $subscriptionData['customer_id'] ?? null,
                'status' => 'trial', // Start with trial if applicable
                'billing_cycle' => $validated['cycle'],
                'amount' => $amount,
                'started_at' => now(),
                'current_period_start' => now(),
                'current_period_end' => now()->addMonth(),
                'trial_ends_at' => $plan->trial_days ? now()->addDays($plan->trial_days) : null,
            ]);

            return redirect()
                ->route('organization.subscription')
                ->with('success', 'Subscription created successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create subscription: ' . $e->getMessage());
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
        $subscription = $organization->activeSubscription;

        if (!$subscription) {
            return back()->with('error', 'No active subscription found!');
        }

        $newPlan = SubscriptionPlan::findOrFail($validated['plan']);

        // Update subscription via payment gateway
        $gateway = $this->paymentGatewayFactory->make($subscription->gateway);

        try {
            $gateway->updateSubscription($subscription->gateway_subscription_id, [
                'plan_id' => $newPlan->id,
            ]);

            $subscription->update([
                'subscription_plan_id' => $newPlan->id,
                'amount' => $subscription->billing_cycle === 'monthly'
                    ? $newPlan->monthly_price
                    : $newPlan->yearly_price,
            ]);

            return redirect()
                ->route('organization.subscription')
                ->with('success', 'Subscription plan changed successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to change plan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request)
    {
        $organization = auth()->user()->organization;
        $subscription = $organization->activeSubscription;

        if (!$subscription) {
            return back()->with('error', 'No active subscription found!');
        }

        // Cancel subscription via payment gateway
        $gateway = $this->paymentGatewayFactory->make($subscription->gateway);

        try {
            $gateway->cancelSubscription($subscription->gateway_subscription_id);

            $subscription->update([
                'cancel_at_period_end' => true,
                'cancelled_at' => now(),
            ]);

            return back()->with('success', 'Subscription will be cancelled at the end of billing period.');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to cancel subscription: ' . $e->getMessage());
        }
    }

    /**
     * Resume cancelled subscription
     */
    public function resume(Request $request)
    {
        $organization = auth()->user()->organization;
        $subscription = $organization->activeSubscription;

        if (!$subscription || !$subscription->cancel_at_period_end) {
            return back()->with('error', 'No cancelled subscription found!');
        }

        // Resume subscription via payment gateway
        $gateway = $this->paymentGatewayFactory->make($subscription->gateway);

        try {
            $gateway->resumeSubscription($subscription->gateway_subscription_id);

            $subscription->update([
                'cancel_at_period_end' => false,
                'cancelled_at' => null,
            ]);

            return back()->with('success', 'Subscription resumed successfully!');

        } catch (\Exception $e) {
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
