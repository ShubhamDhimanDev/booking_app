<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Check Usage Limits Middleware
 *
 * Enforces plan-based usage limits for organizations
 */
class CheckUsageLimits
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
        $org = app('currentOrganization');
        $plan = $org->currentPlan ?? null;

        if (!$plan) {
            return $next($request); // No limits without plan (trial/legacy)
        }

        // Ensure we have a valid Organization instance
        if (!$org instanceof \App\Models\Organization) {
            return $next($request);
        }

        // Check based on action
        $route = $request->route();
        if (!$route) {
            return $next($request);
        }

        $action = $route->getActionMethod();

        // Only check on create/store actions
        if (!in_array($action, ['store', 'create'])) {
            return $next($request);
        }

        // Determine resource from route
        $resource = $this->getResourceFromRoute($route);

        switch ($resource) {
            case 'events':
                $count = $org->events()->count();
                if ($count >= $plan->max_events) {
                    return back()->with('error', "Event limit reached ({$plan->max_events}). Please upgrade your plan.");
                }
                break;

            case 'users':
            case 'team':
                $count = $org->users()->count();
                if ($count >= $plan->max_team_members) {
                    return back()->with('error', "Team member limit reached ({$plan->max_team_members}). Please upgrade your plan.");
                }
                break;

            case 'promo-codes':
                $count = $org->promoCodes()->count();
                if (isset($plan->max_promo_codes) && $count >= $plan->max_promo_codes) {
                    return back()->with('error', "Promo code limit reached ({$plan->max_promo_codes}). Please upgrade your plan.");
                }
                break;
        }

        return $next($request);
    }

    /**
     * Extract resource name from route
     */
    protected function getResourceFromRoute($route): ?string
    {
        $name = $route->getName();

        if (!$name) {
            return null;
        }

        // Extract resource from route name (e.g., 'admin.events.store' -> 'events')
        $parts = explode('.', $name);

        if (count($parts) >= 2) {
            return $parts[count($parts) - 2];
        }

        return null;
    }
}
