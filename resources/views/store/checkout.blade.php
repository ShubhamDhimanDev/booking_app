@extends('layouts.store')

@section('title', 'Checkout')

@push('tracking')
    @include('store.partials.tracking', [
        'meta' => 'InitiateCheckout', 'ga' => 'begin_checkout', 'currency' => $summary['currency'], 'value' => $summary['total'],
        'items' => collect($summary['lines'])->map(fn ($l) => ['id' => $l['product']->sku ?: $l['product']->id, 'name' => $l['product']->name, 'price' => $l['price'], 'qty' => $l['quantity']])->all(),
    ])
@endpush

@section('content')
    <h1>Checkout</h1>
    @php $sym = config('cms.currencies.' . $summary['currency'], $summary['currency'] . ' '); @endphp

    @if($errors->any())
        <div class="st-flash danger">
            @foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="st-cart">
        <form method="POST" action="{{ route('checkout.place', $country->slug) }}" class="st-summary">
            @csrf
            <h3 style="margin-top:0">Delivery details</h3>
            <style>
                .st-field { display: block; margin-bottom: 14px; }
                .st-field span { display: block; font-size: .85rem; color: #475569; margin-bottom: 4px; }
                .st-field input, .st-field textarea { width: 100%; padding: 10px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
                .st-two { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
                @media (max-width: 600px) { .st-two { grid-template-columns: 1fr; } }
            </style>
            <div class="st-two">
                <label class="st-field"><span>Full name</span><input name="customer_name" value="{{ old('customer_name', $user?->name) }}" required></label>
                <label class="st-field"><span>Email</span><input type="email" name="customer_email" value="{{ old('customer_email', $user?->email) }}" required></label>
            </div>
            <label class="st-field"><span>Phone</span><input name="customer_phone" value="{{ old('customer_phone', $user?->phone) }}" required></label>
            <label class="st-field"><span>Address line 1</span><input name="address_line1" value="{{ old('address_line1') }}" required></label>
            <label class="st-field"><span>Address line 2 (optional)</span><input name="address_line2" value="{{ old('address_line2') }}"></label>
            <div class="st-two">
                <label class="st-field"><span>City</span><input name="city" value="{{ old('city') }}" required></label>
                <label class="st-field"><span>State</span><input name="state" value="{{ old('state') }}"></label>
            </div>
            <div class="st-two">
                <label class="st-field"><span>Postal code</span><input name="postal_code" value="{{ old('postal_code') }}" required></label>
                <label class="st-field"><span>Country</span><input value="{{ $country->name }}" disabled></label>
            </div>
            <label class="st-field"><span>Order notes (optional)</span><textarea name="notes" rows="2">{{ old('notes') }}</textarea></label>
            <button class="cms-btn st-btn">Pay {{ $sym }}{{ number_format($summary['total'], 2) }}</button>
        </form>

        <aside class="st-summary">
            <h3 style="margin-top:0">Order summary</h3>
            @foreach($summary['lines'] as $line)
                <div class="row"><span>{{ $line['quantity'] }} &times; {{ $line['product']->name }}</span><span>{{ $sym }}{{ number_format($line['line_total'], 2) }}</span></div>
            @endforeach
            @if($summary['promo'])
                <div class="row" style="color:#166534"><span>Promo {{ $summary['promo']->code }}</span><span>- {{ $sym }}{{ number_format($summary['discount'], 2) }}</span></div>
            @endif
            <div class="row total"><span>Total</span><span>{{ $sym }}{{ number_format($summary['total'], 2) }}</span></div>
            <p class="st-muted">No shipping charges or taxes are added.</p>
            @if($summary['lines'] && collect($summary['lines'])->contains(fn ($l) => $l['product']->grants_free_session))
                <p><span class="st-badge">Includes a free session</span><br><span class="st-muted">The booking link is emailed after payment.</span></p>
            @endif
            <p><a href="{{ route('cart.show', $country->slug) }}">&larr; Back to cart</a></p>
        </aside>
    </div>
@endsection
