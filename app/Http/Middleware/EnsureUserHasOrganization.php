<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasOrganization
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
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Super admins can access organization routes for testing purposes
        if ($user->hasRole('super-admin')) {
            return $next($request);
        }

        // Check if user belongs to an organization
        if (!$user->organization_id) {
            abort(403, 'You must belong to an organization to access this area.');
        }

        // Check if organization is active
        if ($user->organization && $user->organization->status !== 'active') {
            abort(403, 'Your organization is not active. Please contact support.');
        }

        return $next($request);
    }
}
