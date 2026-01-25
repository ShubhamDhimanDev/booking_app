<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
                                <img src="{{ asset('images/email-icons/lock_reset.png') }}" alt="Reset Password" style="width:40px;height:40px;vertical-align:middle;">
                            </div>
                            <h1 style="color:#f8fafc;font-size:36px;font-weight:800;line-height:1.1;margin:0 0 12px;letter-spacing:-0.02em;">Reset Your Password</h1>
                            <p style="color:#cbd5e1;font-size:18px;font-weight:400;line-height:1.5;margin:0;max-width:500px;margin:0 auto;">
                                We received a request to reset your password. Click the button below to create a new password.
                            </p>
                        </td>
                    </tr>

                    <!-- Reset Button -->
                    <tr>
                        <td style="padding:32px 24px;border-top:1px solid #334155;border-bottom:1px solid #334155;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="text-align:center;">
                                        <a href="{{ $resetUrl ?? '#' }}" style="display:inline-block;background-color:#6467f2;color:#ffffff;padding:16px 48px;border-radius:8px;text-decoration:none;font-size:18px;font-weight:700;box-shadow:0 4px 12px rgba(100,103,242,0.3);">
                                            <img src="{{ asset('images/email-icons/vpn_key.png') }}" alt="Key" style="width:22px;height:22px;vertical-align:middle;margin-right:8px;">
                                            Reset Password
                                        </a>
                                        <p style="color:#94a3b8;font-size:12px;margin:16px 0 0;">
                                            This link will expire in {{ $expiresIn ?? '60 minutes' }}
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
                                                {{ $resetUrl ?? 'https://example.com/reset-password?token=xxxxx' }}
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Security Notice -->
                    <tr>
                        <td style="padding:0 24px 48px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;border-radius:8px;padding:24px;border:1px solid #334155;">
                                <tr>
                                    <td>
                                        <table cellpadding="0" cellspacing="0" style="margin-bottom:12px;">
                                            <tr>
                                                <td style="padding-right:12px;vertical-align:top;">
                                                    <img src="{{ asset('images/email-icons/shield.png') }}" alt="Security" style="width:24px;height:24px;">
                                                </td>
                                                <td>
                                                    <h4 style="color:#f8fafc;font-size:16px;font-weight:700;margin:0;">Security Notice</h4>
                                                </td>
                                            </tr>
                                        </table>
                                        <p style="color:#cbd5e1;font-size:14px;line-height:1.6;margin:0;">
                                            If you didn't request a password reset, please ignore this email or contact our support team if you have concerns about your account security.
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
                                        <p style="color:#cbd5e1;font-size:14px;margin:0;">
                                            Need help? Contact us at <a href="mailto:astrology.chaitanya@gmail.com" style="color:#818cf8;text-decoration:none;">astrology.chaitanya@gmail.com</a>
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
