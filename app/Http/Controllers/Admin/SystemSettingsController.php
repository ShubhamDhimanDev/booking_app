<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\ThemeService;
use Illuminate\Http\Request;

class SystemSettingsController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::getSettings();
        return view('admin.system-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'theme_layout' => 'required|in:modern,classic',
            'dark_mode' => 'boolean',
        ]);

        $settings = SystemSetting::getSettings();
        $settings->update([
            'theme_layout' => $validated['theme_layout'],
            'dark_mode' => $request->has('dark_mode'),
        ]);

        // Clear theme cache
        ThemeService::clearCache();

        return redirect()->back()->with([
            'alert_type' => 'success',
            'alert_message' => 'System settings updated successfully!'
        ]);
    }
}
