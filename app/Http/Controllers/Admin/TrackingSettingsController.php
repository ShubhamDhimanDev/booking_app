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
            // Meta Pixel Settings
            'meta_pixel_enabled' => (bool) Setting::getSetting('meta_pixel_enabled', false),
            'meta_pixel_id' => Setting::getSetting('meta_pixel_id', ''),
            'meta_event_page_view' => (bool) Setting::getSetting('meta_event_page_view', true),
            'meta_event_initiate_checkout' => (bool) Setting::getSetting('meta_event_initiate_checkout', true),
            'meta_event_add_payment_info' => (bool) Setting::getSetting('meta_event_add_payment_info', true),
            'meta_event_purchase' => (bool) Setting::getSetting('meta_event_purchase', true),
            'meta_event_viewbookings' => (bool) Setting::getSetting('meta_event_viewbookings', true),
            'meta_event_bookingrescheduled' => (bool) Setting::getSetting('meta_event_bookingrescheduled', true),
            'meta_event_viewtransactions' => (bool) Setting::getSetting('meta_event_viewtransactions', true),
            'meta_event_viewpaymentpage' => (bool) Setting::getSetting('meta_event_viewpaymentpage', true),

            // Google Analytics Settings
            'google_analytics_enabled' => (bool) Setting::getSetting('google_analytics_enabled', false),
            'google_analytics_id' => Setting::getSetting('google_analytics_id', ''),
            'google_event_page_view' => (bool) Setting::getSetting('google_event_page_view', true),
            'google_event_begin_checkout' => (bool) Setting::getSetting('google_event_begin_checkout', true),
            'google_event_add_payment_info' => (bool) Setting::getSetting('google_event_add_payment_info', true),
            'google_event_purchase' => (bool) Setting::getSetting('google_event_purchase', true),
            'google_event_view_bookings' => (bool) Setting::getSetting('google_event_view_bookings', true),
            'google_event_booking_rescheduled' => (bool) Setting::getSetting('google_event_booking_rescheduled', true),
            'google_event_view_transactions' => (bool) Setting::getSetting('google_event_view_transactions', true),
            'google_event_view_payment_page' => (bool) Setting::getSetting('google_event_view_payment_page', true),
        ];

        return view('admin.tracking.index', compact('settings'));
    }

    /**
     * Update tracking settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            // Meta Pixel
            'meta_pixel_enabled' => 'nullable|boolean',
            'meta_pixel_id' => 'nullable|string|max:50',
            'meta_event_page_view' => 'nullable|boolean',
            'meta_event_initiate_checkout' => 'nullable|boolean',
            'meta_event_add_payment_info' => 'nullable|boolean',
            'meta_event_purchase' => 'nullable|boolean',
            'meta_event_viewbookings' => 'nullable|boolean',
            'meta_event_bookingrescheduled' => 'nullable|boolean',
            'meta_event_viewtransactions' => 'nullable|boolean',
            'meta_event_viewpaymentpage' => 'nullable|boolean',

            // Google Analytics
            'google_analytics_enabled' => 'nullable|boolean',
            'google_analytics_id' => 'nullable|string|max:50',
            'google_event_page_view' => 'nullable|boolean',
            'google_event_begin_checkout' => 'nullable|boolean',
            'google_event_add_payment_info' => 'nullable|boolean',
            'google_event_purchase' => 'nullable|boolean',
            'google_event_view_bookings' => 'nullable|boolean',
            'google_event_booking_rescheduled' => 'nullable|boolean',
            'google_event_view_transactions' => 'nullable|boolean',
            'google_event_view_payment_page' => 'nullable|boolean',
        ]);

        // Update Meta Pixel settings
        Setting::setSetting('meta_pixel_enabled', $request->has('meta_pixel_enabled') ? '1' : '0');
        Setting::setSetting('meta_pixel_id', $request->input('meta_pixel_id', ''));
        Setting::setSetting('meta_event_page_view', $request->has('meta_event_page_view') ? '1' : '0');
        Setting::setSetting('meta_event_initiate_checkout', $request->has('meta_event_initiate_checkout') ? '1' : '0');
        Setting::setSetting('meta_event_add_payment_info', $request->has('meta_event_add_payment_info') ? '1' : '0');
        Setting::setSetting('meta_event_purchase', $request->has('meta_event_purchase') ? '1' : '0');
        Setting::setSetting('meta_event_viewbookings', $request->has('meta_event_viewbookings') ? '1' : '0');
        Setting::setSetting('meta_event_bookingrescheduled', $request->has('meta_event_bookingrescheduled') ? '1' : '0');
        Setting::setSetting('meta_event_viewtransactions', $request->has('meta_event_viewtransactions') ? '1' : '0');
        Setting::setSetting('meta_event_viewpaymentpage', $request->has('meta_event_viewpaymentpage') ? '1' : '0');

        // Update Google Analytics settings
        Setting::setSetting('google_analytics_enabled', $request->has('google_analytics_enabled') ? '1' : '0');
        Setting::setSetting('google_analytics_id', $request->input('google_analytics_id', ''));
        Setting::setSetting('google_event_page_view', $request->has('google_event_page_view') ? '1' : '0');
        Setting::setSetting('google_event_begin_checkout', $request->has('google_event_begin_checkout') ? '1' : '0');
        Setting::setSetting('google_event_add_payment_info', $request->has('google_event_add_payment_info') ? '1' : '0');
        Setting::setSetting('google_event_purchase', $request->has('google_event_purchase') ? '1' : '0');
        Setting::setSetting('google_event_view_bookings', $request->has('google_event_view_bookings') ? '1' : '0');
        Setting::setSetting('google_event_booking_rescheduled', $request->has('google_event_booking_rescheduled') ? '1' : '0');
        Setting::setSetting('google_event_view_transactions', $request->has('google_event_view_transactions') ? '1' : '0');
        Setting::setSetting('google_event_view_payment_page', $request->has('google_event_view_payment_page') ? '1' : '0');

        return redirect()->route('admin.tracking.index')->with([
            'alert_type' => 'success',
            'alert_message' => 'Tracking settings updated successfully.'
        ]);
    }
}
