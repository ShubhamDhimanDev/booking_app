@extends('layouts.store')

@section('title', $product->meta_title ?: $product->name)
@section('description', $product->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) ($product->short_description ?: $product->description)), 160, ''))
@section('body_class', 'yp-body')

@php
    $c = fn (string $key, $default = null) => $product->content($key, $default);
    $money = fn ($v) => $price->currency_symbol . number_format($v, fmod((float) $v, 1) == 0 ? 0 : 2);
    $inStock = $product->inStock();
    $cfg = config('product_page');
    $who = $cfg['practitioner'];

    $subtitle = $c('subtitle', $product->short_description);
    $benefits = $c('benefits', []);
    $highlights = $c('highlights', []);
    $reviews = $c('reviews', []);
    $faq = $c('faq', []);
    $specs = $c('specs', []);
    $consultUrl = $c('consult_url');
    $hasContent = ! empty($product->content);
    $aboutText = $c('about', (string) $product->description);
    $material = collect($specs)->first(fn ($s) => strtolower($s['key']) === 'material')['value'] ?? null;

    $reviewCount = count($reviews);
    $avg = $reviewCount ? collect($reviews)->avg('rating') : 0;
    $stars = fn ($n) => str_repeat('★', (int) round($n)) . str_repeat('☆', 5 - (int) round($n));
    $images = $product->images ?: [];
    $name = $product->name;
@endphp

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
    @if($faq)
    <script type="application/ld+json">
    {!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => array_map(fn ($f) => [
            '@type' => 'Question',
            'name' => $f['q'],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f['a']],
        ], $faq),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
    @include('store.partials.yp-styles')
@endpush

@push('tracking')
    @include('store.partials.tracking', [
        'meta' => 'ViewContent', 'ga' => 'view_item', 'currency' => $price->currency, 'value' => (float) $price->sale_price,
        'items' => [['id' => $product->sku ?: $product->id, 'name' => $product->name, 'price' => (float) $price->sale_price, 'qty' => 1]],
    ])
@endpush

