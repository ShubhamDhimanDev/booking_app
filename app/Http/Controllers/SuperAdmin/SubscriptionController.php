<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of all subscriptions
     */
    public function index(Request $request)
    {
        $subscriptions = Subscription::with(['organization', 'plan'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->plan, function ($query, $planId) {
                $query->where('subscription_plan_id', $planId);
            })
            ->when($request->search, function ($query, $search) {
                $query->whereHas('organization', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest('started_at')
            ->paginate(20);

        return view('super-admin.subscriptions.index', compact('subscriptions'));
    }

    /**
     * Display the specified subscription
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['organization', 'plan', 'invoices']);

        return view('super-admin.subscriptions.show', compact('subscription'));
    }

    /**
     * Cancel subscription
     */
    public function cancel(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_at_period_end' => true,
        ]);

        return back()->with('success', 'Subscription cancelled successfully!');
    }

    /**
     * Resume subscription
     */
    public function resume(Subscription $subscription)
    {
        $subscription->update([
            'status' => 'active',
            'cancelled_at' => null,
            'cancel_at_period_end' => false,
        ]);

        return back()->with('success', 'Subscription resumed successfully!');
    }
}
