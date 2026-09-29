@if(!empty($data['image']))
<section class="cms-section cms-image">
    <div class="cms-container">
        @if(!empty($data['link']))<a href="{{ $data['link'] }}">@endif
            <img src="{{ $data['image'] }}" alt="{{ $data['alt'] ?? '' }}">
        @if(!empty($data['link']))</a>@endif
    </div>
</section>
@endif
