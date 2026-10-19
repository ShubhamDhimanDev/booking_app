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
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    @include('cms.partials.styles')
    <style>
        /* Store theme (navy + gold). Scoped to this layout only: booking/event/CMS pages never load it. */
        body.st-theme {
            --ink: #373c80; --gold: #C9973A; --gold-pale: #F9F0DC; --gold-faint: #FDF8F0; --cream: #FAF6EF; --parchment: #F5EDD8;
            --muted: #7A6F5E; --soft: #3D3422; --border: rgba(201,151,58,.18); --field: rgba(201,151,58,.5);
            --red: #8B1A1A; --red-pale: #F8EEEE; --green: #1A5C2C; --green-pale: #EEF8F1;
            margin: 0; font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; color: #0f172a; background: var(--cream); min-height: 100vh;
        }
        *, *::before, *::after { box-sizing: border-box; }
        .st-wrap { padding: 36px 0 0; font-family: 'DM Sans', sans-serif; color: var(--ink); }
        .st-wrap h1 { margin: 0 0 20px; font-family: 'Cormorant Garamond', serif; font-size: 2.5rem; font-weight: 500; line-height: 1.15; color: var(--ink); }
        .st-wrap h3 { font-family: 'Cormorant Garamond', serif; font-weight: 600; color: var(--ink); }
        .st-wrap a:not([class]) { color: var(--ink); text-decoration: underline; text-decoration-color: var(--gold); text-underline-offset: 3px; }
        .st-wrap .cms-text { color: var(--soft); }

        .st-wrap .cms-btn { background: var(--ink); color: var(--parchment) !important; border-radius: 2px; box-shadow: none; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500; letter-spacing: .04em; padding: 13px 26px; transition: background .2s; }
        .st-wrap .cms-btn:hover { background: #2d3070; transform: none; box-shadow: none; }
        .st-wrap .cms-btn.st-btn-alt { background: transparent; color: var(--ink) !important; border: 1px solid var(--ink); }
        .st-wrap .cms-btn.st-btn-alt:hover { background: var(--ink); color: var(--parchment) !important; }
        .st-btn { border: 0; cursor: pointer; font: inherit; }
        .st-wrap .st-btn[disabled] { background: #9a9ab5; cursor: not-allowed; }

        .st-flash { padding: 12px 16px; border-radius: 2px; margin-bottom: 18px; font-size: .95rem; border: 1px solid transparent; }
        .st-flash.success { background: var(--green-pale); color: var(--green); border-color: rgba(26,92,44,.15); }
        .st-flash.warning { background: var(--gold-pale); color: #7a5a14; border-color: var(--border); }
        .st-flash.danger { background: var(--red-pale); color: var(--red); border-color: rgba(139,26,26,.12); }

        .st-toolbar { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 22px; }
        .st-toolbar input, .st-toolbar select, .st-input { padding: 10px 14px; border: 1px solid var(--field); border-radius: 2px; background: #fff; font: inherit; color: var(--ink); }
        .st-toolbar input:focus, .st-toolbar select:focus, .st-qty:focus, .st-input:focus, .st-field input:focus, .st-field textarea:focus { outline: 2px solid rgba(201,151,58,.4); border-color: var(--gold); }
        .st-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 22px; }
        .st-chip { padding: 6px 16px; border: 1px solid var(--border); border-radius: 2px; background: #fff; color: var(--muted); text-decoration: none; font-size: .85rem; letter-spacing: .03em; transition: all .2s; }
        .st-wrap a.st-chip { color: var(--muted); }
        .st-wrap a.st-chip:hover { border-color: var(--gold); color: var(--gold); }
        .st-chip.active, .st-wrap a.st-chip.active { background: var(--gold-pale); border-color: var(--gold); color: var(--gold); }

        .st-card { background: #fff; border: 1px solid var(--border); border-radius: 4px; overflow: hidden; display: flex; flex-direction: column; transition: border-color .2s; }
        .st-card:hover { border-color: var(--gold); }
        .st-card img, .st-noimg { width: 100%; aspect-ratio: 1 / 1; object-fit: cover; background: var(--ink); display: block; }
        .st-noimg { display: flex; align-items: center; justify-content: center; }
        .st-noimg svg { width: 70%; height: 70%; }
        .st-card-body { padding: 16px; display: flex; flex-direction: column; gap: 6px; flex: 1; }
        .st-card h3 { margin: 0; font-size: 1.3rem; font-weight: 600; line-height: 1.25; }
        .st-wrap .st-card h3 a, .st-card h3 a { color: var(--ink); text-decoration: none; }
        .st-price { font-family: 'Cormorant Garamond', serif; font-weight: 500; font-size: 1.6rem; color: var(--ink); }
        .st-mrp { color: var(--muted); text-decoration: line-through; font-weight: 400; font-size: .95rem; margin-left: 6px; }
        .st-off { background: var(--green-pale); color: var(--green); font-size: .72rem; font-weight: 500; padding: 3px 8px; border-radius: 2px; margin-left: 6px; }
        .st-badge { display: inline-block; background: var(--gold-pale); color: var(--gold); font-size: .7rem; font-weight: 500; padding: 4px 10px; border-radius: 2px; letter-spacing: .05em; text-transform: uppercase; }
        .st-muted { color: var(--muted); font-size: .9rem; }
        .st-ok { color: var(--green); }
        .st-err { color: var(--red); font-size: .85rem; }

        .st-product { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 36px; }
        .st-qty { width: 72px; padding: 10px; border: 1px solid var(--field); border-radius: 2px; font: inherit; color: var(--ink); background: #fff; }

        .st-table { width: 100%; border-collapse: separate; border-spacing: 0; background: #fff; border: 1px solid var(--border); border-radius: 4px; overflow: hidden; }
        .st-table th, .st-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid var(--border); vertical-align: middle; font-size: .92rem; }
        .st-table tbody tr:last-child td { border-bottom: 0; }
        .st-table th { background: var(--ink); color: var(--gold); font-size: .7rem; font-weight: 500; letter-spacing: .05em; text-transform: uppercase; }
        .st-cart { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 28px; align-items: start; }
        .st-summary { background: #fff; border: 1px solid var(--border); border-radius: 4px; padding: 24px; }
        .st-summary.st-accent { background: var(--gold-faint); border-left: 3px solid var(--gold); }
        .st-summary h3 { font-size: 1.35rem; }
        .st-summary .row { display: flex; justify-content: space-between; gap: 12px; margin: 8px 0; }
        .st-summary .total { border-top: 1px solid var(--border); padding-top: 12px; margin-top: 12px; font-family: 'Cormorant Garamond', serif; font-weight: 600; font-size: 1.4rem; }
        .st-link { background: none; border: 0; color: var(--red); cursor: pointer; font: inherit; padding: 0; }
        .st-pager { margin-top: 28px; }
        .st-pager nav svg { width: 18px; }

        .st-field { display: block; margin-bottom: 14px; }
        .st-field span { display: block; font-size: .72rem; font-weight: 500; letter-spacing: .05em; text-transform: uppercase; color: var(--muted); margin-bottom: 4px; }
        .st-field input, .st-field textarea { width: 100%; padding: 10px 12px; border: 1px solid var(--field); border-radius: 2px; font: inherit; color: var(--ink); background: #fff; }
        .st-field input:disabled { background: var(--gold-faint); color: var(--muted); }
        .st-two { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }

        @media (max-width: 800px) {
            .st-product, .st-cart { grid-template-columns: 1fr; }
            .st-wrap h1 { font-size: 2rem; }
            .st-cart form:not(.st-summary) { overflow-x: auto; }
        }
        @media (max-width: 600px) { .st-two { grid-template-columns: 1fr; } }
    </style>
</head>
<body class="st-theme @yield('body_class')">
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
