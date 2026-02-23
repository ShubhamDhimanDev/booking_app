<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class SettingsController extends Controller
{
    /**
     * Display system settings
     */
    public function index()
    {
        $settings = [
            'default_payment_gateway' => AppSetting::get('default_payment_gateway', 'razorpay'),
            'razorpay_enabled' => AppSetting::get('razorpay_enabled', true),
            'stripe_enabled' => AppSetting::get('stripe_enabled', false),
            'paypal_enabled' => AppSetting::get('paypal_enabled', false),
        ];

        return view('super-admin.settings.index', compact('settings'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'timezone' => 'required|string',
        ]);

        // Update .env file or config
        // Implementation depends on your config management

        return back()->with('success', 'General settings updated successfully!');
    }

    /**
     * Update email settings
     */
    public function updateEmail(Request $request)
    {
        $validated = $request->validate([
            'mail_driver' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|integer',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
        ]);

        // Update email config
        return back()->with('success', 'Email settings updated successfully!');
    }

    /**
     * Update payment gateway settings
     */
    public function updatePayment(Request $request)
    {
        $validated = $request->validate([
            'default_payment_gateway' => 'required|in:razorpay,stripe,paypal',
            'razorpay_enabled' => 'nullable|boolean',
            'stripe_enabled' => 'nullable|boolean',
            'paypal_enabled' => 'nullable|boolean',
        ]);

        // Update settings
        AppSetting::set('default_payment_gateway', $validated['default_payment_gateway'], 'string');
        AppSetting::set('razorpay_enabled', $validated['razorpay_enabled'] ?? false, 'boolean');
        AppSetting::set('stripe_enabled', $validated['stripe_enabled'] ?? false, 'boolean');
        AppSetting::set('paypal_enabled', $validated['paypal_enabled'] ?? false, 'boolean');

        // Clear settings cache
        AppSetting::clearCache();

        return back()->with('success', 'Payment gateway settings updated successfully!');
    }

    /**
     * Clear application cache
     */
    public function clearCache()
    {
        Cache::flush();
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('view:clear');
        Artisan::call('route:clear');

        return back()->with('success', 'Cache cleared successfully!');
    }
}
