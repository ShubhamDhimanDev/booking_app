@php
    $ids = array_filter((array) ($data['event_ids'] ?? []));
    $events = $country->events()
        ->when($ids, fn ($q) => $q->whereIn('id', $ids))
        ->whereDate('available_to_date', '>=', now()->toDateString())
        ->orderBy('title')->get()
        ->each(fn ($e) => $e->setRelation('country', $country));
@endphp
<section class="cms-section">
    <div class="cms-container">
        @if(!empty($data['heading']))<h2>{{ $data['heading'] }}</h2>@endif
        <div class="cms-grid">
            @forelse($events as $event)
                <div class="cms-card">
                    <h3>{{ $event->title }}</h3>
                    @if($event->description)<div class="cms-text">{{ \Illuminate\Support\Str::limit(strip_tags($event->description), 140) }}</div>@endif
                    <div class="cms-price">{{ $event->currency_symbol }}{{ number_format($event->price, 2) }}</div>
                    <a class="cms-btn" href="{{ $event->publicUrl() }}">Book now</a>
                </div>
            @empty
                <p class="cms-text">No sessions are open for booking right now.</p>
            @endforelse
        </div>
    </div>
</section>
