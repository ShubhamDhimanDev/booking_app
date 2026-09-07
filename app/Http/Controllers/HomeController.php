<?php

namespace App\Http\Controllers;

use App\Models\Setting;
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

        return $this->renderRegion('homepage_html_in');
    }

    /**
     * `/en-us` — the US home page.
     */
    public function us()
    {
        Cookie::queue('region', 'us', self::REGION_COOKIE_MINUTES);

        return $this->renderRegion('homepage_html_us');
    }

    /**
     * The stored HTML (Admin > Homepage Settings) is returned as-is and is
     * the ENTIRE response body — no app layout, no header/footer/nav, no
     * scripts from the rest of the site. Whatever the admin saved is exactly
     * what renders.
     */
    protected function renderRegion(string $settingKey)
    {
        $html = trim((string) Setting::getSetting($settingKey, ''));

        if ($html === '') {
            $html = '<!doctype html><html><head><meta charset="utf-8">'
                . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
                . '<title>' . e(config('app.name')) . '</title></head>'
                . '<body style="font-family:sans-serif;padding:4rem 2rem;text-align:center;color:#475569;">'
                . "This page hasn't been set up yet."
                . '</body></html>';
        }

        return response($html);
    }
}
