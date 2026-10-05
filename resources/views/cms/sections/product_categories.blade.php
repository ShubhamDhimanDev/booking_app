@if($country->ecommerce_enabled)
@php
    $categories = \App\Models\ProductCategory::where('is_active', true)->whereNull('parent_id')
        ->whereHas('products', fn ($q) => $q->soldIn($country))
        ->orderBy('sort_order')->orderBy('name')->get();
@endphp
@if($categories->isNotEmpty())
<section class="cms-section">
    <div class="cms-container">
        @if(!empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
        <div class="cms-grid">
            @foreach($categories as $category)
                <a class="cms-card" style="text-decoration:none;color:inherit" href="{{ route('store.index', [$country->slug, 'category' => $category->slug]) }}">
                    @if($category->image)<img src="{{ $category->image }}" alt="" loading="lazy" style="width:100%;aspect-ratio:16/10;object-fit:cover;border-radius:10px;margin-bottom:12px">@endif
                    <h3>{{ $category->name }}</h3>
                </a>
            @endforeach
        </div>
    </div>
</section>
@endif
@endif
