<?php

namespace App\Http\Middleware;

use App\Models\Country;
use App\Support\VisitorCountry;
use Closure;
use Illuminate\Http\Request;

/**
 * An event belongs to one country. A visitor whose (detected) country is a
 * different configured country gets a "not available for your country" page.
 *
 * Not restricted: admins, events without a country, visitors whose country is
 * unknown, and visitors from a country that has no site of its own.
 */
class EnsureEventAvailableInCountry
{
    public function handle(Request $request, Closure $next)
    {
        $event = $request->route('event');
        $user = $request->user();

        if (! $event || ($user && $user->hasAnyRole(['admin', 'owner']))) {
            return $next($request);
        }

        $event->loadMissing('country');
        $iso = VisitorCountry::isoCode($request);
        if (! $event->country || ! $iso || strtoupper((string) $event->country->iso_code) === $iso) {
            return $next($request);
        }

        $visitorCountry = Country::where('is_active', true)->where('iso_code', $iso)->first();
        if (! $visitorCountry) {
            return $next($request);
        }

        return response()->view('cms.event-unavailable', [
            'event' => $event,
            'country' => $visitorCountry,
        ], 403);
    }
}
