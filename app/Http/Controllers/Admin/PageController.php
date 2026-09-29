<?php

namespace App\Http\Controllers\Admin;

use App\Cms\SectionTypes;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PageController extends Controller
{
    public function index(Request $request)
    {
        $countries = Country::orderBy('sort_order')->orderBy('name')->get();
        $countryId = $request->query('country_id') ?: optional($countries->firstWhere('is_default', true) ?? $countries->first())->id;

        $pages = Page::with('country')->withCount('sections')
            ->where('country_id', $countryId)
            ->orderByDesc('is_home')->orderBy('sort_order')->orderBy('title')
            ->get();

        return view('admin.cms.pages.index', compact('countries', 'countryId', 'pages'));
    }

    public function create(Request $request)
    {
        $countries = Country::orderBy('sort_order')->get();
        abort_if($countries->isEmpty(), 404);

        return view('admin.cms.pages.create', [
            'countries' => $countries,
            'countryId' => $request->query('country_id') ?: optional($countries->firstWhere('is_default', true))->id,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = (int) Page::where('country_id', $data['country_id'])->max('sort_order') + 1;
        $page = Page::create($data);
        $this->syncHome($page);

        return redirect()->route('admin.pages.edit', $page)->with(['alert_type' => 'success', 'alert_message' => 'Page created. Add sections below.']);
    }

    public function edit(Page $page)
    {
        $page->load(['country', 'sections']);
        $events = $page->country->events()->orderBy('title')->get(['id', 'title']);

        return view('admin.cms.pages.edit', [
            'page' => $page,
            'types' => SectionTypes::all(),
            'events' => $events,
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $data = $this->validated($request, $page);
        unset($data['country_id']); // moving a page between countries = duplicate
        $page->update($data);
        $this->syncHome($page);

        return back()->with(['alert_type' => 'success', 'alert_message' => 'Page saved.']);
    }

    public function destroy(Page $page)
    {
        $countryId = $page->country_id;
        $page->delete();

        return redirect()->route('admin.pages.index', ['country_id' => $countryId])->with(['alert_type' => 'success', 'alert_message' => 'Page deleted.']);
    }

    /** Copy a page (with all its sections) into any country, as a draft. */
    public function duplicate(Request $request, Page $page)
    {
        $request->validate(['country_id' => 'required|exists:countries,id']);
        $countryId = (int) $request->input('country_id');

        $slug = $base = $page->slug;
        for ($i = 2; Page::where('country_id', $countryId)->where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        $copy = Page::create([
            'country_id' => $countryId,
            'title' => $page->title,
            'slug' => $slug,
            'is_home' => false,
            'status' => 'draft',
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'sort_order' => (int) Page::where('country_id', $countryId)->max('sort_order') + 1,
        ]);
        foreach ($page->sections as $section) {
            $copy->sections()->create($section->only(['type', 'content', 'sort_order', 'is_visible']));
        }

        return redirect()->route('admin.pages.edit', $copy)->with(['alert_type' => 'success', 'alert_message' => 'Page copied as a draft.']);
    }

    protected function validated(Request $request, ?Page $page = null): array
    {
        $countryId = $page ? $page->country_id : (int) $request->input('country_id');

        $data = $request->validate([
            'country_id' => 'required|exists:countries,id',
            'title' => 'required|string|max:150',
            'slug' => ['required', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', 'max:100',
                Rule::unique('pages', 'slug')->where('country_id', $countryId)->ignore($page?->id),
                Rule::notIn(config('cms.reserved_slugs'))],
            'status' => 'required|in:draft,published',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:1000',
        ]);
        $data['is_home'] = $request->boolean('is_home');

        return $data;
    }

    protected function syncHome(Page $page): void
    {
        if ($page->is_home) {
            Page::where('country_id', $page->country_id)->where('id', '!=', $page->id)->update(['is_home' => false]);
        }
    }
}
