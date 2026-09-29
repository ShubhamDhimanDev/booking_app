<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Where is the visitor? Sources, first match wins:
 *  1. `?as_country=US` (non-production only, for testing),
 *  2. a country header set by a CDN in front of the site (Cloudflare / CloudFront / Vercel),
 *  3. IP geolocation via freeipapi.com, cached per IP.
 *
 * Returns null when unknown (private/local IP, lookup failed or rate-limited).
 * Callers must treat null as "don't restrict" -- geolocation is best-effort and fails open.
 */
class VisitorCountry
{
    protected const HEADERS = ['CF-IPCountry', 'CloudFront-Viewer-Country', 'X-Vercel-IP-Country'];

    public static function isoCode(Request $request): ?string
    {
        if (! app()->environment('production') && $request->query('as_country')) {
            return strtoupper((string) $request->query('as_country'));
        }

        foreach (self::HEADERS as $header) {
            $value = strtoupper(trim((string) $request->header($header)));
            // Cloudflare uses XX (unknown) and T1 (Tor).
            if (preg_match('/^[A-Z]{2}$/', $value) && ! in_array($value, ['XX', 'T1'], true)) {
                return $value;
            }
        }

        return self::lookupIp($request->ip());
    }

    protected static function lookupIp(?string $ip): ?string
    {
        if (! config('cms.geo.enabled') || ! $ip) {
            return null;
        }
        // Local / private / reserved addresses can't be geolocated.
        if (! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return null;
        }

        $key = 'geo:country:' . $ip;
        $cached = Cache::get($key);
        if ($cached !== null) {
            return $cached === '' ? null : $cached; // '' = a remembered failure
        }

        $iso = null;
        try {
            $response = Http::timeout((float) config('cms.geo.timeout'))
                ->acceptJson()
                ->get(rtrim(config('cms.geo.url'), '/') . '/' . $ip);

            if ($response->successful()) {
                $code = strtoupper((string) $response->json('countryCode'));
                $iso = preg_match('/^[A-Z]{2}$/', $code) ? $code : null;
            }
        } catch (\Throwable $e) {
            // timeout / DNS / TLS: fall through as unknown
        }

        // Remember failures briefly so a rate-limited or down API isn't hammered on every request.
        Cache::put($key, $iso ?? '', $iso ? (int) config('cms.geo.ttl') : (int) config('cms.geo.failure_ttl'));

        return $iso;
    }
}
