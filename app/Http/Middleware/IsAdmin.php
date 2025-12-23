<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IsAdmin
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
        // Allow users with either 'admin' or 'owner' role to pass
        $user = auth()->user();
        if (! $user || ! $user->hasAnyRole(['admin', 'owner', 'team-member'])) {
            return redirect()->route('user.bookings.index');
        }
        return $next($request);
    }
}
