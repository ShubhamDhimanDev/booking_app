@extends('layouts.store')

@section('title', 'Your cart')

@if($add = session('track_add'))
    @push('tracking')
        @include('store.partials.tracking', [
            'meta' => 'AddToCart', 'ga' => 'add_to_cart', 'currency' => $add['currency'], 'value' => $add['price'] * $add['qty'],
            'items' => [['id' => $add['id'], 'name' => $add['name'], 'price' => $add['price'], 'qty' => $add['qty']]],
        ])
    @endpush
@endif

@section('content')
    <h1>Your cart</h1>

    @if(! $summary || empty($summary['lines']))
        <p class="st-muted">Your cart is empty.</p>
        <a class="cms-btn" href="{{ route('store.index', $country->slug) }}">Continue shopping</a>
    @else
        @php $sym = config('cms.currencies.' . $summary['currency'], $summary['currency'] . ' '); @endphp

        <div class="st-cart">
            <div>
                <form method="POST" action="{{ route('cart.update', $country->slug) }}">
                    @csrf
                    <table class="st-table">
                        <thead>
                            <tr><th>Product</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr>
                        </thead>
                        <tbody>
                            @foreach($summary['lines'] as $line)
                                <tr>
                                    <td>
                                        @if($line['product'])
                                            <a href="{{ route('store.show', [$country->slug, $line['product']->slug]) }}">{{ $line['product']->name }}</a>
                                        @else
                                            <span class="st-muted">Removed product</span>
                                        @endif
                                        @if($line['error'])<div style="color:#dc2626;font-size:.85rem">{{ $line['error'] }}</div>@endif
                                    </td>
                                    <td>
                                        {{ $sym }}{{ number_format($line['price'], 2) }}
                                        @if($line['mrp'] > $line['price'])<span class="st-mrp">{{ $sym }}{{ number_format($line['mrp'], 2) }}</span>@endif
                                    </td>
                                    <td>
                                        @if($line['product'])
                                            <input class="st-qty" type="number" min="0" max="{{ \App\Services\CartService::MAX_QTY }}" name="quantities[{{ $line['product']->id }}]" value="{{ $line['quantity'] }}">
                                        @endif
                                    </td>
                                    <td>{{ $sym }}{{ number_format($line['line_total'], 2) }}</td>
                                    <td>
                                        <button type="submit" class="st-link" form="remove-{{ $line['item']->product_id }}">Remove</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <p style="margin-top:14px"><button class="cms-btn st-btn" style="background:#475569">Update cart</button></p>
                </form>

                @foreach($summary['lines'] as $line)
                    <form id="remove-{{ $line['item']->product_id }}" method="POST" action="{{ route('cart.remove', [$country->slug, $line['item']->product_id]) }}">@csrf</form>
                @endforeach
            </div>

            <aside class="st-summary">
                <div class="row"><span>Subtotal</span><span>{{ $sym }}{{ number_format($summary['subtotal'], 2) }}</span></div>
                @if($summary['savings'] > 0)
                    <div class="row" style="color:#166534"><span>You save (vs MRP)</span><span>{{ $sym }}{{ number_format($summary['savings'], 2) }}</span></div>
                @endif
                @if($summary['promo'])
                    <div class="row" style="color:#166534">
                        <span>Promo {{ $summary['promo']->code }}
                            <button type="submit" class="st-link" form="promo-remove" style="margin-left:6px">remove</button>
                        </span>
                        <span>- {{ $sym }}{{ number_format($summary['discount'], 2) }}</span>
                    </div>
                @endif
                @if($summary['promo_error'])
                    <div style="color:#dc2626;font-size:.85rem">{{ $summary['promo_error'] }}</div>
                @endif
                <div class="row total"><span>Total</span><span>{{ $sym }}{{ number_format($summary['total'], 2) }}</span></div>
                <p class="st-muted" style="margin:6px 0 0">No shipping charges or taxes are added.</p>

                @if(! $summary['promo'])
                    <form method="POST" action="{{ route('cart.promo', $country->slug) }}" style="display:flex;gap:8px;margin-top:18px">
                        @csrf
                        <input type="text" name="promo_code" placeholder="Promo code" style="flex:1;min-width:0;padding:9px 12px;border:1px solid #cbd5e1;border-radius:8px;font:inherit">
                        <button class="cms-btn st-btn" style="padding:9px 16px">Apply</button>
                    </form>
                @endif
                <form id="promo-remove" method="POST" action="{{ route('cart.promo.remove', $country->slug) }}">@csrf</form>

                <p style="margin-top:20px">
                    @if($summary['has_issues'])
                        <button class="cms-btn st-btn" style="width:100%" disabled>Fix the items marked above</button>
                    @else
                        <a class="cms-btn" style="display:block;text-align:center" href="{{ route('checkout.show', $country->slug) }}">Checkout</a>
                    @endif
                </p>
            </aside>
        </div>
    @endif
@endsection
