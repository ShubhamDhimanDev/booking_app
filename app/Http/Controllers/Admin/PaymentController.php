<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function paymentHistory(Request $request)
    {
        $query = Payment::with(['booking', 'user']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('provider')) {
            $query->where('provider', $request->provider);
        }
        if ($request->filled('currency')) {
            $query->where('currency', $request->currency);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Sorting (whitelist columns to prevent arbitrary column injection via query string)
        $allowedSorts = ['created_at', 'amount', 'status'];
        $sort = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : 'created_at';
        $direction = strtolower((string) $request->query('direction')) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sort, $direction);
        if ($sort !== 'created_at') {
            // stable secondary sort so ties don't jump around between page loads
            $query->orderBy('created_at', 'desc');
        }

        $payments = $query->paginate(20)->withQueryString();

        // Filter dropdown options (actual values used across the app, see PaymentController/RazorpayService/PayUService)
        $statuses = [
            'pending' => 'Pending',
            'success' => 'Success',
            'failed' => 'Failed',
        ];
        $providers = [
            'razorpay' => 'Razorpay',
            'payu' => 'PayU',
            'free' => 'Free',
        ];
        $currencies = [
            'INR' => 'INR',
            'USD' => 'USD',
        ];

        return view('admin.payment.index', compact('payments', 'statuses', 'providers', 'currencies', 'sort', 'direction'));
    }
}
