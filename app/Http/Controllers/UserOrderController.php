<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class UserOrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $this->ownOrders($request)
            ->whereIn('payment_status', ['paid', 'refunded']) // unpaid or failed attempts are not orders yet
            ->with('country')->withCount('items')->latest()->paginate(10);

        return view('user.orders.index', compact('orders'));
    }

    public function show(Request $request, Order $order)
    {
        abort_unless($this->ownOrders($request)->whereKey($order->id)->exists(), 404);

        $order->load(['items', 'country', 'freeSessionInvite']);

        return view('user.orders.show', compact('order'));
    }

    /** Orders placed while logged in, plus guest orders made with the account's verified email. */
    protected function ownOrders(Request $request)
    {
        $user = $request->user();

        return Order::where(function ($q) use ($user) {
            $q->where('user_id', $user->id);
            if ($user->email_verified_at) {
                $q->orWhere(fn ($g) => $g->whereNull('user_id')->where('customer_email', $user->email));
            }
        });
    }
}
