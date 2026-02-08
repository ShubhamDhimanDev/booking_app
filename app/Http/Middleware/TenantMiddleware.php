<?php

namespace App\Http\Middleware;

use App\Models\Organization;
use Closure;
use Illuminate\Http\Request;

/**
 * Tenant Middleware
 *
 * Identifies the organization from subdomain or custom domain
 * and sets it in the application context
 */
class TenantMiddleware
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
        $host = $request->getHost();

        // Skip for main platform domain or localhost
        $platformDomain = config('app.platform_domain', 'meetflow.app');
        if ($host === $platformDomain || $host === 'localhost' || str_contains($host, '127.0.0.1')) {
            return $next($request);
        }

        $org = null;

        // Check if custom domain
        $org = Organization::where('domain', $host)->first();

        // If not, check subdomain
        if (!$org) {
            $subdomain = explode('.', $host)[0];
            if ($subdomain !== 'www' && $subdomain !== config('app.domain_root', 'meetflow')) {
                $org = Organization::where('slug', $subdomain)->first();
            }
        }

        if (!$org) {
            abort(404, 'Organization not found');
        }

        // Set organization context
        app()->instance('currentOrganization', $org);
        $request->merge(['organization_id' => $org->id]);

        // Share organization data with Blade views
        view()->share('currentOrganization', $org);
        view()->share('currentOrgId', $org->id);
        view()->share('currentOrgName', $org->name);

        // Check if organization is active
        if (!$org->isActive() && !$org->isOnTrial()) {
            if ($org->isSuspended()) {
                return redirect()->route('organization.suspended');
            }
            if ($org->hasExpiredTrial()) {
                return redirect()->route('organization.trial-expired');
            }
        }

        return $next($request);
    }
}
