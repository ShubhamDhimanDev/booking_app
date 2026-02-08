<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Display a listing of subscription plans
     */
    public function index()
    {
        $plans = SubscriptionPlan::withCount(['subscriptions' => function ($query) {
            $query->where('status', 'active');
        }])
            ->orderBy('monthly_price')
            ->get();

        return view('super-admin.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new plan
     */
    public function create()
    {
        return view('super-admin.plans.create');
    }

    /**
     * Store a newly created plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'max_events' => 'nullable|integer|min:1',
            'max_team_members' => 'nullable|integer|min:1',
            'max_bookings_per_month' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        $plan = SubscriptionPlan::create($validated);

        return redirect()
            ->route('super-admin.plans.show', $plan)
            ->with('success', 'Subscription plan created successfully!');
    }

    /**
     * Display the specified plan
     */
    public function show(SubscriptionPlan $plan)
    {
        $plan->loadCount('subscriptions');
        $plan->load(['subscriptions' => function ($query) {
            $query->with('organization')->latest()->take(10);
        }]);

        return view('super-admin.plans.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified plan
     */
    public function edit(SubscriptionPlan $plan)
    {
        return view('super-admin.plans.edit', compact('plan'));
    }

    /**
     * Update the specified plan
     */
    public function update(Request $request, SubscriptionPlan $plan)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'monthly_price' => 'required|numeric|min:0',
            'yearly_price' => 'nullable|numeric|min:0',
            'trial_days' => 'nullable|integer|min:0',
            'max_events' => 'nullable|integer|min:1',
            'max_team_members' => 'nullable|integer|min:1',
            'max_bookings_per_month' => 'nullable|integer|min:1',
            'features' => 'nullable|array',
            'status' => 'required|in:active,inactive',
        ]);

        $plan->update($validated);

        return redirect()
            ->route('super-admin.plans.show', $plan)
            ->with('success', 'Subscription plan updated successfully!');
    }

    /**
     * Remove the specified plan
     */
    public function destroy(SubscriptionPlan $plan)
    {
        // Check if plan has active subscriptions
        if ($plan->subscriptions()->where('status', 'active')->count() > 0) {
            return back()->with('error', 'Cannot delete plan with active subscriptions!');
        }

        $plan->delete();

        return redirect()
            ->route('super-admin.plans.index')
            ->with('success', 'Subscription plan deleted successfully!');
    }

    /**
     * Activate plan
     */
    public function activate(SubscriptionPlan $plan)
    {
        $plan->update(['status' => 'active']);

        return back()->with('success', 'Plan activated successfully!');
    }

    /**
     * Deactivate plan
     */
    public function deactivate(SubscriptionPlan $plan)
    {
        $plan->update(['status' => 'inactive']);

        return back()->with('success', 'Plan deactivated successfully!');
    }
}
