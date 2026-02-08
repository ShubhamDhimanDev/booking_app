<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function paymentHistory()
    {
        $payments = Payment::with(['booking', 'user'])
            ->latest()
            ->get();

        return view('admin.payment.index', compact('payments'));
    }
}
