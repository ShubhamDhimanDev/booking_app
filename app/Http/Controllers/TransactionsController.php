<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class TransactionsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $payments = Payment::where('user_id', $user->id)->latest()->get();

        return view('user.transactions.index', compact('payments'));
    }
}
