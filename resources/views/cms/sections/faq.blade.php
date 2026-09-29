<section class="cms-section">
    <div class="cms-container cms-faq">
        @if(!empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
        @foreach($data['items'] ?? [] as $item)
            <details>
                <summary>{{ $item['question'] ?? '' }}</summary>
                <p>{!! nl2br(e($item['answer'] ?? '')) !!}</p>
            </details>
        @endforeach
    </div>
</section>
