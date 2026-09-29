<section class="cms-section">
    <div class="cms-container">
        @if(!empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
        <div class="cms-grid">
            @foreach($data['items'] ?? [] as $item)
                <div class="cms-card">
                    <p class="cms-quote">&ldquo;{{ $item['quote'] ?? '' }}&rdquo;</p>
                    <strong>{{ $item['author'] ?? '' }}</strong>
                </div>
            @endforeach
        </div>
    </div>
</section>
