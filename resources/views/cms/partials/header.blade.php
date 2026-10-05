@include('cms.partials.styles')
@if(trim((string) $country->header_html) !== '')
    {!! $country->header_html !!}
@else
    <header class="cms-header">
        <div class="cms-container">
            <a href="{{ $country->url() }}" class="cms-brand">
                <img src="{{ asset('images/AC-Logo.png') }}" alt="" onerror="this.remove()">
                <span>{{ config('app.name') }}</span>
            </a>
            <nav class="cms-nav">
                @foreach($country->headerItems()->with('page.country')->get() as $item)
                    <a href="{{ $item->href() }}" @if($item->opens_new_tab) target="_blank" rel="noopener" @endif>{{ $item->label }}</a>
                @endforeach
                @if($country->ecommerce_enabled)
                    <a href="{{ route('store.index', $country->slug) }}">Store</a>
                    <a href="{{ route('cart.show', $country->slug) }}">Cart ({{ app(\App\Services\CartService::class)->count($country) }})</a>
                @endif
                @auth
                    <a href="{{ route('user.bookings.index') }}">My Bookings</a>
                @else
                    <a href="{{ route('login') }}">Login</a>
                @endauth
            </nav>
        </div>
    </header>
@endif
