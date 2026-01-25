<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Declined</title>
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
                        <td style="padding:48px 24px 32px;text-align:center;background:linear-gradient(to bottom, rgba(239,68,68,0.05), transparent);">
                            <div style="display:inline-block;width:64px;height:64px;background-color:rgba(239,68,68,0.1);border-radius:50%;padding:0;margin-bottom:24px;line-height:60px;text-align:center;">
                                <img src="{{ asset('images/email-icons/event_busy.png') }}" alt="Declined" style="width:40px;height:40px;vertical-align:middle;">
                            </div>
                            <h1 style="color:#f8fafc;font-size:36px;font-weight:800;line-height:1.1;margin:0 0 12px;letter-spacing:-0.02em;">Booking Declined</h1>
                            <p style="color:#cbd5e1;font-size:18px;font-weight:400;line-height:1.5;margin:0;max-width:500px;margin:0 auto;">
                                We regret to inform you that your booking request has been declined by {{ $organizerName ?? 'the organizer' }}.
                            </p>
                        </td>
                    </tr>

                    <!-- Booking Details -->
                    <tr>
                        <td style="padding:32px 24px;border-top:1px solid #334155;border-bottom:1px solid #334155;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;border-radius:12px;padding:32px;">
                                <tr>
                                    <td style="vertical-align:top;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td colspan="2" style="padding-bottom:4px;">
                                                    <span style="font-size:11px;font-weight:700;color:#ef4444;text-transform:uppercase;letter-spacing:0.15em;">Declined Booking</span>
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
                                                                <p style="color:#94a3b8;font-size:16px;font-weight:600;margin:0;text-decoration:line-through;">{{ $bookingDate ?? 'January 30, 2026' }}</p>
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
                                                                <p style="color:#94a3b8;font-size:16px;font-weight:600;margin:0;text-decoration:line-through;">{{ $bookingTime ?? '2:00 PM - 3:00 PM' }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            @if(isset($declineReason) && $declineReason)
                                            <tr>
                                                <td colspan="2" style="padding-top:20px;padding-bottom:20px;border-top:1px solid #334155;">
                                                    <p style="color:#cbd5e1;font-size:14px;margin:0 0 8px;font-weight:600;">Reason:</p>
                                                    <p style="color:#94a3b8;font-size:14px;margin:0;font-style:italic;">{{ $declineReason }}</p>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Next Steps -->
                    <tr>
                        <td style="padding:48px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="text-align:center;">
                                        <h4 style="color:#f8fafc;font-size:20px;font-weight:700;margin:0 0 16px;">What's Next?</h4>
                                        <p style="color:#cbd5e1;font-size:16px;line-height:1.6;margin:0 0 24px;max-width:500px;margin:0 auto 24px;">
                                            Don't worry! You can book another time slot or explore other available sessions.
                                        </p>
                                        <a href="{{ $browseEventsUrl ?? url('/events') }}" style="display:inline-block;background-color:#6467f2;color:#ffffff;padding:12px 32px;border-radius:8px;text-decoration:none;font-size:16px;font-weight:700;box-shadow:0 4px 12px rgba(100,103,242,0.2);">
                                            <img src="{{ asset('images/email-icons/explore.png') }}" alt="Browse" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;">
                                            Browse Available Sessions
                                        </a>
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
                                        <p style="color:#cbd5e1;font-size:14px;margin:0;">
                                            If you have any questions, please contact us at <a href="mailto:astrology.chaitanya@gmail.com" style="color:#818cf8;text-decoration:none;">astrology.chaitanya@gmail.com</a>
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
