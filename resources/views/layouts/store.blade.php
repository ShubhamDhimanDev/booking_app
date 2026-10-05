<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') | {{ config('app.name') }}</title>
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @endif
    @stack('head')
    {!! \App\Services\TrackingService::getBaseScript() !!}
    {!! \App\Services\TrackingService::getGoogleBaseScript() !!}
    @include('cms.partials.styles')
    <style>
        body { margin: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #1e293b; background: #f8fafc; }
        *, *::before, *::after { box-sizing: border-box; }
        .st-wrap { padding: 36px 0 0; }
        .st-wrap h1 { margin: 0 0 20px; font-size: 1.9rem; color: #0f172a; }
        .st-flash { padding: 12px 16px; border-radius: 10px; margin-bottom: 18px; font-size: .95rem; }
        .st-flash.success { background: #dcfce7; color: #166534; }
        .st-flash.warning { background: #fef9c3; color: #854d0e; }
        .st-flash.danger { background: #fee2e2; color: #991b1b; }
        .st-toolbar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 22px; }
        .st-toolbar input, .st-toolbar select { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; background: #fff; font: inherit; }
        .st-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
        .st-chip { padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 999px; background: #fff; color: #475569; text-decoration: none; font-size: .9rem; }
        .st-chip.active { background: #4f46e5; border-color: #4f46e5; color: #fff; }
        .st-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; display: flex; flex-direction: column; }
        .st-card img, .st-noimg { width: 100%; aspect-ratio: 1 / 1; object-fit: cover; background: #e2e8f0; display: block; }
        .st-card-body { padding: 16px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
        .st-card h3 { margin: 0; font-size: 1rem; }
        .st-card h3 a { color: inherit; text-decoration: none; }
        .st-price { font-weight: 700; font-size: 1.15rem; color: #4f46e5; }
        .st-mrp { color: #94a3b8; text-decoration: line-through; font-weight: 400; font-size: .95rem; margin-left: 6px; }
        .st-off { background: #dcfce7; color: #166534; font-size: .78rem; font-weight: 600; padding: 2px 8px; border-radius: 999px; margin-left: 6px; }
        .st-badge { display: inline-block; background: #e0e7ff; color: #3730a3; font-size: .8rem; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
        .st-muted { color: #64748b; font-size: .9rem; }
        .st-btn { border: 0; cursor: pointer; font: inherit; }
        .st-btn[disabled] { background: #94a3b8; cursor: not-allowed; }
        .st-product { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 36px; }
        .st-gallery img { width: 100%; border-radius: 14px; display: block; background: #e2e8f0; }
        .st-thumbs { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .st-thumbs img { width: 72px; height: 72px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid transparent; }
        .st-qty { width: 72px; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
        .st-table { width: 100%; border-collapse: collapse; background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; overflow: hidden; }
        .st-table th, .st-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .st-table th { background: #f1f5f9; font-size: .85rem; color: #475569; }
        .st-cart { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 28px; align-items: start; }
        .st-summary { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 22px; }
        .st-summary .row { display: flex; justify-content: space-between; margin: 8px 0; }
        .st-summary .total { border-top: 1px solid #e2e8f0; padding-top: 12px; margin-top: 12px; font-weight: 700; font-size: 1.15rem; }
        .st-link { background: none; border: 0; color: #dc2626; cursor: pointer; font: inherit; padding: 0; }
        .st-pager { margin-top: 28px; }
        .st-pager nav svg { width: 18px; }
        @media (max-width: 800px) {
            .st-product, .st-cart { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    @include('cms.partials.tz-boot', ['country' => $country])
    @include('cms.partials.header', ['country' => $country])
    <main class="st-wrap">
        <div class="cms-container">
            @if(session('alert_type'))
                <div class="st-flash {{ session('alert_type') }}">{{ session('alert_message') }}</div>
            @endif
            @yield('content')
        </div>
    </main>
    @include('cms.partials.footer', ['country' => $country])
    @stack('scripts')
    @stack('tracking')
</body>
</html>
