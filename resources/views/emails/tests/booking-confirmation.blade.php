<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
</head>
<body style="font-family:'Manrope',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;background-color:#f6f6f8;margin:0;padding:16px 0;min-height:100vh;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;">
        <tr>
            <td>
                <!-- Main Container -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#ffffff;border-radius:12px;box-shadow:0 10px 15px rgba(0,0,0,0.1);overflow:hidden;">

                    <!-- Header Branding -->
                    <tr>
                        <td style="padding:32px 32px 16px;border-bottom:1px solid #f3f4f6;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <table cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="vertical-align:middle;padding-right:8px;">
                                                    <svg width="32" height="32" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                        <path fill-rule="evenodd" clip-rule="evenodd" d="M24 4H42V17.3333V30.6667H24V44H6V30.6667V17.3333H24V4Z" fill="#6467f2"/>
                                                    </svg>
                                                </td>
                                                <td style="vertical-align:middle;">
                                                    <span style="font-size:20px;font-weight:700;color:#111118;letter-spacing:-0.02em;">{{ config('app.name') }}</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="text-align:right;vertical-align:middle;">
                                        <span style="color:#9ca3af;font-size:11px;font-weight:500;text-transform:uppercase;letter-spacing:0.1em;">CONFIRMATION</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Success Icon -->
                    <tr>
                        <td style="padding:40px 32px 16px;text-align:center;">
                            <div style="display:inline-block;background-color:rgba(34,197,94,0.1);border-radius:50%;padding:16px;">
                                <span style="font-size:48px;">{{ asset('images/email-icons/check_circle.png') }}</span>
                            </div>
                        </td>
                    </tr>

                    <!-- Main Heading -->
                    <tr>
                        <td style="padding:0 32px;text-align:center;">
                            <h1 style="color:#6467f2;font-size:28px;font-weight:800;line-height:1.2;margin:0 0 12px;letter-spacing:-0.02em;">Booking Confirmed</h1>
                            <p style="color:#616289;font-size:16px;font-weight:500;line-height:1.5;margin:0;max-width:400px;margin:0 auto;">
                                Hi {{ $bookerName ?? 'Alex' }}, your booking is all set! We're looking forward to seeing you.
                            </p>
                        </td>
                    </tr>

                    <!-- Booking Details Section -->
                    <tr>
                        <td style="padding:32px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f6f8;border-radius:12px;padding:24px;">
                                <tr>
                                    <td>
                                        <h3 style="color:#111118;font-size:14px;font-weight:700;line-height:1.2;margin:0 0 16px;padding-bottom:16px;border-bottom:1px solid #e5e7eb;letter-spacing:-0.01em;">Booking Summary</h3>

                                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:0;">
                                            <!-- Service -->
                                            <tr>
                                                <td style="padding:8px 0;vertical-align:top;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:middle;padding-right:8px;">
                                                                <span style="font-size:16px;">📋</span>
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#616289;font-size:13px;font-weight:500;margin:0;">Service</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="padding:8px 0;text-align:right;vertical-align:top;">
                                                    <p style="color:#111118;font-size:13px;font-weight:700;margin:0;">{{ $eventTitle ?? 'Professional Consultation' }}</p>
                                                </td>
                                            </tr>

                                            <!-- Date -->
                                            <tr>
                                                <td style="padding:8px 0;vertical-align:top;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:middle;padding-right:8px;">
                                                                <span style="font-size:16px;">📅</span>
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#616289;font-size:13px;font-weight:500;margin:0;">Date</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="padding:8px 0;text-align:right;vertical-align:top;">
                                                    <p style="color:#111118;font-size:13px;font-weight:700;margin:0;">{{ $bookingDate ?? 'October 24, 2023' }}</p>
                                                </td>
                                            </tr>

                                            <!-- Time -->
                                            <tr>
                                                <td style="padding:8px 0;vertical-align:top;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:middle;padding-right:8px;">
                                                                <span style="font-size:16px;">🕐</span>
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#616289;font-size:13px;font-weight:500;margin:0;">Time</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="padding:8px 0;text-align:right;vertical-align:top;">
                                                    <p style="color:#111118;font-size:13px;font-weight:700;margin:0;">{{ $bookingTime ?? '10:00 AM - 11:00 AM EST' }}</p>
                                                </td>
                                            </tr>

                                            <!-- Location -->
                                            <tr>
                                                <td style="padding:8px 0;vertical-align:top;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:middle;padding-right:8px;">
                                                                <span style="font-size:16px;">🔗</span>
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#616289;font-size:13px;font-weight:500;margin:0;">Location</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td style="padding:8px 0;text-align:right;vertical-align:top;">
                                                    <a href="{{ $meetingLink ?? '#' }}" style="color:#6467f2;font-size:13px;font-weight:700;text-decoration:none;">
                                                        {{ $meetingLocation ?? 'Google Meet' }} →
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Main CTA -->
                    <tr>
                        <td style="padding:0 32px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="text-align:center;">
                                        <a href="{{ $bookingDetailsUrl ?? url('/user/bookings') }}" style="display:inline-block;background-color:#6467f2;color:#ffffff;font-weight:700;padding:16px 24px;border-radius:8px;text-decoration:none;font-size:14px;box-shadow:0 4px 6px rgba(100,103,242,0.2);">
                                            View Booking Details →
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f6f6f8;padding:32px;text-align:center;">
                            <!-- Social Links -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
                                <tr>
                                    <td style="text-align:center;">
                                        <table cellpadding="0" cellspacing="0" style="display:inline-block;">
                                            <tr>
                                                <td style="padding:0 12px;">
                                                    <a href="#" style="color:#9ca3af;text-decoration:none;">
                                                        <span style="font-size:20px;">📘</span>
                                                    </a>
                                                </td>
                                                <td style="padding:0 12px;">
                                                    <a href="#" style="color:#9ca3af;text-decoration:none;">
                                                        <span style="font-size:20px;">🐦</span>
                                                    </a>
                                                </td>
                                                <td style="padding:0 12px;">
                                                    <a href="#" style="color:#9ca3af;text-decoration:none;">
                                                        <span style="font-size:20px;">💼</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <p style="color:#616289;font-size:13px;margin:0 0 16px;">
                                Need help with your booking? <a href="{{ url('/contact') }}" style="color:#6467f2;font-weight:700;text-decoration:none;">Contact Support</a>
                            </p>

                            <div style="padding-top:24px;border-top:1px solid #e5e7eb;">
                                <p style="color:#616289;font-size:11px;margin:0 0 8px;">
                                    © {{ date('Y') }} {{ config('app.name') }} Inc. All rights reserved.
                                </p>
                                <table cellpadding="0" cellspacing="0" style="display:inline-block;">
                                    <tr>
                                        <td style="padding:0 8px;">
                                            <a href="{{ url('/privacy') }}" style="color:#616289;font-size:11px;text-decoration:underline;">Privacy Policy</a>
                                        </td>
                                        <td style="padding:0 8px;">
                                            <a href="{{ url('/terms') }}" style="color:#616289;font-size:11px;text-decoration:underline;">Terms of Service</a>
                                        </td>
                                        <td style="padding:0 8px;">
                                            <a href="{{ url('/unsubscribe') }}" style="color:#616289;font-size:11px;text-decoration:underline;">Unsubscribe</a>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
