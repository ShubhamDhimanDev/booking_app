@extends('layouts.store')

@section('title', 'Order ' . $order->order_number)

{{-- Purchase fires once per browser session, even if the page is reloaded. --}}
@if($order->isPaid() && ! session('tracked_order_' . $order->id))
    @php session(['tracked_order_' . $order->id => true]); @endphp
    @push('tracking')
        @include('store.partials.tracking', [
            'meta' => 'Purchase', 'ga' => 'purchase', 'currency' => $order->currency, 'value' => (float) $order->total,
            'transactionId' => $order->order_number,
            'items' => $order->items->map(fn ($i) => ['id' => $i->sku ?: $i->product_id, 'name' => $i->name, 'price' => (float) $i->price, 'qty' => $i->quantity])->all(),
        ])
    @endpush
@endif

@section('content')
    @php $sym = $order->currency_symbol; $invite = $order->freeSessionInvite; @endphp

    @if($order->isPaid())
        <h1>Thank you, {{ $order->customer_name }}!</h1>
        <p class="cms-text">Your order <strong>{{ $order->order_number }}</strong> is confirmed. A confirmation email is on its way to {{ $order->customer_email }}.</p>
    @else
        <h1>We're confirming your payment</h1>
        <p class="cms-text">Order <strong>{{ $order->order_number }}</strong> is waiting for the payment gateway to confirm. This page and your email will update once it does; you can safely refresh in a minute.</p>
    @endif

    @if($invite && $invite->status === 'pending')
        <div class="st-summary" style="margin:20px 0;background:#eef2ff">
            <h3 style="margin-top:0">Your free session</h3>
            <p class="cms-text">Your order includes a free session. Choose a slot that suits you (link valid until {{ $invite->expires_at->format('j F Y') }}).</p>
            <a class="cms-btn" href="{{ url('/followup/' . $invite->token) }}">Book your free session</a>
        </div>
    @endif

    <table class="st-table" style="margin-top:20px">
        <thead><tr><th>Item</th><th>Qty</th><th>Total</th></tr></thead>
        <tbody>
            @foreach($order->items as $item)
                <tr><td>{{ $item->name }}</td><td>{{ $item->quantity }}</td><td>{{ $sym }}{{ number_format($item->line_total, 2) }}</td></tr>
            @endforeach
            @if($order->discount > 0)
                <tr><td colspan="2">Discount ({{ $order->promo_code }})</td><td>- {{ $sym }}{{ number_format($order->discount, 2) }}</td></tr>
            @endif
            <tr><td colspan="2"><strong>Total paid</strong></td><td><strong>{{ $sym }}{{ number_format($order->total, 2) }}</strong></td></tr>
        </tbody>
    </table>

    <p class="st-muted" style="margin-top:18px">
        Delivering to: {{ collect([$order->address_line1, $order->address_line2, $order->city, $order->state, $order->postal_code, $order->address_country])->filter()->implode(', ') }}
    </p>
    <p><a href="{{ route('store.index', $country->slug) }}">Continue shopping</a></p>
@endsection
