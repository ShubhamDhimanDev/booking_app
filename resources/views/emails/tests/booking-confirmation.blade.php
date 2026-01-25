<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation</title>
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

                <!-- Main Confirmation Card -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#1e293b;border-radius:12px;box-shadow:0 8px 30px rgba(0,0,0,0.3);overflow:hidden;border:1px solid #334155;">

                    <!-- Success Banner Header -->
                    <tr>
                        <td style="padding:48px 24px 32px;text-align:center;background:linear-gradient(to bottom, rgba(100,103,242,0.05), transparent);">
                            <div style="display:inline-block;width:64px;height:64px;background-color:rgba(16,185,129,0.1);border-radius:50%;padding:0;margin-bottom:24px;line-height:60px;text-align:center;">
                                <img src="{{ asset('images/email-icons/check_circle.png') }}" alt="Success" style="width:40px;height:40px;vertical-align:middle;">
                            </div>
                            <h1 style="color:#f8fafc;font-size:36px;font-weight:800;line-height:1.1;margin:0 0 12px;letter-spacing:-0.02em;">Namaste from {{ config('app.name') }}!</h1>
                            <p style="color:#cbd5e1;font-size:18px;font-weight:400;line-height:1.5;margin:0;max-width:500px;margin:0 auto;">
                                Your session with {{ config('app.name') }} has been successfully booked.<br><br>
                                We are glad you have taken this step towards gaining clarity and understanding the deeper patterns influencing your life.
                            </p>
                        </td>
                    </tr>

                    <!-- Session Details Component with Image -->
                    <tr>
                        <td style="padding:32px 24px;border-top:1px solid #334155;border-bottom:1px solid #334155;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;border-radius:12px;padding:32px;">
                                <tr>
                                    <!-- Details Section -->
                                    <td style="vertical-align:top;">
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <!-- Confirmed Label -->
                                            <tr>
                                                <td colspan="2" style="padding-bottom:4px;">
                                                    <span style="font-size:11px;font-weight:700;color:#6467f2;text-transform:uppercase;letter-spacing:0.15em;">Confirmed Appointment</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding-bottom:16px;">
                                                    <h3 style="color:#f8fafc;font-size:24px;font-weight:700;margin:0;">{{ $eventTitle ?? 'Energy Reading Session' }}</h3>
                                                </td>
                                            </tr>
                                            <!-- Date -->
                                            <tr>
                                                <td style="width:50%;padding:12px 12px 12px 0;vertical-align:top;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:middle;">
                                                                <img src="{{ asset('images/email-icons/calendar_today.png') }}" alt="Date" style="width:20px;height:20px;">
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#f8fafc;font-size:16px;font-weight:600;margin:0;">{{ $bookingDate ?? 'Monday, Oct 24, 2024' }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <!-- Time -->
                                                <td style="width:50%;padding:12px 0 12px 12px;vertical-align:top;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:middle;">
                                                                <img src="{{ asset('images/email-icons/schedule.png') }}" alt="Time" style="width:20px;height:20px;">
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#f8fafc;font-size:16px;font-weight:600;margin:0;">{{ $bookingTime ?? '10:00 AM - 11:00 AM' }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <!-- Timezone -->
                                            <tr>
                                                <td colspan="2" style="padding:12px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:middle;">
                                                                <img src="{{ asset('images/email-icons/public.png') }}" alt="Timezone" style="width:20px;height:20px;">
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#94a3b8;font-size:14px;font-weight:400;margin:0;">Timezone: {{ $timezone ?? '(GMT+5:30) India Standard Time' }}</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <!-- Join Button -->
                                            <tr>
                                                <td colspan="2" style="padding-top:16px;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td>
                                                                <a href="{{ $meetingLink ?? '#' }}" style="display:inline-block;background-color:#6467f2;color:#ffffff;padding:12px 24px;border-radius:8px;text-decoration:none;font-size:16px;font-weight:700;box-shadow:0 4px 12px rgba(100,103,242,0.2);">
                                                                    <img src="{{ asset('images/email-icons/video_call.png') }}" alt="Join" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;">
                                                                    Join Session
                                                                </a>
                                                                <span style="color:#ffffff;">Or use meeting link: <a href="https://meet.astrochaitanya.com/j-2410" style="color:#6467f2; text-decoration:none;">meet.astrochaitanya.com/j-2410</a></span>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    <p style="color:#94a3b8;font-size:12px;margin:8px 0 0;font-style:italic;">
                                                        (Please ensure you join from a quiet place with a stable internet connection.)
                                                    </p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Informational Grid (2 Columns) -->
                    <tr>
                        <td style="padding:48px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <!-- What to Expect -->
                                    <td style="width:50%;vertical-align:top;padding-right:16px;">
                                        <table cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                                            <tr>
                                                <td style="padding-right:12px;vertical-align:middle;">
                                                    <div style="width:40px;height:40px;background-color:rgba(100,103,242,0.1);border-radius:8px;display:inline-block;text-align:center;line-height:40px;">
                                                        <img src="{{ asset('images/email-icons/bolt.png') }}" alt="Expect" style="width:20px;height:20px;vertical-align:middle;">
                                                    </div>
                                                </td>
                                                <td style="vertical-align:middle;">
                                                    <h4 style="color:#f8fafc;font-size:18px;font-weight:700;margin:0;">What to expect during the session</h4>
                                                </td>
                                            </tr>
                                        </table>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:10px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:top;padding-right:12px;padding-top: 2px;">
                                                                <img src="{{ asset('images/email-icons/wand_stars.png') }}" alt="Wand Stars" style="width:14px;height:14px;margin-top:4px;">
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:16px;line-height:1.6;margin:0;">Chaitanya will read your energy in real time, no date of birth, charts, or prior details required.</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:top;padding-right:12px;padding-top: 2px;">
                                                                <img src="{{ asset('images/email-icons/wand_stars.png') }}" alt="Wand Stars" style="width:14px;height:14px;margin-top:4px;">
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:16px;line-height:1.6;margin:0;">The focus will be on identifying the root cause of the challenges you are currently facing.</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:top;padding-right:12px;padding-top: 2px;">
                                                                <img src="{{ asset('images/email-icons/wand_stars.png') }}" alt="Wand Stars" style="width:14px;height:14px;margin-top:4px;">
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:16px;line-height:1.6;margin:0;">You will receive clear insights and guidance based on your energy right now.</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>

                                    <!-- Before the Session -->
                                    <td style="width:50%;vertical-align:top;padding-left:16px;">
                                        <table cellpadding="0" cellspacing="0" style="margin-bottom:20px;">
                                            <tr>
                                                <td style="padding-right:12px;vertical-align:middle;">
                                                    <div style="width:40px;height:40px;background-color:rgba(100,103,242,0.1);border-radius:8px;display:inline-block;text-align:center;line-height:40px;">
                                                        <img src="{{ asset('images/email-icons/potted_plant.png') }}" alt="Potted Plant" style="width:20px;height:20px;vertical-align:middle;">
                                                    </div>
                                                </td>
                                                <td style="vertical-align:middle;">
                                                    <h4 style="color:#f8fafc;font-size:18px;font-weight:700;margin:0;">Before the session</h4>
                                                </td>
                                            </tr>
                                        </table>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:10px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:top;padding-right:12px;padding-top: 2px;">
                                                                <img src="{{ asset('images/email-icons/check.png') }}" alt="Check" style="width:14px;height:14px;margin-top:4px;">
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:16px;line-height:1.6;margin:0;">No preparation is required.</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:top;padding-right:12px;padding-top: 2px;">
                                                                <img src="{{ asset('images/email-icons/check.png') }}" alt="Check" style="width:14px;height:14px;margin-top:4px;">
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:16px;line-height:1.6;margin:0;">Please join the call with an open mind and avoid distractions.</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:10px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="vertical-align:top;padding-right:12px;padding-top: 2px;">
                                                                <img src="{{ asset('images/email-icons/check.png') }}" alt="Check" style="width:14px;height:14px;margin-top:4px;">
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:16px;line-height:1.6;margin:0;">If possible, log in 2–3 minutes early.</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer Section within card -->
                    <tr>
        <td style="background-color:#0f172a;padding:32px 24px;border-top:1px solid #334155;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <!-- Questions Section -->
                                    <td style="width:50%;vertical-align:top;text-align:left;padding-right:24px;">
                                        <p style="color:#f8fafc;font-size:16px;font-weight:700;margin:0 0 8px;">Questions?</p>
                                        <p style="color:#cbd5e1;font-size:14px;margin:0 0 4px;">
                                            Email: <a href="mailto:astrology.chaitanya@gmail.com" style="color:#818cf8;text-decoration:none;">astrology.chaitanya@gmail.com</a>
                                        </p>
                                        <p style="color:#cbd5e1;font-size:14px;margin:0;">
                                            Call: <a href="tel:+916366282505" style="color:#818cf8;text-decoration:none;">+91 63662 82505</a>
                                        </p>
                                    </td>
                                    <!-- Closing Message -->
                                    <td style="width:50%;vertical-align:top;text-align:right;padding-left:24px;">
                                        <p style="color:#94a3b8;font-size:14px;margin:0 0 8px;font-style:italic;">We look forward to connecting with you and supporting you on this journey.</p>
                                        <p style="color:#f8fafc;font-size:16px;font-weight:700;margin:0;">Warm regards,</p>
                                        <p style="color:#818cf8;font-size:16px;font-weight:700;margin:0;">Team {{ config('app.name') }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>

                <!-- Final Legal Footer -->
                <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:40px;padding:0 24px 40px;">
                    <tr>
                        <td style="text-align:center;">
                            <p style="color:#94a3b8;font-size:12px;margin:0 0 8px;">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                            <table cellpadding="0" cellspacing="0" style="display:inline-block;">
                                <tr>
                                    <td style="padding:0 12px;">
                                        <a href="{{ url('/privacy') }}" style="color:#94a3b8;font-size:12px;text-decoration:none;">Privacy Policy</a>
                                    </td>
                                    <td style="padding:0 12px;">
                                        <a href="{{ url('/terms') }}" style="color:#94a3b8;font-size:12px;text-decoration:none;">Terms of Service</a>
                                    </td>
                                    <td style="padding:0 12px;">
                                        <a href="{{ url('/unsubscribe') }}" style="color:#94a3b8;font-size:12px;text-decoration:none;">Unsubscribe</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>
