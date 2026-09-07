<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class HomeController extends Controller
{
    /**
     * How long the `region` cookie sticks, in minutes (1 year — arbitrary,
     * easy to tune later).
     */
    protected const REGION_COOKIE_MINUTES = 60 * 24 * 365;

    /**
     * `/` — the public entry point. A returning visitor with a `region`
     * cookie is redirected straight to their region's home page server-side
     * (no JS round-trip). A first-time visitor gets a standalone detection
     * page that figures out the region client-side (browser timezone).
     *
     * `?redetect=1` skips the cookie fast-path — useful for QA, and as an
     * escape hatch for a visitor who got misdetected.
     */
    public function index(Request $request)
    {
        if (! $request->boolean('redetect')) {
            $region = $request->cookie('region');

            if ($region === 'in') {
                return redirect()->route('home.in');
            }

            if ($region === 'us') {
                return redirect()->route('home.us');
            }
        }

        return view('home.detect');
    }

    /**
     * `/en-in` — the India home page.
     */
    public function in()
    {
        Cookie::queue('region', 'in', self::REGION_COOKIE_MINUTES);

        return view('home.show', [
            'region' => 'in',
            'event' => Event::where('currency', 'INR')->latest()->first(),
        ]);
    }

    /**
     * `/en-us` — the US home page. Shows a "coming soon" card until a USD
     * event actually exists (see docs/us-expansion/phase-4-us-event-launch-runbook.md);
     * once one is created, this page picks it up automatically.
     */
    public function us()
    {
        Cookie::queue('region', 'us', self::REGION_COOKIE_MINUTES);

        return view('home.show', [
            'region' => 'us',
            'event' => Event::where('currency', 'USD')->latest()->first(),
        ]);
    }
}
