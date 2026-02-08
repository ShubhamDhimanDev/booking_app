<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function edit()
    {
        $current = Setting::getSetting('payment_gateway', 'razorpay');
        $gateways = ['razorpay', 'payu'];

        $settings = [];
        foreach ($gateways as $gateway) {
            $settings[$gateway] = [
                'key_id' => Setting::getSetting($gateway . '_key_id', ''),
                'key_secret' => Setting::getSetting($gateway . '_key_secret', ''),
                'merchant_key' => Setting::getSetting($gateway . '_merchant_key', ''),
                'merchant_id' => Setting::getSetting($gateway . '_merchant_id', ''),
                'merchant_salt' => Setting::getSetting($gateway . '_merchant_salt', ''),
            ];
        }

        return view('admin.payment_gateway.edit', [
            'current' => $current,
            'gateways' => $gateways,
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'gateway' => 'required|in:razorpay,payu',
            'razorpay_key_id' => 'nullable|string',
            'razorpay_key_secret' => 'nullable|string',
            'payu_merchant_key' => 'nullable|string',
            'payu_merchant_id' => 'nullable|string',
            'payu_merchant_salt' => 'nullable|string',
        ]);

        // Update active gateway
        Setting::setSetting('payment_gateway', $request->gateway, false);

        // Store Razorpay credentials (encrypted)
        if ($request->filled('razorpay_key_id')) {
            Setting::setSetting('razorpay_key_id', $request->razorpay_key_id, true);
        }
        if ($request->filled('razorpay_key_secret')) {
            Setting::setSetting('razorpay_key_secret', $request->razorpay_key_secret, true);
        }

        // Store PayU credentials (encrypted)
        if ($request->filled('payu_merchant_key')) {
            Setting::setSetting('payu_merchant_key', $request->payu_merchant_key, true);
        }
        if ($request->filled('payu_merchant_id')) {
            Setting::setSetting('payu_merchant_id', $request->payu_merchant_id, true);
        }
        if ($request->filled('payu_merchant_salt')) {
            Setting::setSetting('payu_merchant_salt', $request->payu_merchant_salt, true);
        }

        return redirect()->back()->with('success', 'Payment gateway settings updated.');
    }
}
