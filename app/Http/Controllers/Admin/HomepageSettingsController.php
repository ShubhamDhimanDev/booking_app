<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomepageSettingsController extends Controller
{
    /**
     * Which regions can have their homepage content edited here, and the
     * Setting key each one is stored under.
     */
    protected const REGIONS = [
        'in' => ['label' => 'Homepage - India', 'key' => 'homepage_html_in'],
        'us' => ['label' => 'Homepage - International', 'key' => 'homepage_html_us'],
    ];

    public function index(Request $request)
    {
        $region = $request->query('region');
        if (! array_key_exists($region, self::REGIONS)) {
            $region = 'in';
        }

        $html = Setting::getSetting(self::REGIONS[$region]['key'], '');

        return view('admin.homepage-settings.index', [
            'regions' => self::REGIONS,
            'region' => $region,
            'html' => $html,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'region' => 'required|in:' . implode(',', array_keys(self::REGIONS)),
            'html' => 'nullable|string',
        ]);

        Setting::setSetting(self::REGIONS[$validated['region']]['key'], $validated['html'] ?? '');

        return redirect()->route('admin.homepage-settings.index', ['region' => $validated['region']])->with([
            'alert_type' => 'success',
            'alert_message' => self::REGIONS[$validated['region']]['label'] . ' content updated successfully.',
        ]);
    }
}
