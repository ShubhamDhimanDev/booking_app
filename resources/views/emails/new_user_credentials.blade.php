<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Your account details</title>
  </head>
  <body style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;line-height:1.6;color:#111827;">
    <div style="max-width:680px;margin:0 auto;padding:24px;">
      <header style="margin-bottom:16px;">
        <h1 style="margin:0;font-size:20px;color:#111827">Your account on {{ config('app.name') }}</h1>
      </header>

      <p>Hello {{ $user->name }},</p>

      <p>We've created an account for you so you can manage bookings and view your upcoming events.</p>

      <table style="width:100%;border-collapse:collapse;margin:16px 0;">
        <tr>
          <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600">Email</td>
          <td style="padding:8px;border:1px solid #e5e7eb">{{ $user->email }}</td>
        </tr>
        <tr>
          <td style="padding:8px;border:1px solid #e5e7eb;font-weight:600">Password</td>
          <td style="padding:8px;border:1px solid #e5e7eb">{{ $password }}</td>
        </tr>
      </table>

      <p>
        You can log in here: <a href="{{ url('/login') }}">{{ url('/login') }}</a>
      </p>

      <p style="color:#6b7280;font-size:13px">For security, change your password after signing in.</p>

      <footer style="margin-top:24px;color:#6b7280;font-size:13px">
        — The {{ config('app.name') }} team
      </footer>
    </div>
  </body>
</html>
