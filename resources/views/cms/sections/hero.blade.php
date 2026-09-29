<section class="cms-hero" @if(!empty($data['image'])) style="background-image: linear-gradient(rgba(15,23,42,.55), rgba(15,23,42,.55)), url('{{ $data['image'] }}')" @endif>
    <div class="cms-container">
        @if(!empty($data['heading']))<h1>{{ $data['heading'] }}</h1>@endif
        @if(!empty($data['subheading']))<p>{{ $data['subheading'] }}</p>@endif
        @if(!empty($data['button_label']) && !empty($data['button_url']))
            <a class="cms-btn" href="{{ $data['button_url'] }}">{{ $data['button_label'] }}</a>
        @endif
    </div>
</section>
