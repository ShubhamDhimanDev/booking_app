@extends('layouts.store')

@section('title', 'Payment failed')

@section('content')
    <h1>Payment was not completed</h1>
    <p class="cms-text">Order <strong>{{ $order->order_number }}</strong> was not paid, so you have not been charged for it. If money was deducted from your account, it is refunded automatically by your bank or the gateway within a few days; contact us with the order number if it isn't.</p>
    <a class="cms-btn" href="{{ route('cart.show', $country->slug) }}">Back to your cart</a>
@endsection
