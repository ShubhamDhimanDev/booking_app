<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::withCount(['pages', 'events'])->orderBy('sort_order')->orderBy('name')->get();

        return view('admin.cms.countries.index', compact('countries'));
    }

    public function create()
    {
        return view('admin.cms.countries.create', ['country' => new Country(['is_active' => true, 'currency' => 'USD', 'default_timezone' => 'UTC'])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['sort_order'] = (int) Country::max('sort_order') + 1;
        $country = Country::create($data);
        $this->syncDefault($country);

        return redirect()->route('admin.countries.edit', $country)->with([
            'alert_type' => 'success',
            'alert_message' => 'Country created. Add its header/footer and pages next.',
        ]);
    }

    public function edit(Country $country)
    {
        $country->load(['navItems.page', 'pages']);

        return view('admin.cms.countries.edit', compact('country'));
    }

    public function update(Request $request, Country $country)
    {
        $data = $this->validated($request, $country);
        $country->update($data);
        $this->syncDefault($country);

        return back()->with([
            'alert_type' => 'success',
            'alert_message' => 'Country updated.',
        ]);
    }

    public function destroy(Country $country)
    {
        if ($country->is_default) {
            return back()->with(['alert_type' => 'danger', 'alert_message' => 'The default country cannot be deleted. Make another country the default first.']);
        }
        if ($country->events()->exists()) {
            return back()->with(['alert_type' => 'danger', 'alert_message' => 'This country still has events. Move or delete them first.']);
        }

        $country->delete();

        return redirect()->route('admin.countries.index')->with(['alert_type' => 'success', 'alert_message' => 'Country deleted.']);
    }

    protected function validated(Request $request, ?Country $country = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => ['required', 'regex:/^[a-z0-9]+(-[a-z0-9]+)*$/', 'max:60',
                Rule::unique('countries', 'slug')->ignore($country?->id),
                Rule::notIn(config('cms.reserved_slugs'))],
            'iso_code' => 'nullable|alpha|size:2',
            'currency' => ['required', Rule::in(array_keys(config('cms.currencies')))],
            'default_timezone' => ['required', Rule::in(\DateTimeZone::listIdentifiers())],
            'header_html' => 'nullable|string',
            'footer_html' => 'nullable|string',
            'free_session_event_id' => 'nullable|exists:events,id',
        ]);

        $data['iso_code'] = isset($data['iso_code']) ? strtoupper($data['iso_code']) : null;
        $data['is_active'] = $request->boolean('is_active');
        $data['is_default'] = $request->boolean('is_default');
        $data['ecommerce_enabled'] = $request->boolean('ecommerce_enabled');
        if ($data['is_default']) {
            $data['is_active'] = true;
        }
        // Header/footer are only edited on the edit form; keep them when absent.
        foreach (['header_html', 'footer_html'] as $key) {
            if (! $request->has($key)) {
                unset($data[$key]);
            }
        }

        return $data;
    }

    /** Exactly one default country, and there is always at least one. */
    protected function syncDefault(Country $country): void
    {
        if ($country->is_default) {
            Country::where('id', '!=', $country->id)->update(['is_default' => false]);
        } elseif (! Country::where('is_default', true)->exists()) {
            $country->update(['is_default' => true, 'is_active' => true]);
        }
    }
}
