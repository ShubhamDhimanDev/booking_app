<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\TrackingService;
use Illuminate\Http\Request;

class TrackingSettingsController extends Controller
{
    /**
     * Show tracking settings form
     */
    public function index()
    {
        $settings = [
            'meta_pixel_enabled' => (bool) Setting::getSetting('meta_pixel_enabled', false),
            'meta_pixel_id' => Setting::getSetting('meta_pixel_id', ''),
            'meta_event_page_view' => (bool) Setting::getSetting('meta_event_page_view', true),
            'meta_event_initiate_checkout' => (bool) Setting::getSetting('meta_event_initiate_checkout', true),
            'meta_event_add_payment_info' => (bool) Setting::getSetting('meta_event_add_payment_info', true),
            'meta_event_purchase' => (bool) Setting::getSetting('meta_event_purchase', true),
        ];

        return view('admin.tracking.index', compact('settings'));
    }

    /**
     * Update tracking settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'meta_pixel_enabled' => 'nullable|boolean',
            'meta_pixel_id' => 'nullable|string|max:50',
            'meta_event_page_view' => 'nullable|boolean',
            'meta_event_initiate_checkout' => 'nullable|boolean',
            'meta_event_add_payment_info' => 'nullable|boolean',
            'meta_event_purchase' => 'nullable|boolean',
        ]);

        // Update each setting
        Setting::setSetting('meta_pixel_enabled', $request->has('meta_pixel_enabled') ? '1' : '0');
        Setting::setSetting('meta_pixel_id', $request->input('meta_pixel_id', ''));
        Setting::setSetting('meta_event_page_view', $request->has('meta_event_page_view') ? '1' : '0');
        Setting::setSetting('meta_event_initiate_checkout', $request->has('meta_event_initiate_checkout') ? '1' : '0');
        Setting::setSetting('meta_event_add_payment_info', $request->has('meta_event_add_payment_info') ? '1' : '0');
        Setting::setSetting('meta_event_purchase', $request->has('meta_event_purchase') ? '1' : '0');

        return redirect()->route('admin.tracking.index')->with([
            'alert_type' => 'success',
            'alert_message' => 'Tracking settings updated successfully.'
        ]);
    }
}
