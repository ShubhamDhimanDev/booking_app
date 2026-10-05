@extends('layouts.store')

@section('title', $product->meta_title ?: $product->name)
@section('description', $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($product->short_description ?: $product->description)), 160, ''))

@push('head')
    <link rel="canonical" href="{{ route('store.show', [$country->slug, $product->slug]) }}">
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'sku' => $product->sku,
        'description' => strip_tags((string) ($product->short_description ?: $product->description)),
        'image' => $product->images ?: null,
        'offers' => [
            '@type' => 'Offer',
            'priceCurrency' => $price->currency,
            'price' => number_format($price->sale_price, 2, '.', ''),
            'availability' => $product->inStock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url' => route('store.show', [$country->slug, $product->slug]),
        ],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
@endpush

@push('tracking')
    @include('store.partials.tracking', [
        'meta' => 'ViewContent', 'ga' => 'view_item', 'currency' => $price->currency, 'value' => (float) $price->sale_price,
        'items' => [['id' => $product->sku ?: $product->id, 'name' => $product->name, 'price' => (float) $price->sale_price, 'qty' => 1]],
    ])
@endpush

@section('content')
    <p class="st-muted"><a href="{{ route('store.index', $country->slug) }}">&larr; Back to store</a></p>

    <div class="st-product">
        <div class="st-gallery">
            @if($product->first_image)
                <img id="st-main" src="{{ $product->first_image }}" alt="{{ $product->name }}">
                @if(count($product->images) > 1)
                    <div class="st-thumbs">
                        @foreach($product->images as $img)
                            <img src="{{ $img }}" alt="" onclick="document.getElementById('st-main').src = this.src">
                        @endforeach
                    </div>
                @endif
            @else
                <div class="st-noimg" style="border-radius:14px"></div>
            @endif
        </div>

        <div>
            @if($product->category)<p class="st-muted" style="margin:0 0 6px">{{ $product->category->name }}</p>@endif
            <h1>{{ $product->name }}</h1>
            <div style="margin-bottom:14px">@include('store.partials.price', ['price' => $price])</div>

            @if($product->short_description)<p class="cms-text">{{ $product->short_description }}</p>@endif

            @if($hasFreeSession)
                <p><span class="st-badge">Includes a free session</span>
                <span class="st-muted">&nbsp;We email you a link to book your slot after you order.</span></p>
            @endif

            @if($product->inStock())
                <form method="POST" action="{{ route('cart.add', $country->slug) }}" style="display:flex;gap:10px;align-items:center;margin:18px 0">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input class="st-qty" type="number" name="quantity" value="1" min="1" max="{{ $product->track_stock ? min(\App\Services\CartService::MAX_QTY, $product->stock_qty) : \App\Services\CartService::MAX_QTY }}">
                    <button class="cms-btn st-btn">Add to cart</button>
                </form>
                @if($product->track_stock && $product->stock_qty <= 5)
                    <p class="st-muted">Only {{ $product->stock_qty }} left.</p>
                @endif
            @else
                <p style="color:#dc2626;font-weight:600">Out of stock</p>
            @endif

            @if($product->description)
                <div class="cms-text" style="margin-top:22px">{!! nl2br(e($product->description)) !!}</div>
            @endif
        </div>
    </div>
@endsection
