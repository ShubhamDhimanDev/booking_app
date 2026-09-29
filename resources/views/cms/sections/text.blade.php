<section class="cms-section">
    <div class="cms-container">
        @if(!empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
        <div class="cms-text">{!! $data['body'] ?? '' !!}</div>
    </div>
</section>
