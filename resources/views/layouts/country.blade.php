<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $page->meta_title ?: $page->title }} | {{ config('app.name') }}</title>
    @if($page->meta_description)
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    <link rel="canonical" href="{{ $page->url() }}">
    @foreach($alternates ?? [] as $alt)
        <link rel="alternate" hreflang="{{ strtolower($alt->country->iso_code ?: $alt->country->slug) }}" href="{{ $alt->url() }}">
    @endforeach
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @include('cms.partials.styles')
    <style>
        body { margin: 0; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #0f172a; background: linear-gradient(to bottom right, #f8fafc, #eff6ff, #eef2ff); min-height: 100vh; }
        *, *::before, *::after { box-sizing: border-box; }
    </style>
    @stack('head')
</head>
<body>
    @include('cms.partials.tz-boot', ['country' => $country])
    @include('cms.partials.header', ['country' => $country])
    <main>@yield('content')</main>
    @include('cms.partials.footer', ['country' => $country])
    @stack('scripts')
</body>
</html>
