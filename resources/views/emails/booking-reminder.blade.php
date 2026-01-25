<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Reminder</title>
</head>
<body style="font-family:'Manrope',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;background-color:#101226;margin:0;padding:40px 16px;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:960px;margin:0 auto;">
        <tr>
            <td>
                <!-- Header Branding -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:32px;padding:0 24px;">
                    <tr>
                        <td style="vertical-align:middle;">
                            <table cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;padding-right:12px;">
                                        <img src="{{ asset('images/AC-Logo.png') }}" alt="Logo" style="width:60px;padding:8px;">
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <span style="font-size:20px;font-weight:800;color:#f8fafc;letter-spacing:-0.02em;">{{ config('app.name') }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Main Card -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#1e293b;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.3);overflow:hidden;border:1px solid #334155;">

                    <!-- Header -->
                    <tr>
                        <td style="padding:48px 24px 32px;text-align:center;background:linear-gradient(to bottom, rgba(245,158,11,0.05), transparent);">
                            <div style="display:inline-block;width:64px;height:64px;background-color:rgba(245,158,11,0.1);border-radius:50%;padding:0;margin-bottom:24px;line-height:60px;text-align:center;">
                                <img src="{{ asset('images/email-icons/notifications_active.png') }}" alt="Reminder" style="width:40px;height:40px;vertical-align:middle;">
                            </div>
                            <h1 style="color:#f8fafc;font-size:36px;font-weight:800;line-height:1.1;margin:0 0 12px;letter-spacing:-0.02em;">Session Reminder</h1>
                            <p style="color:#cbd5e1;font-size:18px;font-weight:400;line-height:1.5;margin:0;max-width:500px;margin:0 auto;">
                                Your session with {{ $organizerName ?? 'Astro Chaitanya' }} is coming up soon!
                            </p>
                        </td>
                    </tr>

                    <!-- Session Details -->
                    <tr>
                        <td style="padding:32px 24px;border-top:1px solid #334155;border-bottom:1px solid #334155;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;border-radius:12px;padding:32px;">
                                <tr>
                                    <td style="vertical-align:top;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td colspan="2" style="padding-bottom:4px;">
                                                    <span style="font-size:11px;font-weight:700;color:#f59e0b;text-transform:uppercase;letter-spacing:0.15em;">Upcoming Session</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding-bottom:16px;">
                                                    <h3 style="color:#f8fafc;font-size:24px;font-weight:700;margin:0;">{{ $eventTitle ?? 'Energy Reading Session' }}</h3>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width:50%;padding:12px 12px 12px 0;vertical-align:top;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:middle;">
                                                                <img src="{{ asset('images/email-icons/calendar_today.png') }}" alt="Date" style="width:20px;height:20px;">
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#f8fafc;font-size:16px;font-weight:600;margin:0;">{{ $bookingDate ?? 'Today' }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="width:50%;padding:12px 0 12px 12px;vertical-align:top;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:middle;">
                                                                <img src="{{ asset('images/email-icons/schedule.png') }}" alt="Time" style="width:20px;height:20px;">
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#f8fafc;font-size:16px;font-weight:600;margin:0;">{{ $bookingTime ?? '2:00 PM' }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding:12px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:middle;">
                                                                <img src="{{ asset('images/email-icons/alarm.png') }}" alt="Time Until" style="width:20px;height:20px;">
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#f59e0b;font-size:18px;font-weight:700;margin:0;">Starts in {{ $timeUntil ?? '30 minutes' }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding-top:16px;">
                                                    <a href="{{ $meetingLink ?? '#' }}" style="display:inline-block;background-color:#f59e0b;color:#ffffff;padding:14px 32px;border-radius:8px;text-decoration:none;font-size:18px;font-weight:700;box-shadow:0 4px 12px rgba(245,158,11,0.3);">
                                                        <img src="{{ asset('images/email-icons/video_call.png') }}" alt="Join" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;">
                                                        Join Session Now
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Reminder Note -->
                    <tr>
                        <td style="padding:48px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="text-align:center;">
                                        <img src="{{ asset('images/email-icons/info.png') }}" alt="Info" style="width:32px;height:32px;margin-bottom:16px;">
                                        <p style="color:#cbd5e1;font-size:16px;line-height:1.6;margin:0;">
                                            Please ensure you're in a quiet environment with a stable internet connection before joining.
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#0f172a;padding:32px 24px;border-top:1px solid #334155;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="text-align:center;">
                                        <p style="color:#cbd5e1;font-size:14px;margin:0 0 8px;">
                                            Need to reschedule? <a href="{{ $rescheduleUrl ?? '#' }}" style="color:#818cf8;text-decoration:none;font-weight:600;">Click here</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

                <!-- Legal Footer -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:40px;padding:0 24px 40px;">
                    <tr>
                        <td style="text-align:center;">
                            <p style="color:#94a3b8;font-size:12px;margin:0 0 8px;">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>
