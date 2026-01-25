<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email Address</title>
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
                        <td style="padding:48px 24px 32px;text-align:center;background:linear-gradient(to bottom, rgba(16,185,129,0.05), transparent);">
                            <div style="display:inline-block;width:64px;height:64px;background-color:rgba(16,185,129,0.1);border-radius:50%;padding:0;margin-bottom:24px;line-height:60px;text-align:center;">
                                <img src="{{ asset('images/email-icons/mark_email_read.png') }}" alt="Verify Email" style="width:40px;height:40px;vertical-align:middle;">
                            </div>
                            <h1 style="color:#f8fafc;font-size:36px;font-weight:800;line-height:1.1;margin:0 0 12px;letter-spacing:-0.02em;">Verify Your Email</h1>
                            <p style="color:#cbd5e1;font-size:18px;font-weight:400;line-height:1.5;margin:0;max-width:500px;margin:0 auto;">
                                Welcome to {{ config('app.name') }}! Please verify your email address to get started.
                            </p>
                        </td>
                    </tr>

                    <!-- Verify Button -->
                    <tr>
                        <td style="padding:32px 24px;border-top:1px solid #334155;border-bottom:1px solid #334155;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="text-align:center;">
                                        <a href="{{ $verifyUrl ?? '#' }}" style="display:inline-block;background-color:#10b981;color:#ffffff;padding:16px 48px;border-radius:8px;text-decoration:none;font-size:18px;font-weight:700;box-shadow:0 4px 12px rgba(16,185,129,0.3);">
                                            <img src="{{ asset('images/email-icons/verified.png') }}" alt="Verify" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;">
                                            Verify Email Address
                                        </a>
                                        <p style="color:#94a3b8;font-size:12px;margin:16px 0 0;">
                                            This link will expire in {{ $expiresIn ?? '24 hours' }}
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Alternative Link -->
                    <tr>
                        <td style="padding:32px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td>
                                        <p style="color:#cbd5e1;font-size:14px;margin:0 0 12px;text-align:center;">If the button doesn't work, copy and paste this link into your browser:</p>
                                        <div style="background-color:#0f172a;border-radius:8px;padding:16px;border:1px solid #334155;">
                                            <p style="color:#818cf8;font-size:13px;margin:0;word-break:break-all;text-align:center;">
                                                {{ $verifyUrl ?? 'https://example.com/verify-email?token=xxxxx' }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- What's Next -->
                    <tr>
                        <td style="padding:0 24px 48px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;border-radius:8px;padding:24px;border:1px solid #334155;">
                                <tr>
                                    <td>
                                        <h4 style="color:#f8fafc;font-size:18px;font-weight:700;margin:0 0 16px;text-align:center;">What's Next?</h4>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:8px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:top;padding-top:2px;">
                                                                <img src="{{ asset('images/email-icons/check_circle.png') }}" alt="Check" style="width:16px;height:16px;">
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:14px;margin:0;">Complete your profile setup</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:top;padding-top:2px;">
                                                                <img src="{{ asset('images/email-icons/check_circle.png') }}" alt="Check" style="width:16px;height:16px;">
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:14px;margin:0;">Create your first event or book a session</p>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:top;padding-top:2px;">
                                                                <img src="{{ asset('images/email-icons/check_circle.png') }}" alt="Check" style="width:16px;height:16px;">
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:14px;margin:0;">Explore all features and integrations</p>
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

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#0f172a;padding:32px 24px;border-top:1px solid #334155;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="text-align:center;">
                                        <p style="color:#cbd5e1;font-size:14px;margin:0;">
                                            Didn't sign up? You can safely ignore this email.
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
