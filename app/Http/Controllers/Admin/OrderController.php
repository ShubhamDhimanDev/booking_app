<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use RuntimeException;

class OrderController extends Controller
{
    public function __construct(protected OrderService $orders)
    {
        $this->middleware('role:admin|owner');
    }

    public function index(Request $request)
    {
        $orders = $this->filtered($request)->with('country')->withCount('items')->paginate(20)->withQueryString();

        return view('admin.store.orders.index', [
            'orders' => $orders,
            'countries' => Country::orderBy('name')->get(),
            'statuses' => Order::STATUSES,
            'stats' => [
                'to_ship' => Order::whereIn('status', ['paid', 'processing'])->count(),
                'revenue' => Order::where('payment_status', 'paid')->selectRaw('currency, SUM(total) as total')->groupBy('currency')->pluck('total', 'currency'),
            ],
        ]);
    }

    public function show(Order $order)
    {
        $order->load(['items.product', 'country', 'user', 'payments', 'freeSessionInvite.event']);

        return view('admin.store.orders.show', [
            'order' => $order,
            'nextStatuses' => OrderService::TRANSITIONS[$order->status] ?? [],
            'refund' => \App\Models\Refund::where('order_id', $order->id)->latest('id')->first(),
        ]);
    }

    public function slip(Order $order)
    {
        return view('admin.store.orders.slip', ['order' => $order->load('items')]);
    }

    public function status(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|string',
            'carrier' => 'nullable|string|max:100',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        return $this->attempt(fn () => $this->orders->advance($order, $data['status'], $data['carrier'] ?? null, $data['tracking_number'] ?? null), 'Order updated.');
    }

    public function cancel(Order $order)
    {
        return $this->attempt(fn () => $this->orders->cancelUnpaid($order), 'Order cancelled.');
    }

    public function refund(Request $request, Order $order)
    {
        return $this->attempt(function () use ($request, $order) {
            $this->orders->startRefund($order, $request->user());
        }, 'Refund started. The order closes when the gateway confirms it; follow it under Refunds.');
    }

    public function resend(Order $order)
    {
        return $this->attempt(fn () => $this->orders->resendConfirmation($order), 'Confirmation email sent.');
    }

    public function export(Request $request)
    {
        $orders = $this->filtered($request)->with('country')->get();

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Order', 'Date', 'Country', 'Customer', 'Email', 'Phone', 'Items', 'Currency', 'Subtotal', 'Discount', 'Total', 'Promo', 'Status', 'Payment', 'Carrier', 'Tracking', 'City', 'Postal code']);
            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->order_number, $o->created_at->format('Y-m-d H:i'), $o->country?->name, $o->customer_name, $o->customer_email,
                    $o->customer_phone, $o->items()->sum('quantity'), $o->currency, $o->subtotal, $o->discount, $o->total, $o->promo_code,
                    $o->status_label, $o->payment_status, $o->carrier, $o->tracking_number, $o->city, $o->postal_code,
                ]);
            }
            fclose($out);
        }, 'orders-' . now()->format('Ymd-His') . '.csv', ['Content-Type' => 'text/csv']);
    }

    protected function filtered(Request $request)
    {
        return Order::query()->latest()
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('country_id'), fn ($q) => $q->where('country_id', $request->country_id))
            ->when($request->filled('date_from'), fn ($q) => $q->where('created_at', '>=', $request->date_from . ' 00:00:00'))
            ->when($request->filled('date_to'), fn ($q) => $q->where('created_at', '<=', $request->date_to . ' 23:59:59'))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w
                ->where('order_number', 'like', '%' . $request->q . '%')
                ->orWhere('customer_email', 'like', '%' . $request->q . '%')
                ->orWhere('customer_name', 'like', '%' . $request->q . '%')
                ->orWhere('customer_phone', 'like', '%' . $request->q . '%')));
    }

    protected function attempt(callable $action, string $success)
    {
        try {
            $action();
        } catch (RuntimeException $e) {
            return back()->with(['alert_type' => 'danger', 'alert_message' => $e->getMessage()]);
        }

        return back()->with(['alert_type' => 'success', 'alert_message' => $success]);
    }
}
