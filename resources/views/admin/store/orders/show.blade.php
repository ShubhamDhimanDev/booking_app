@extends('admin.layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
@php
    $sym = $order->currency_symbol;
    $invite = $order->freeSessionInvite;
    $canRefund = $order->isPaid() && in_array($order->status, ['paid', 'processing', 'shipped', 'delivered'], true)
        && ! ($refund && in_array($refund->status, ['pending', 'processing', 'completed'], true));
@endphp
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h4 class="mb-0">Order {{ $order->order_number }}
            <span class="badge bg-secondary ms-2">{{ $order->status_label }}</span>
        </h4>
        <div>
            <a href="{{ route('admin.orders.slip', $order) }}" target="_blank" class="btn btn-outline-secondary">Packing slip</a>
            <a href="{{ route('admin.orders.index') }}" class="btn btn-light">Back</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">Items</div>
                <div class="table-responsive">
                    <table class="table mb-0 align-middle">
                        <thead class="table-light"><tr><th>Product</th><th>MRP</th><th>Price</th><th>Qty</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            @foreach($order->items as $item)
                                <tr>
                                    <td>{{ $item->name }} @if($item->sku)<br><small class="text-muted">{{ $item->sku }}</small>@endif</td>
                                    <td>{{ $sym }}{{ number_format($item->mrp, 2) }}</td>
                                    <td>{{ $sym }}{{ number_format($item->price, 2) }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td class="text-end">{{ $sym }}{{ number_format($item->line_total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr><td colspan="4" class="text-end">Subtotal</td><td class="text-end">{{ $sym }}{{ number_format($order->subtotal, 2) }}</td></tr>
                            @if($order->discount > 0)
                                <tr><td colspan="4" class="text-end">Discount ({{ $order->promo_code }})</td><td class="text-end">- {{ $sym }}{{ number_format($order->discount, 2) }}</td></tr>
                            @endif
                            <tr><td colspan="4" class="text-end"><strong>Total</strong></td><td class="text-end"><strong>{{ $sym }}{{ number_format($order->total, 2) }}</strong></td></tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">Payments</div>
                <div class="table-responsive">
                    <table class="table mb-0">
                        <thead class="table-light"><tr><th>Gateway</th><th>Reference</th><th>Amount</th><th>Status</th><th>Date</th></tr></thead>
                        <tbody>
                            @forelse($order->payments as $payment)
                                <tr>
                                    <td>{{ ucfirst($payment->provider) }}</td>
                                    <td><small>{{ $payment->transaction_id ?? $payment->gateway_order_id ?? '—' }}</small></td>
                                    <td>{{ $sym }}{{ number_format($payment->amount, 2) }}</td>
                                    <td>{{ ucfirst($payment->status) }}</td>
                                    <td>{{ $payment->created_at->format('d M Y, H:i') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-muted text-center">No payment attempts.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm mb-4">
                <div class="card-header">Customer</div>
                <div class="card-body">
                    <strong>{{ $order->customer_name }}</strong><br>
                    <a href="mailto:{{ $order->customer_email }}">{{ $order->customer_email }}</a><br>
                    {{ $order->customer_phone }}
                    <hr>
                    {{ $order->address_line1 }}<br>
                    @if($order->address_line2){{ $order->address_line2 }}<br>@endif
                    {{ $order->city }}@if($order->state), {{ $order->state }}@endif {{ $order->postal_code }}<br>
                    {{ $order->address_country }}
                    @if($order->notes)<hr><small class="text-muted">Note:</small> {{ $order->notes }}@endif
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">Fulfilment</div>
                <div class="card-body">
                    @if($order->carrier || $order->tracking_number)
                        <p class="mb-2"><strong>{{ $order->carrier }}</strong> {{ $order->tracking_number }}</p>
                    @endif

                    @foreach($nextStatuses as $next)
                        <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mb-2">
                            @csrf
                            <input type="hidden" name="status" value="{{ $next }}">
                            @if($next === 'shipped')
                                <input type="text" name="carrier" class="form-control mb-2" placeholder="Carrier (e.g. Delhivery)">
                                <input type="text" name="tracking_number" class="form-control mb-2" placeholder="Tracking number">
                            @endif
                            <button class="btn btn-primary w-100">Mark as {{ \App\Models\Order::STATUSES[$next] }}</button>
                        </form>
                    @endforeach

                    @if(in_array($order->status, ['pending_payment', 'failed'], true))
                        <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('Cancel this unpaid order?');">
                            @csrf
                            <button class="btn btn-outline-danger w-100">Cancel order</button>
                        </form>
                    @endif

                    @if($canRefund)
                        <form method="POST" action="{{ route('admin.orders.refund', $order) }}" class="mt-2"
                              onsubmit="return confirm('Refund {{ $sym }}{{ number_format($order->total, 2) }} to the customer? This cannot be undone.');">
                            @csrf
                            <button class="btn btn-outline-danger w-100">Refund {{ $sym }}{{ number_format($order->total, 2) }}</button>
                        </form>
                        <small class="text-muted">Unshipped items go back into stock. Any unused free-session link is cancelled.</small>
                    @endif

                    @if($refund)
                        <p class="mt-3 mb-0 small">Refund: <a href="{{ route('admin.refunds.show', $refund) }}">#{{ $refund->id }} ({{ $refund->status }})</a></p>
                    @endif

                    @if(! $nextStatuses && ! $canRefund && ! $refund && ! in_array($order->status, ['pending_payment', 'failed'], true))
                        <p class="text-muted mb-0">No actions available.</p>
                    @endif
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">Free session</div>
                <div class="card-body">
                    @if($invite)
                        <p class="mb-2">
                            {{ $invite->event->title ?? 'Session' }}:
                            @if($invite->status === 'accepted') <span class="badge bg-success">Booked</span>
                            @elseif($invite->status === 'expired' || $invite->isExpired()) <span class="badge bg-secondary">Expired</span>
                            @else <span class="badge bg-info">Link sent, not used</span> @endif
                            <br><small class="text-muted">Valid until {{ $invite->expires_at?->format('d M Y') }}</small>
                        </p>
                    @else
                        <p class="text-muted">This order has no free session.</p>
                    @endif

                    @if($order->isPaid())
                        <form method="POST" action="{{ route('admin.orders.resend', $order) }}">
                            @csrf
                            <button class="btn btn-outline-primary w-100">Resend confirmation email</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
