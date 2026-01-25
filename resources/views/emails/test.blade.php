<!doctype html>
<html>
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Test Email</title>
  </head>
  <body style="font-family:system-ui,-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;line-height:1.6;color:#111827;background:#f8fafc;margin:0;padding:0;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc;padding:40px 20px;">
      <tr>
        <td align="center">
          <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.07);">
          <table width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 6px rgba(0,0,0,0.07);">

            <!-- Header with gradient -->
            <tr>
              <td style="background:linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);padding:40px 32px;text-align:center;">
                <div style="display:inline-block;background:rgba(255,255,255,0.2);width:64px;height:64px;border-radius:16px;margin-bottom:16px;">
                  <svg width="32" height="32" viewBox="0 0 24 24" fill="none" style="margin:16px auto;display:block;">
                    <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z" fill="#ffffff"/>
                  </svg>
                </div>
                <h1 style="margin:0;font-size:32px;font-weight:700;color:#ffffff;letter-spacing:-0.5px;">{{ config('app.name') }}</h1>
                <p style="margin:8px 0 0;color:rgba(255,255,255,0.9);font-size:15px;font-weight:500;">Email Configuration Test</p>
              </td>
            </tr>

            <!-- Main Content -->
            <tr>
              <td style="padding:40px 32px;">
                <div style="text-align:center;margin-bottom:32px;">
                  <div style="display:inline-block;background:#f0fdf4;width:56px;height:56px;border-radius:50%;margin-bottom:16px;padding:14px 0;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" style="display:block;margin:0 auto;">
                      <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z" fill="#22c55e"/>
                    </svg>
                  </div>
                  <h2 style="margin:0 0 12px;font-size:26px;font-weight:700;color:#111827;">Hello {{ $name ?? 'Guest' }}!</h2>
                  <p style="margin:0;color:#6366f1;font-size:15px;font-weight:600;">✨ Email System Working Perfectly</p>
                </div>

                <p style="margin:0 0 16px;color:#475569;font-size:15px;line-height:1.7;">
                  Great news! This email confirms that your email configuration is <strong style="color:#6366f1;">working correctly</strong> and ready to send notifications to your users.
                </p>

                <p style="margin:0 0 28px;color:#475569;font-size:15px;line-height:1.7;">
                  Your email service is properly configured and all systems are operational.
                </p>

                <!-- Info Card -->
                <div style="background:linear-gradient(135deg, #faf5ff 0%, #f3f4f6 100%);border:2px solid #e9d5ff;border-radius:12px;padding:24px;margin:24px 0;">
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td>
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;display:inline-block;margin-right:8px;">
                          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#6366f1"/>
                        </svg>
                        <p style="margin:0;color:#1f2937;font-weight:700;font-size:16px;display:inline;vertical-align:middle;">Email Details</p>
                      </td>
                    </tr>
                  </table>
                  <table style="width:100%;border-collapse:collapse;margin-top:16px;">
                    <tr>
                      <td style="padding:8px 0;color:#64748b;font-size:14px;width:40%;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;display:inline-block;margin-right:6px;">
                          <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z" fill="#64748b"/>
                        </svg>
                        Sent at
                      </td>
                      <td style="padding:8px 0;color:#1e293b;font-size:14px;font-weight:600;">{{ now()->format('F j, Y g:i A') }}</td>
                    </tr>
                    <tr>
                      <td style="padding:8px 0;color:#64748b;font-size:14px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;display:inline-block;margin-right:6px;">
                          <path d="M19 15v4H5v-4h14m1-2H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1v-6c0-.55-.45-1-1-1zM7 18.5c-.82 0-1.5-.67-1.5-1.5s.68-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM19 5v4H5V5h14m1-2H4c-.55 0-1 .45-1 1v6c0 .55.45 1 1 1h16c.55 0 1-.45 1-1V4c0-.55-.45-1-1-1zM7 7.5c-.82 0-1.5-.67-1.5-1.5S6.18 4.5 7 4.5s1.5.68 1.5 1.5S7.83 7.5 7 7.5z" fill="#64748b"/>
                        </svg>
                        Environment
                      </td>
                      <td style="padding:8px 0;color:#1e293b;font-size:14px;font-weight:600;text-transform:capitalize;">{{ config('app.env') }}</td>
                    </tr>
                    <tr>
                      <td style="padding:8px 0;color:#64748b;font-size:14px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;display:inline-block;margin-right:6px;">
                          <path d="M3.9 12c0-1.71 1.39-3.1 3.1-3.1h4V7H7c-2.76 0-5 2.24-5 5s2.24 5 5 5h4v-1.9H7c-1.71 0-3.1-1.39-3.1-3.1zM8 13h8v-2H8v2zm9-6h-4v1.9h4c1.71 0 3.1 1.39 3.1 3.1s-1.39 3.1-3.1 3.1h-4V17h4c2.76 0 5-2.24 5-5s-2.24-5-5-5z" fill="#6366f1"/>
                        </svg>
                        App URL
                      </td>
                      <td style="padding:8px 0;color:#6366f1;font-size:14px;font-weight:600;">{{ config('app.url') }}</td>
                    </tr>
                  </table>
                </div>

                <!-- Call to Action Button -->
                <div style="text-align:center;margin:36px 0 24px;">
                  <a href="{{ url('/') }}"
                     style="display:inline-block;background:linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);color:#ffffff;padding:16px 40px;border-radius:12px;text-decoration:none;font-weight:700;font-size:16px;box-shadow:0 4px 12px rgba(99,102,241,0.3);">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;display:inline-block;margin-right:8px;">
                      <path d="M19 19H5V5h7V3H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-7h-2v7zM14 3v2h3.59l-9.83 9.83 1.41 1.41L19 6.41V10h2V3h-7z" fill="#ffffff"/>
                    </svg>
                    Visit {{ config('app.name') }}
                  </a>
                </div>

                <div style="text-align:center;background:#fefce8;border-left:4px solid #fbbf24;padding:12px 16px;border-radius:8px;margin-top:24px;">
                  <table width="100%" cellpadding="0" cellspacing="0">
                    <tr>
                      <td style="text-align:center;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;display:inline-block;margin-right:4px;">
                          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" fill="#92400e"/>
                        </svg>
                        <span style="margin:0;color:#92400e;font-size:13px;vertical-align:middle;">This is an automated test email. Please do not reply to this message.</span>
                      </td>
                    </tr>
                  </table>
                </div>
              </td>
            </tr>

            <!-- Footer -->
            <tr>
              <td style="background:#f8fafc;padding:32px;text-align:center;border-top:1px solid #e2e8f0;">
                <p style="margin:0 0 12px;color:#64748b;font-size:13px;">
                  © {{ date('Y') }} <strong style="color:#6366f1;">{{ config('app.name') }}</strong>. All rights reserved.
                </p>
                <p style="margin:0;color:#94a3b8;font-size:12px;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" style="vertical-align:middle;display:inline-block;margin-right:4px;">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" fill="#94a3b8"/>
                  </svg>
                  This email was sent from {{ config('app.url') }}
                </p>
              </td>
            </tr>

          </table>
        </td>
      </tr>
    </table>
  </body>
</html>
