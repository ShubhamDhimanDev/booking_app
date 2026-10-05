@extends('layouts.store')

@section('title', $category ? $category->name . ' – Store' : 'Store')

@section('content')
    <h1>{{ $category?->name ?? 'Store' }}</h1>

    <form method="GET" class="st-toolbar">
        @if($category)<input type="hidden" name="category" value="{{ $category->slug }}">@endif
        @if(request()->boolean('preview'))<input type="hidden" name="preview" value="1">@endif
        <input type="search" name="q" placeholder="Search products" value="{{ request('q') }}">
        <select name="sort" onchange="this.form.submit()">
            <option value="">Featured</option>
            <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>Price: low to high</option>
            <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>Price: high to low</option>
        </select>
        <button class="cms-btn st-btn">Search</button>
    </form>

    @if($categories->isNotEmpty())
        <div class="st-chips">
            <a class="st-chip {{ ! $category ? 'active' : '' }}" href="{{ route('store.index', $country->slug) }}">All</a>
            @foreach($categories->whereNull('parent_id') as $cat)
                <a class="st-chip {{ $category?->id === $cat->id ? 'active' : '' }}" href="{{ route('store.index', [$country->slug, 'category' => $cat->slug]) }}">{{ $cat->name }}</a>
            @endforeach
        </div>
    @endif

    <div class="cms-grid">
        @forelse($products as $product)
            @php $price = $product->priceFor($country); @endphp
            <div class="st-card">
                <a href="{{ route('store.show', [$country->slug, $product->slug]) }}">
                    @if($product->first_image)
                        <img src="{{ $product->first_image }}" alt="{{ $product->name }}" loading="lazy">
                    @else
                        <div class="st-noimg"></div>
                    @endif
                </a>
                <div class="st-card-body">
                    <h3><a href="{{ route('store.show', [$country->slug, $product->slug]) }}">{{ $product->name }}</a></h3>
                    <div>@include('store.partials.price', ['price' => $price])</div>
                    @if($product->grants_free_session)<div><span class="st-badge">Includes a free session</span></div>@endif
                    <div style="margin-top:auto;padding-top:10px">
                        @if($product->inStock())
                            <form method="POST" action="{{ route('cart.add', $country->slug) }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button class="cms-btn st-btn">Add to cart</button>
                            </form>
                        @else
                            <span class="st-muted">Out of stock</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="st-muted">No products found.</p>
        @endforelse
    </div>

    <div class="st-pager">{{ $products->links() }}</div>
@endsection
