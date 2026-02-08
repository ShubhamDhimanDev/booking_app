<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Exception;

class TestRazorpayRefundController extends Controller
{
    protected $razorpay;

    public function __construct()
    {
        $this->razorpay = new Api(
            Setting::getSetting('razorpay_key_id') ?: env('RAZORPAY_KEY_ID'),
            Setting::getSetting('razorpay_key_secret') ?: env('RAZORPAY_KEY_SECRET')
        );
    }

    /**
     * Show refund test page
     */
    public function index()
    {
        return view('razorpay.refund-test');
    }

    /**
     * Process refund
     */
    public function refund(Request $request)
    {
        $request->validate([
            'payment_id' => 'required|string',
            'amount' => 'nullable|numeric|min:1', // in INR
        ]);

        try {
            $paymentId = $request->payment_id;

            $data = [];

            // Razorpay expects amount in paise
            if ($request->amount) {
                $data['amount'] = $request->amount * 100;
            }

            $refund = $this->razorpay
                ->payment
                ->fetch($paymentId)
                ->refund($data);

            return back()->with([
                'success' => 'Refund successful',
                'refund' => json_encode($refund, JSON_PRETTY_PRINT),
            ]);

        } catch (Exception $e) {
            return back()->withErrors([
                'error' => $e->getMessage(),
            ]);
        }
    }
}
