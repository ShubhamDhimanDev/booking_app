<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CaptureUtmParameters
{
    /**
     * Handle an incoming request and capture UTM parameters.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // List of tracking parameters to capture
        $trackingParams = [
            'utm_source',
            'utm_medium',
            'utm_campaign',
            'utm_content',
            'utm_term',
            'fbclid',    // Facebook Click ID
            'gclid',     // Google Click ID
        ];

        // Capture any tracking parameters from the URL
        foreach ($trackingParams as $param) {
            if ($request->has($param) && !empty($request->get($param))) {
                // Store in session with a reasonable lifetime (30 days)
                session()->put("tracking_{$param}", $request->get($param));
            }
        }

        return $next($request);
    }
}
