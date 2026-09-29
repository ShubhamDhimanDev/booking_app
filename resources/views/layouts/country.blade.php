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
    @include('cms.partials.styles')
    <style>
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1e293b; background: #f8fafc; }
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
