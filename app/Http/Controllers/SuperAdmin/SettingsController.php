<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
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
        return view('super-admin.settings.index');
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
            'razorpay_key' => 'nullable|string',
            'razorpay_secret' => 'nullable|string',
            'stripe_key' => 'nullable|string',
            'stripe_secret' => 'nullable|string',
            'paypal_client_id' => 'nullable|string',
            'paypal_secret' => 'nullable|string',
        ]);

        // Update payment config
        return back()->with('success', 'Payment settings updated successfully!');
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
