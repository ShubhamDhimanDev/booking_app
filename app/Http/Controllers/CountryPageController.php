<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CountryPageController extends Controller
{
    protected const COUNTRY_COOKIE_MINUTES = 60 * 24 * 365;

    /** `/{country}` — the country's home page. */
    public function home(Request $request, Country $cmsCountry)
    {
        $page = $this->pages($request, $cmsCountry)->where('is_home', true)->first();

        return $this->render($request, $cmsCountry, $page);
    }

    /** `/{country}/{page}` */
    public function show(Request $request, Country $cmsCountry, string $slug)
    {
        $page = $this->pages($request, $cmsCountry)->where('slug', $slug)->firstOrFail();

        return $this->render($request, $cmsCountry, $page);
    }

    /** Admins may preview drafts and inactive countries with ?preview=1. */
    protected function isPreview(Request $request): bool
    {
        return $request->boolean('preview') && $request->user() && $request->user()->hasAnyRole(['admin', 'owner']);
    }

    protected function pages(Request $request, Country $country)
    {
        abort_unless($country->is_active || $this->isPreview($request), 404);
        $query = $country->pages()->with('sections');

        return $this->isPreview($request) ? $query : $query->published();
    }

    protected function render(Request $request, Country $country, ?Page $page)
    {
        if (! $this->isPreview($request)) {
            Cookie::queue('country', $country->slug, self::COUNTRY_COOKIE_MINUTES);
        }

        if (! $page) {
            // Home page not built yet: same friendly placeholder the site always had.
            abort_unless($request->path() === $country->slug, 404);

            return response('<!doctype html><html><head><meta charset="utf-8">'
                . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
                . '<title>' . e(config('app.name')) . '</title></head>'
                . '<body style="font-family:sans-serif;padding:4rem 2rem;text-align:center;color:#475569;">'
                . "This page hasn't been set up yet."
                . '</body></html>');
        }

        $page->setRelation('country', $country);

        if ($html = $page->fullDocumentHtml()) {
            return response($html);
        }

        // Same page slug in other countries -> hreflang alternates.
        $alternates = Page::with('country')->published()
            ->where('slug', $page->slug)->where('country_id', '!=', $country->id)
            ->whereHas('country', fn ($q) => $q->where('is_active', true))
            ->get();

        return view('cms.page', [
            'page' => $page,
            'country' => $country,
            'alternates' => $alternates,
        ]);
    }
}
