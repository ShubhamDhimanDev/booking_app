<?php

namespace App\Http\Controllers;

use App\Models\Payment;

class TransactionsController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $payments = Payment::where('user_id', $user->id)->latest()->paginate(10);

        return view('user.transactions.index', compact('payments'));
    }
}