@section('content')
<div class="yp">
    <nav class="yp-bc" aria-label="Breadcrumb">
        <a href="{{ $country->url() }}">Home</a> <span>›</span>
        <a href="{{ route('store.index', $country->slug) }}">Store</a> <span>›</span>
        @if($product->category)
            <a href="{{ route('store.index', [$country->slug, 'category' => $product->category->slug]) }}">{{ $product->category->name }}</a> <span>›</span>
        @endif
        <b>{{ $name }}</b>
    </nav>

    {{-- HERO --}}
    <div class="yp-hero">
        <div>
            <div class="yp-frame">
                <span class="yp-stock {{ $inStock ? '' : 'out' }}">{{ $inStock ? 'In Stock' : 'Out of Stock' }}</span>
                @if($images)
                    <img id="yp-main" src="{{ $images[0] }}" alt="{{ $name }}">
                @else
                    <div class="yp-yantra-wrap">@include('store.partials.yp-yantra')</div>
                @endif
            </div>
            @if(count($images) > 1)
                <div class="yp-thumbs">
                    @foreach($images as $i => $img)
                        <button type="button" class="yp-thumb {{ $i === 0 ? 'active' : '' }}" data-src="{{ $img }}" aria-label="Show image {{ $i + 1 }}"><img src="{{ $img }}" alt=""></button>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="yp-info">
            @if($product->category)<span class="yp-cat">@include('store.partials.yp-icon', ['n' => 'tag', 'size' => 11]){{ $product->category->name }}</span>@endif
            <div>
                <h1 class="yp-title">{{ $name }}</h1>
                @if($subtitle)<div class="yp-sub">{{ $subtitle }}</div>@endif
            </div>

            @if($benefits)
                <ul class="yp-benefits">
                    @foreach($benefits as $b)
                        <li><span class="yp-dot">@include('store.partials.yp-icon', ['n' => 'check', 'size' => 10, 'w' => 2.5])</span>{{ $b }}</li>
                    @endforeach
                </ul>
            @endif

            @if($c('purpose'))<div class="yp-purpose">{{ $c('purpose') }}</div>@endif

            @if($reviewCount)
                <div class="yp-rating"><span class="yp-stars">{{ $stars($avg) }}</span><span class="yp-rcount">{{ number_format($avg, 1) }} · {{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</span></div>
            @endif

            @if($highlights)
                <div class="yp-pills">@foreach($highlights as $h)<span class="yp-pill">{{ $h }}</span>@endforeach</div>
            @endif

            <div class="yp-price-row">
                <span class="yp-price">{{ $money($price->sale_price) }}</span>
                @if($price->sale_price < $price->mrp)
                    <span class="yp-mrp">{{ $money($price->mrp) }}</span>
                    <span class="yp-off">{{ $price->discount_percent }}% off</span>
                @endif
            </div>

            <div class="yp-trust">
                @foreach($cfg['trust_cues'] as $cue)
                    @continue(! empty($cue['consult']) && ! $hasFreeSession)
                    <div>@include('store.partials.yp-icon', ['n' => $cue['icon'], 'w' => 1.8]){{ $cue['text'] }}</div>
                @endforeach
            </div>

            @if($material)
                <div>
                    <div class="yp-var-label">Material</div>
                    <span class="yp-vpill">{{ $material }}</span>
                </div>
            @endif

            <form id="yp-add" method="POST" action="{{ route('cart.add', $country->slug) }}" style="display:contents">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button class="yp-btn" @disabled(! $inStock)>{{ $inStock ? 'Add to cart — ' . $money($price->sale_price) : 'Out of stock' }}</button>
            </form>
            @if($inStock && $product->track_stock && $product->stock_qty <= 5)
                <div class="yp-low">Only {{ $product->stock_qty }} left.</div>
            @endif
            @if($consultUrl)<a class="yp-btn ghost" href="{{ $consultUrl }}">Book a consultation first</a>@endif

            <div class="yp-delivery">
                @foreach($cfg['delivery'] as $d)
                    @continue(! empty($d['consult']) && ! $hasFreeSession)
                    <div>@include('store.partials.yp-icon', ['n' => $d['icon'], 'size' => 13, 'w' => 1.8]){{ $d['text'] }}</div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="yp-content">
        {{-- BENEFITS --}}
        @if($c('benefit_cards'))
            <section class="yp-sec">
                <span class="yp-eyebrow">What it does for you</span>
                <h2 class="yp-h">Benefits</h2>
                @if($c('benefits_intro'))<p class="yp-p" style="margin-bottom:24px">{{ $c('benefits_intro') }}</p>@endif
                <div class="yp-grid">
                    @foreach($c('benefit_cards') as $card)
                        <div class="yp-card col">
                            <div class="yp-card-h"><span class="yp-ico">@include('store.partials.yp-icon', ['n' => 'check', 'size' => 15, 'w' => 1.8])</span>{{ $card['title'] }}</div>
                            <p class="yp-cp">{{ $card['body'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- WHO IT IS FOR --}}
        @if($c('who_yes') || $c('who_no'))
            <section class="yp-sec">
                <span class="yp-eyebrow">Right fit</span>
                <h2 class="yp-h">Who this is for — and who it is not</h2>
                <div class="yp-two">
                    @if($c('who_yes'))
                        <div class="yp-box yes">
                            <div class="yp-box-h">This is for you if</div>
                            @foreach($c('who_yes') as $t)<div class="yp-i">@include('store.partials.yp-icon', ['n' => 'check', 'size' => 12, 'w' => 2.5]){{ $t }}</div>@endforeach
                        </div>
                    @endif
                    @if($c('who_no'))
                        <div class="yp-box no">
                            <div class="yp-box-h">This is not for you if</div>
                            @foreach($c('who_no') as $t)<div class="yp-i">@include('store.partials.yp-icon', ['n' => 'x', 'size' => 12, 'w' => 2.5]){{ $t }}</div>@endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- ABOUT --}}
        @if($aboutText)
            <section class="yp-sec">
                <span class="yp-eyebrow">About this product</span>
                <h2 class="yp-h">{{ $c('about_heading', 'What is the ' . $name . '?') }}</h2>
                @foreach(preg_split('/\R{2,}/', trim($aboutText)) as $para)
                    <p class="yp-p">{!! nl2br(e($para)) !!}</p>
                @endforeach
            </section>
        @endif

        {{-- SIGNS --}}
        @if($c('signs'))
            <section class="yp-sec">
                <span class="yp-eyebrow">Is this for you?</span>
                <h2 class="yp-h">Signs this may be for you</h2>
                <div class="yp-grid">
                    @foreach($c('signs') as $s)
                        <div class="yp-card"><span class="yp-ico">@include('store.partials.yp-icon', ['n' => 'check', 'size' => 15, 'w' => 1.8])</span><p class="yp-cp">{{ $s }}</p></div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- HOW IT WORKS --}}
        @if($c('steps'))
            <section class="yp-sec">
                <span class="yp-eyebrow">The process</span>
                <h2 class="yp-h">{{ $c('steps_heading', 'How the ' . $name . ' works') }}</h2>
                @foreach($c('steps') as $i => $step)
                    <div class="yp-step">
                        <div class="yp-num">{{ $i + 1 }}</div>
                        <div><div class="yp-step-h">{{ $step['title'] }}</div><p class="yp-step-p">{{ $step['body'] }}</p></div>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- ENERGISING --}}
        @if($c('energy_quote') || $c('energy_checks'))
            <section class="yp-sec">
                <span class="yp-eyebrow">The energising process</span>
                <h2 class="yp-h">Personally energised — not mass-produced</h2>
                <div class="yp-energy">
                    <div class="yp-who">
                        <div class="yp-avatar">{{ $who['initials'] }}</div>
                        <div><div class="yp-who-n">{{ $who['name'] }}</div><div class="yp-who-r">{{ $who['role'] }}</div></div>
                    </div>
                    @if($c('energy_quote'))<p class="yp-quote">“{{ $c('energy_quote') }}”</p>@endif
                    <div class="yp-checks">
                        @foreach($c('energy_checks', []) as $t)<div>@include('store.partials.yp-icon', ['n' => 'check', 'size' => 12, 'w' => 2.5]){{ $t }}</div>@endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- USAGE --}}
        @if($c('usage'))
            <section class="yp-sec">
                <span class="yp-eyebrow">Usage and care</span>
                <h2 class="yp-h">How to use + care instructions</h2>
                <div class="yp-grid">
                    @foreach($c('usage') as $u)
                        <div class="yp-card use"><div class="yp-use-h">{{ $u['title'] }}</div><p class="yp-use-p">{{ $u['body'] }}</p></div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- EXPECT --}}
        @if($c('expect_yes') || $c('expect_no'))
            <section class="yp-sec">
                <span class="yp-eyebrow">Setting expectations</span>
                <h2 class="yp-h">What to expect</h2>
                <div class="yp-two">
                    @if($c('expect_yes'))
                        <div class="yp-box yes">
                            <div class="yp-box-h">You may notice over time</div>
                            @foreach($c('expect_yes') as $t)<div class="yp-i">@include('store.partials.yp-icon', ['n' => 'check', 'size' => 12, 'w' => 2.5]){{ $t }}</div>@endforeach
                        </div>
                    @endif
                    @if($c('expect_no'))
                        <div class="yp-box no">
                            <div class="yp-box-h">What not to expect</div>
                            @foreach($c('expect_no') as $t)<div class="yp-i">@include('store.partials.yp-icon', ['n' => 'x', 'size' => 12, 'w' => 2.5]){{ $t }}</div>@endforeach
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- COMPARISON --}}
        @if($hasContent)
            <section class="yp-sec">
                <span class="yp-eyebrow">The difference</span>
                <h2 class="yp-h">How this is different from regular astrology products</h2>
                <div style="overflow-x:auto">
                    <table class="yp-table">
                        <thead><tr><th>Aspect</th><th>Astro Chaitanya</th><th>Typical products</th></tr></thead>
                        <tbody>
                            @foreach($cfg['comparison'] as [$aspect, $ours, $theirs])
                                <tr>
                                    <td>{{ $aspect }}</td>
                                    <td class="good"><span>@include('store.partials.yp-icon', ['n' => 'check', 'size' => 13, 'w' => 2.5]){{ $ours }}</span></td>
                                    <td class="bad"><span>@include('store.partials.yp-icon', ['n' => 'x', 'size' => 13]){{ $theirs }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- PRACTITIONER NOTE --}}
        @if($c('note_quote'))
            <section class="yp-sec">
                <div class="yp-note">
                    <div class="yp-avatar">{{ $who['initials'] }}</div>
                    <div>
                        <div class="yp-who-n">{{ $who['name'] }}</div>
                        <div class="yp-who-r">{{ $who['role'] }}</div>
                        <p class="yp-note-q">“{{ $c('note_quote') }}”</p>
                    </div>
                </div>
            </section>
        @endif

        {{-- SPECS --}}
        @if($specs)
            <section class="yp-sec">
                <span class="yp-eyebrow">Specifications</span>
                <h2 class="yp-h">Product details</h2>
                <div class="yp-specs">
                    @foreach($specs as $s)<div class="yp-spec"><b>{{ $s['key'] }}</b><span>{{ $s['value'] }}</span></div>@endforeach
                </div>
            </section>
        @endif

        {{-- WHAT THIS IS NOT --}}
        @if($c('not_cards'))
            <section class="yp-sec">
                <span class="yp-eyebrow">Important to understand</span>
                <h2 class="yp-h">What this is not</h2>
                @if($c('not_intro'))<p class="yp-p">{{ $c('not_intro') }}</p>@endif
                <div class="yp-grid" style="margin-top:20px">
                    @foreach($c('not_cards') as $n)
                        <div class="yp-card"><span class="yp-ico">@include('store.partials.yp-icon', ['n' => 'ban', 'size' => 15, 'w' => 1.8])</span><p class="yp-cp"><strong>{{ $n['title'] }}</strong> {{ $n['body'] }}</p></div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- REVIEWS --}}
        @if($reviewCount)
            <section class="yp-sec">
                <span class="yp-eyebrow">Real experiences</span>
                <h2 class="yp-h">Reviews</h2>
                <div class="yp-rbar">
                    <div class="yp-big"><div class="yp-big-n">{{ number_format($avg, 1) }}</div><span class="yp-stars">{{ $stars($avg) }}</span><div class="yp-rcount">{{ $reviewCount }} {{ \Illuminate\Support\Str::plural('review', $reviewCount) }}</div></div>
                    <div class="yp-bars">
                        @foreach([5, 4, 3, 2, 1] as $star)
                            @php $n = collect($reviews)->where('rating', $star)->count(); @endphp
                            <div class="yp-bar"><b>{{ $star }}★</b><i><u style="width:{{ round($n / $reviewCount * 100) }}%"></u></i><span>{{ $n }}</span></div>
                        @endforeach
                    </div>
                </div>
                @foreach($reviews as $r)
                    <div class="yp-rv">
                        <div class="yp-rv-top">
                            <div><div class="yp-rv-n">{{ $r['name'] }}</div>@if($r['location'])<div class="yp-rv-l">{{ $r['location'] }}</div>@endif</div>
                            <span class="yp-stars" style="font-size:13px;letter-spacing:0">{{ $stars($r['rating']) }}</span>
                        </div>
                        <p class="yp-rv-q">“{{ $r['text'] }}”</p>
                        @if($r['tag'])<span class="yp-rv-tag">{{ $r['tag'] }}</span>@endif
                    </div>
                @endforeach
            </section>
        @endif

        {{-- FAQ --}}
        @if($faq)
            <section class="yp-sec">
                <span class="yp-eyebrow">Common questions</span>
                <h2 class="yp-h">Frequently asked questions</h2>
                <div class="yp-faq">
                    @foreach($faq as $f)<details><summary>{{ $f['q'] }}</summary><p>{{ $f['a'] }}</p></details>@endforeach
                </div>
            </section>
        @endif

        {{-- RELATED --}}
        @if($related->isNotEmpty())
            <section class="yp-sec">
                <span class="yp-eyebrow">Explore more</span>
                <h2 class="yp-h">Other products</h2>
                <div class="yp-rel">
                    @foreach($related as $rp)
                        @php $rprice = $rp->priceFor($country); @endphp
                        <a class="yp-rel-card" href="{{ route('store.show', [$country->slug, $rp->slug]) }}">
                            <div class="yp-rel-img">
                                @if($rp->first_image)<img src="{{ $rp->first_image }}" alt="" loading="lazy">@else<span style="color:#C9973A">@include('store.partials.yp-icon', ['n' => 'shield', 'size' => 56, 'w' => 1])</span>@endif
                            </div>
                            <div class="yp-rel-n">{{ $rp->name }}</div>
                            @if($rp->short_description)<div class="yp-rel-u">{{ \Illuminate\Support\Str::limit($rp->short_description, 60) }}</div>@endif
                            <div class="yp-rel-p">{{ $price->currency_symbol }}{{ number_format($rprice->sale_price, fmod((float) $rprice->sale_price, 1) == 0 ? 0 : 2) }}</div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- FIXED BAR --}}
    <div class="yp-sticky">
        <div class="yp-sticky-in">
            <div class="yp-sticky-l">
                <div><div class="yp-sticky-n">{{ $name }}</div><div class="yp-sticky-s">{{ $material ? $material . ' · ' : '' }}{{ $inStock ? 'In stock' : 'Out of stock' }}</div></div>
                <div class="yp-sticky-p">{{ $money($price->sale_price) }}</div>
            </div>
            <div class="yp-sticky-r">
                @if($consultUrl)<a class="yp-btn ghost" href="{{ $consultUrl }}">Book consultation first</a>@endif
                <button class="yp-btn" form="yp-add" @disabled(! $inStock)>{{ $inStock ? 'Add to cart' : 'Out of stock' }}</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.yp-thumb').forEach(function (t) {
    t.addEventListener('click', function () {
        document.getElementById('yp-main').src = t.dataset.src;
        document.querySelectorAll('.yp-thumb').forEach(function (x) { x.classList.remove('active'); });
        t.classList.add('active');
    });
});
</script>
@endpush
