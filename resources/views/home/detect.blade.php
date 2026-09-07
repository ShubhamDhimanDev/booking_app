<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="3;url={{ route('home.in') }}">
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
        .links { display: flex; gap: 1.5rem; justify-content: center; }
        a { color: #4f46e5; font-weight: 600; text-decoration: none; font-size: 0.95rem; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="spinner"></div>
        <p>Taking you to the right site&hellip;</p>
        <div class="links">
            <a href="{{ route('home.in') }}">Continue to India site</a>
            <a href="{{ route('home.us') }}">Continue to US site</a>
        </div>
    </div>

    <script>
        (function () {
            try {
                // UTC offset in minutes, positive = ahead of UTC. IST is a fixed
                // UTC+5:30 (no DST), so this is +330 for every India-based visitor
                // regardless of which IANA zone name their OS reports (Asia/Kolkata
                // vs. the older Asia/Calcutta alias, etc.) — offset-based detection
                // sidesteps that naming inconsistency entirely.
                var offsetMinutes = -(new Date().getTimezoneOffset());
                var isIndia = offsetMinutes === 330;
                var target = isIndia ? '{{ route('home.in') }}' : '{{ route('home.us') }}';
                location.replace(target);
            } catch (e) {
                // Detection unsupported — the meta-refresh above and the manual
                // links handle this visitor instead.
            }
        })();
    </script>
</body>
</html>
