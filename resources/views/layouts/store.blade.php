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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @include('cms.partials.styles')
    <style>
        body { margin: 0; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #0f172a; background: linear-gradient(to bottom right, #f8fafc, #eff6ff, #eef2ff); min-height: 100vh; }
        *, *::before, *::after { box-sizing: border-box; }
        .st-wrap { padding: 36px 0 0; }
        .st-wrap h1 { margin: 0 0 20px; font-size: 1.9rem; font-weight: 800; color: #0f172a; }
        .st-wrap a { color: #6366f1; }
        .st-flash { padding: 12px 16px; border-radius: 10px; margin-bottom: 18px; font-size: .95rem; }
        .st-flash.success { background: #dcfce7; color: #166534; }
        .st-flash.warning { background: #fef9c3; color: #854d0e; }
        .st-flash.danger { background: #fee2e2; color: #991b1b; }
        .st-toolbar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 22px; }
        .st-toolbar input, .st-toolbar select { padding: 10px 14px; border: 1px solid #e2e8f0; border-radius: 12px; background: #fff; font: inherit; }
        .st-toolbar input:focus, .st-toolbar select:focus, .st-qty:focus { outline: 2px solid #c7d2fe; border-color: #6366f1; }
        .st-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
        .st-chip { padding: 6px 14px; border: 1px solid #cbd5e1; border-radius: 999px; background: #fff; color: #475569; text-decoration: none; font-size: .9rem; }
        .st-chip.active { background: #6366f1; border-color: #6366f1; color: #fff; }
        .st-wrap a.st-chip { color: #475569; } .st-wrap a.st-chip.active { color: #fff; }
        .st-card { background: #fff; border: 1px solid #f1f5f9; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,.08), 0 4px 6px -4px rgba(0,0,0,.08); overflow: hidden; display: flex; flex-direction: column; transition: box-shadow .3s, transform .3s; }
        .st-card:hover { box-shadow: 0 20px 25px -5px rgba(0,0,0,.1), 0 8px 10px -6px rgba(0,0,0,.1); transform: translateY(-2px); }
        .st-card img, .st-noimg { width: 100%; aspect-ratio: 1 / 1; object-fit: cover; background: linear-gradient(135deg, #e0e7ff, #f1f5f9); display: block; }
        .st-card-body { padding: 16px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
        .st-card h3 { margin: 0; font-size: 1rem; font-weight: 700; }
        .st-wrap .st-card h3 a { color: #0f172a; }
        .st-card h3 a { color: inherit; text-decoration: none; }
        .st-price { font-weight: 800; font-size: 1.15rem; color: #6366f1; }
        .st-mrp { color: #94a3b8; text-decoration: line-through; font-weight: 400; font-size: .95rem; margin-left: 6px; }
        .st-off { background: #dcfce7; color: #166534; font-size: .78rem; font-weight: 600; padding: 2px 8px; border-radius: 999px; margin-left: 6px; }
        .st-badge { display: inline-block; background: #e0e7ff; color: #3730a3; font-size: .8rem; font-weight: 600; padding: 4px 10px; border-radius: 999px; }
        .st-muted { color: #64748b; font-size: .9rem; }
        .st-btn { border: 0; cursor: pointer; font: inherit; }
        .st-btn[disabled] { background: #94a3b8; cursor: not-allowed; }
        .st-product { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 36px; }
        .st-gallery img { width: 100%; border-radius: 24px; display: block; background: #e2e8f0; }
        .st-thumbs { display: flex; gap: 10px; margin-top: 10px; flex-wrap: wrap; }
        .st-thumbs img { width: 72px; height: 72px; object-fit: cover; border-radius: 8px; cursor: pointer; border: 2px solid transparent; }
        .st-qty { width: 72px; padding: 11px; border: 1px solid #e2e8f0; border-radius: 12px; font: inherit; }
        .st-table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border: 1px solid #f1f5f9; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,.08); overflow: hidden; }
        .st-table th, .st-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        .st-table th { background: #f8fafc; font-size: .85rem; color: #475569; }
        .st-cart { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 28px; align-items: start; }
        .st-summary { background: #fff; border: 1px solid #f1f5f9; border-radius: 24px; box-shadow: 0 10px 15px -3px rgba(0,0,0,.08); padding: 24px; }
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
