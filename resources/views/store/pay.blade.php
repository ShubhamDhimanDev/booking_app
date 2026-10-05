@extends('layouts.store')

@section('title', 'Payment')

@section('content')
    <h1>Complete your payment</h1>
    @php $sym = $order->currency_symbol; @endphp
    <p class="st-muted">Order {{ $order->order_number }} &middot; {{ $sym }}{{ number_format($order->total, 2) }}</p>

    @if($payment->provider === 'payu')
        <p>Taking you to PayU&hellip;</p>
        <form id="payu-form" method="POST" action="{{ $gateway['payu_url'] ?? 'https://secure.payu.in/_payment' }}">
            @foreach(collect($gateway)->except(['gateway', 'payu_url', 'success']) as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <noscript><button class="cms-btn st-btn">Continue to PayU</button></noscript>
        </form>
        @push('scripts')
            <script>document.getElementById('payu-form').submit();</script>
        @endpush
    @else
        <p id="rzp-msg" class="st-muted">Opening the payment window&hellip;</p>
        <button id="rzp-btn" class="cms-btn st-btn" type="button">Pay {{ $sym }}{{ number_format($order->total, 2) }}</button>
        <p style="margin-top:14px"><a href="{{ route('cart.show', $country->slug) }}">Cancel and return to cart</a></p>

        <form id="rzp-form" method="POST" action="{{ \Illuminate\Support\Facades\URL::signedRoute('order.razorpay.verify', [$country->slug, $order->order_number]) }}">
            @csrf
            <input type="hidden" name="razorpay_order_id">
            <input type="hidden" name="razorpay_payment_id">
            <input type="hidden" name="razorpay_signature">
        </form>

        @push('scripts')
            <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
            <script>
                function openRzp() {
                    const rzp = new Razorpay({
                        key: @json($gateway['key'] ?? ''),
                        order_id: @json($gateway['order_id'] ?? ''),
                        amount: @json((int) round($order->total * 100)),
                        currency: @json($order->currency),
                        name: @json(config('app.name')),
                        description: @json('Order ' . $order->order_number),
                        prefill: { name: @json($order->customer_name), email: @json($order->customer_email), contact: @json($order->customer_phone) },
                        handler: function (r) {
                            const f = document.getElementById('rzp-form');
                            f.razorpay_order_id.value = r.razorpay_order_id;
                            f.razorpay_payment_id.value = r.razorpay_payment_id;
                            f.razorpay_signature.value = r.razorpay_signature;
                            document.getElementById('rzp-msg').textContent = 'Confirming your payment…';
                            f.submit();
                        },
                        modal: { ondismiss: function () { document.getElementById('rzp-msg').textContent = 'Payment window closed. Click the button to try again.'; } },
                        theme: { color: '#4f46e5' }
                    });
                    rzp.open();
                }
                document.getElementById('rzp-btn').addEventListener('click', openRzp);
                window.addEventListener('load', openRzp);
            </script>
        @endpush
    @endif
@endsection
