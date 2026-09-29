<section class="cms-section cms-cta">
    <div class="cms-container">
        @if(!empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
        @if(!empty($data['text']))<p class="cms-text">{{ $data['text'] }}</p>@endif
        @if(!empty($data['button_label']) && !empty($data['button_url']))
            <a class="cms-btn" href="{{ $data['button_url'] }}">{{ $data['button_label'] }}</a>
        @endif
    </div>
</section>
