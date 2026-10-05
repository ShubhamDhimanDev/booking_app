@extends('layouts.app')

@section('title', 'Order ' . $order->order_number)

@section('content')
@php
    $sym = $order->currency_symbol;
    $invite = $order->freeSessionInvite;
    $steps = ['paid' => 'Paid', 'processing' => 'Processing', 'shipped' => 'Shipped', 'delivered' => 'Delivered'];
    $reached = array_search($order->status, array_keys($steps), true);
@endphp
<div class="mb-8">
    <a href="{{ route('user.orders.index') }}" class="text-sm text-slate-500 hover:text-primary">&larr; All orders</a>
    <h1 class="mt-2 text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight">Order {{ $order->order_number }}</h1>
    <p class="text-slate-500 dark:text-slate-400">Placed {{ $order->created_at->format('d M Y, H:i') }}</p>
</div>

@if($reached !== false)
    <div class="flex flex-wrap gap-3 mb-8">
        @foreach($steps as $key => $label)
            <span class="px-4 py-1.5 rounded-full text-xs font-bold {{ $loop->index <= $reached ? 'bg-primary text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-500' }}">{{ $label }}</span>
        @endforeach
    </div>
@else
    <p class="mb-8"><span class="inline-flex px-3 py-1 bg-slate-100 dark:bg-slate-700 rounded-full text-xs font-bold uppercase">{{ $order->status_label }}</span></p>
@endif

@if($order->tracking_number || $order->carrier)
    <div class="mb-8 p-5 rounded-2xl bg-white dark:bg-slate-800 border border-slate-100 dark:border-slate-700">
        <p class="text-xs uppercase font-bold text-slate-400 mb-1">Shipment</p>
        <p class="font-semibold text-slate-900 dark:text-white">{{ $order->carrier }} {{ $order->tracking_number }}</p>
    </div>
@endif

@if($invite && $invite->status === 'pending' && ! $invite->isExpired() && $order->isPaid())
    <div class="mb-8 p-6 rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700">
        <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-1">Your free session</h2>
        <p class="text-slate-600 dark:text-slate-300 mb-4">Book it before {{ $invite->expires_at->format('j F Y') }}.</p>
        <a href="{{ url('/followup/' . $invite->token) }}" class="inline-block px-5 py-2.5 rounded-xl bg-primary text-white font-semibold">Book your free session</a>
    </div>
@elseif($invite && $invite->status === 'accepted')
    <p class="mb-8 text-sm text-emerald-700 dark:text-emerald-400">Your free session has been booked. See it under My Bookings.</p>
@endif

<div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden mb-8">
    <table class="w-full">
        <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
            @foreach($order->items as $item)
                <tr>
                    <td class="px-6 py-4 text-sm text-slate-800 dark:text-slate-200">{{ $item->name }}</td>
                    <td class="px-6 py-4 text-sm text-slate-500">{{ $item->quantity }} &times; {{ $sym }}{{ number_format($item->price, 2) }}</td>
                    <td class="px-6 py-4 text-sm font-semibold text-right text-slate-900 dark:text-white">{{ $sym }}{{ number_format($item->line_total, 2) }}</td>
                </tr>
            @endforeach
            @if($order->discount > 0)
                <tr><td class="px-6 py-3 text-sm text-slate-500" colspan="2">Discount ({{ $order->promo_code }})</td><td class="px-6 py-3 text-sm text-right">- {{ $sym }}{{ number_format($order->discount, 2) }}</td></tr>
            @endif
            <tr><td class="px-6 py-4 font-bold" colspan="2">Total</td><td class="px-6 py-4 font-bold text-right">{{ $sym }}{{ number_format($order->total, 2) }}</td></tr>
        </tbody>
    </table>
</div>

<p class="text-sm text-slate-500 dark:text-slate-400">
    Delivering to {{ $order->customer_name }}, {{ collect([$order->address_line1, $order->address_line2, $order->city, $order->state, $order->postal_code, $order->address_country])->filter()->implode(', ') }}
</p>
@endsection
