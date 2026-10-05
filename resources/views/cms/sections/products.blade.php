@if($country->ecommerce_enabled)
@php
    $ids = array_filter((array) ($data['product_ids'] ?? []));
    $limit = max(1, min(24, (int) ($data['limit'] ?? 0) ?: 8));
    $items = \App\Models\Product::soldIn($country)
        ->with(['prices' => fn ($q) => $q->where('country_id', $country->id)])
        ->when($ids, fn ($q) => $q->whereIn('id', $ids), fn ($q) => $q->orderByDesc('is_featured')->orderByDesc('id')->limit($limit))
        ->get();
@endphp
@if($items->isNotEmpty())
<section class="cms-section">
    <div class="cms-container">
        @if(!empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
        <div class="cms-grid">
            @foreach($items as $product)
                @php $price = $product->priceFor($country); @endphp
                <div class="cms-card">
                    @if($product->first_image)
                        <a href="{{ route('store.show', [$country->slug, $product->slug]) }}"><img src="{{ $product->first_image }}" alt="{{ $product->name }}" loading="lazy" style="width:100%;aspect-ratio:1/1;object-fit:cover;border-radius:10px;margin-bottom:12px"></a>
                    @endif
                    <h3><a href="{{ route('store.show', [$country->slug, $product->slug]) }}" style="color:inherit;text-decoration:none">{{ $product->name }}</a></h3>
                    <div class="cms-price">
                        {{ $price->currency_symbol }}{{ number_format($price->sale_price, 2) }}
                        @if($price->sale_price < $price->mrp)
                            <span style="color:#94a3b8;text-decoration:line-through;font-weight:400;font-size:.95rem;margin-left:6px">{{ $price->currency_symbol }}{{ number_format($price->mrp, 2) }}</span>
                        @endif
                    </div>
                    <a class="cms-btn" href="{{ route('store.show', [$country->slug, $product->slug]) }}">View</a>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif
@endif
