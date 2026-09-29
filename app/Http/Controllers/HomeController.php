<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Support\VisitorCountry;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * `/` — the public entry point. Picks the visitor's country, in order:
     *  1. the `country` cookie (set whenever a country page is viewed),
     *  2. the `CF-IPCountry` header, when the site sits behind Cloudflare,
     *  3. a client-side guess (browser locale/timezone) on the detect page.
     *
     * `?redetect=1` skips 1 and 2 — QA, and an escape hatch for a misdetected visitor.
     */
    public function index(Request $request)
    {
        $countries = Country::where('is_active', true)->orderBy('sort_order')->get();
        abort_if($countries->isEmpty(), 404);

        if (! $request->boolean('redetect')) {
            $cookie = $countries->firstWhere('slug', $request->cookie('country'));
            if ($cookie) {
                return redirect($cookie->url());
            }

            $iso = VisitorCountry::isoCode($request);
            $byIp = $iso ? $countries->firstWhere('iso_code', $iso) : null;
            if ($byIp) {
                return redirect($byIp->url());
            }
        }

        return view('home.detect', [
            'countries' => $countries,
            'default' => $countries->firstWhere('is_default', true) ?? $countries->first(),
        ]);
    }
}
