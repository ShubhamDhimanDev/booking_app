<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class TransactionsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Unfinished store-order payment attempts are not transactions yet.
        $payments = Payment::with('order')->where('user_id', $user->id)
            ->where(fn ($q) => $q->whereNull('order_id')->orWhere('status', '!=', 'pending'))
            ->latest()->paginate(10);

        return view('user.transactions.index', compact('payments'));
    }
}
