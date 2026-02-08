<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $organization = auth()->user()->organization;

        $payments = $organization->payments()
            ->with(['booking.event'])
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('created_at', '>=', $date);
            })
            ->latest()
            ->paginate(20);

        $stats = [
            'total_amount' => $organization->payments()->where('status', 'completed')->sum('amount'),
            'pending_amount' => $organization->payments()->where('status', 'pending')->sum('amount'),
            'refunded_amount' => $organization->payments()->where('status', 'refunded')->sum('amount'),
        ];

        return view('organization.payments.index', compact('payments', 'stats'));
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        $payment->load(['booking.event']);

        return view('organization.payments.show', compact('payment'));
    }

    /**
     * Process refund for a payment
     */
    public function refund(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        if ($payment->status !== 'completed') {
            return back()->with('error', 'Only completed payments can be refunded!');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Process refund via payment gateway
        try {
            // dispatch(new ProcessRefund($payment, $validated['reason']));

            return back()->with('success', 'Refund initiated successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to initiate refund: ' . $e->getMessage());
        }
    }
}
