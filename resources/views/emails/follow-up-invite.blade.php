<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Follow-up Session Invitation</title>
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
                        <td style="padding:48px 24px 32px;text-align:center;background:linear-gradient(to bottom, rgba(100,103,242,0.05), transparent);">
                            <div style="display:inline-block;width:64px;height:64px;background-color:rgba(100,103,242,0.1);border-radius:50%;padding:0;margin-bottom:24px;line-height:60px;text-align:center;">
                                <img src="{{ asset('images/email-icons/video_call.png') }}" alt="Follow-up" style="width:40px;height:40px;vertical-align:middle;">
                            </div>
                            <h1 style="color:#f8fafc;font-size:36px;font-weight:800;line-height:1.1;margin:0 0 12px;letter-spacing:-0.02em;">Follow-up Session Invitation</h1>
                            <p style="color:#cbd5e1;font-size:18px;font-weight:400;line-height:1.5;margin:0;max-width:500px;margin:0 auto;">
                                Hello {{ $userName }},<br><br>
                                {{ $organizerName }} would like to invite you for a follow-up session.
                            </p>
                        </td>
                    </tr>

                    <!-- Session Details -->
                    <tr>
                        <td style="padding:32px 24px;border-top:1px solid #334155;border-bottom:1px solid #334155;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;border-radius:12px;padding:32px;">
                                <tr>
                                    <td>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <!-- Event Title -->
                                            <tr>
                                                <td colspan="2" style="padding-bottom:4px;">
                                                    <span style="font-size:11px;font-weight:700;color:#6467f2;text-transform:uppercase;letter-spacing:0.15em;">Follow-up Session</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding-bottom:24px;">
                                                    <h3 style="color:#f8fafc;font-size:24px;font-weight:700;margin:0;">{{ $eventTitle }}</h3>
                                                </td>
                                            </tr>

                                            <!-- Previous Session Info -->
                                            <tr>
                                                <td colspan="2" style="padding:16px 0;border-top:1px solid #334155;">
                                                    <p style="color:#94a3b8;font-size:14px;margin:0 0 8px;">
                                                        Based on your previous session on {{ \Carbon\Carbon::parse($originalBookingDate)->format('d M Y') }}
                                                    </p>
                                                </td>
                                            </tr>

                                            <!-- Price Info -->
                                            <tr>
                                                <td colspan="2" style="padding:16px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:middle;">
                                                                <img src="{{ asset('images/email-icons/credit_card.png') }}" alt="Price" style="width:20px;height:20px;">
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                @if($isFree)
                                                                    <p style="color:#10b981;font-size:18px;font-weight:700;margin:0;">This session is FREE!</p>
                                                                @else
                                                                    <p style="color:#f8fafc;font-size:18px;font-weight:600;margin:0;">Special Price: ₹{{ number_format($customPrice, 2) }}</p>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <!-- Book Button -->
                                            <tr>
                                                <td colspan="2" style="padding-top:24px;text-align:center;">
                                                    <a href="{{ $bookingUrl }}" style="display:inline-block;background-color:#6467f2;color:#ffffff;padding:16px 40px;border-radius:8px;text-decoration:none;font-size:18px;font-weight:700;box-shadow:0 4px 12px rgba(100,103,242,0.3);">
                                                        Book Your Follow-up Session
                                                    </a>
                                                </td>
                                            </tr>

                                            @if($expiresAt)
                                            <!-- Expiration Notice -->
                                            <tr>
                                                <td colspan="2" style="padding-top:16px;text-align:center;">
                                                    <p style="color:#94a3b8;font-size:13px;margin:0;">
                                                        This invitation expires on {{ \Carbon\Carbon::parse($expiresAt)->format('d M Y, h:i A') }}
                                                    </p>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Why Follow-up Section -->
                    <tr>
                        <td style="padding:32px 24px;border-bottom:1px solid #334155;">
                            <h3 style="color:#f8fafc;font-size:18px;font-weight:700;margin:0 0 16px;text-align:center;">Why a Follow-up Session?</h3>
                            <p style="color:#cbd5e1;font-size:15px;line-height:1.6;margin:0;text-align:center;max-width:600px;margin:0 auto;">
                                Follow-up sessions help deepen your understanding and address any new questions or insights that may have emerged since your last session. {{ $organizerName }} believes this will be valuable for your continued growth.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:32px 24px;text-align:center;">
                            <p style="color:#64748b;font-size:13px;line-height:1.5;margin:0;">
                                If you have any questions, please feel free to reach out.<br>
                                We look forward to seeing you again!
                            </p>
                            <p style="color:#475569;font-size:12px;margin:16px 0 0;">
                                © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
