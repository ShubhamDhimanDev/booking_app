<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Check Subscription Middleware
 *
 * Ensures the organization has an active subscription or trial
 */
class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (!app()->has('currentOrganization')) {
            abort(403, 'No organization context');
        }

        $org = app('currentOrganization');

        // Ensure we have a valid Organization instance
        if (!$org instanceof \App\Models\Organization) {
            abort(403, 'Invalid organization context');
        }

        // Check trial expiration
        if ($org->hasExpiredTrial()) {
            return redirect()->route('subscription.expired')
                ->with('error', 'Your trial has expired. Please subscribe to continue.');
        }

        // Check subscription status
        if ($org->status === 'suspended') {
            return redirect()->route('subscription.suspended')
                ->with('error', 'Your subscription is suspended. Please update your payment method.');
        }

        if ($org->status === 'cancelled') {
            return redirect()->route('subscription.cancelled')
                ->with('error', 'Your subscription has been cancelled.');
        }

        return $next($request);
    }
}
