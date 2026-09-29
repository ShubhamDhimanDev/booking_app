<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="3;url={{ $default->url() }}">
    <title>{{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background: #f8fafc;
            color: #1e293b;
            text-align: center;
        }
        .wrap { padding: 2rem; max-width: 28rem; }
        .spinner {
            width: 2.5rem;
            height: 2.5rem;
            margin: 0 auto 1.5rem;
            border: 3px solid #e2e8f0;
            border-top-color: #4f46e5;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        p { color: #475569; margin: 0 0 1.5rem; }
        .links { display: flex; gap: 1.5rem; justify-content: center; flex-wrap: wrap; }
        a { color: #4f46e5; font-weight: 600; text-decoration: none; font-size: 0.95rem; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="spinner"></div>
        <p>Taking you to the right site&hellip;</p>
        <div class="links">
            @foreach($countries as $country)
                <a href="{{ $country->url() }}">Continue to {{ $country->name }} site</a>
            @endforeach
        </div>
    </div>

    <script>
        (function () {
            try {
                var countries = @json($countries->map(fn ($c) => ['url' => $c->url(), 'iso' => $c->iso_code, 'tz' => $c->default_timezone])->values());
                var fallback = @json($default->url());

                var tz = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
                var langs = navigator.languages && navigator.languages.length ? navigator.languages : [navigator.language || ''];

                function offsetMinutes(zone) {
                    try {
                        var parts = new Intl.DateTimeFormat('en-US', { timeZone: zone, timeZoneName: 'shortOffset' }).formatToParts(new Date());
                        var name = parts.filter(function (p) { return p.type === 'timeZoneName'; })[0].value; // GMT+5:30
                        var m = name.match(/GMT([+-])(\d+)(?::(\d+))?/);
                        if (!m) return 0;
                        return (m[1] === '-' ? -1 : 1) * (parseInt(m[2], 10) * 60 + parseInt(m[3] || '0', 10));
                    } catch (e) { return null; }
                }

                var match = null;
                // 1. exact timezone match
                countries.forEach(function (c) { if (!match && c.tz === tz) match = c; });
                // 2. browser locale region (en-US -> US)
                if (!match) {
                    langs.forEach(function (l) {
                        var region = (l.split('-')[1] || '').toUpperCase();
                        countries.forEach(function (c) { if (!match && region && c.iso === region) match = c; });
                    });
                }
                // 3. same continent prefix (America/* -> a country whose default zone is America/*)
                if (!match && tz.indexOf('/') > -1) {
                    var continent = tz.split('/')[0];
                    countries.forEach(function (c) { if (!match && c.tz.split('/')[0] === continent) match = c; });
                }
                // 4. same UTC offset right now
                if (!match) {
                    var mine = -(new Date().getTimezoneOffset());
                    countries.forEach(function (c) { if (!match && offsetMinutes(c.tz) === mine) match = c; });
                }

                location.replace((match || { url: fallback }).url);
            } catch (e) {
                // Detection unsupported: the meta-refresh above and the manual links handle this visitor.
            }
        })();
    </script>
</body>
</html>
