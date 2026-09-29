<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Not available in your country | {{ config('app.name') }}</title>
    @include('cms.partials.styles')
    <style>
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1e293b; background: #f8fafc; }
        *, *::before, *::after { box-sizing: border-box; }
        .cms-unavailable { text-align: center; padding: 96px 0 48px; }
        .cms-unavailable h1 { font-size: 1.8rem; margin: 0 0 12px; }
        .cms-unavailable p { color: #475569; margin: 0 0 26px; }
    </style>
</head>
<body>
    @include('cms.partials.header', ['country' => $country])
    <main class="cms-unavailable">
        <div class="cms-container">
            <h1>This event is not available in your country</h1>
            <p>&ldquo;{{ $event->title }}&rdquo; can&rsquo;t be booked from {{ $country->name }}.</p>
            <a class="cms-btn" href="{{ $country->url() }}">See sessions available in {{ $country->name }}</a>
        </div>
    </main>
    @include('cms.partials.footer', ['country' => $country])
</body>
</html>
