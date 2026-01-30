<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Account Credentials</title>
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
                                <img src="{{ asset('images/email-icons/account_circle.png') }}" alt="Account" style="width:40px;height:40px;vertical-align:middle;">
                            </div>
                            <h1 style="color:#f8fafc;font-size:36px;font-weight:800;line-height:1.1;margin:0 0 12px;letter-spacing:-0.02em;">Welcome to {{ config('app.name') }}!</h1>
                            <p style="color:#cbd5e1;font-size:18px;font-weight:400;line-height:1.5;margin:0;max-width:500px;margin:0 auto;">
                                Your account has been created successfully. Here are your login credentials.
                            </p>
                        </td>
                    </tr>

                    <!-- Credentials -->
                    <tr>
                        <td style="padding:32px 24px;border-top:1px solid #334155;border-bottom:1px solid #334155;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;border-radius:12px;padding:32px;">
                                <tr>
                                    <td>
                                        <h3 style="color:#f8fafc;font-size:18px;font-weight:700;margin:0 0 24px;text-align:center;">Your Login Credentials</h3>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:16px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:middle;">
                                                                <img src="{{ asset('images/email-icons/mail.png') }}" alt="Email" style="width:24px;height:24px;">
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#94a3b8;font-size:14px;margin:0 0 4px;">Email Address</p>
                                                                <div style="background-color:#1e293b;border-radius:6px;padding:12px;border:1px solid #334155;">
                                                                    <p style="color:#f8fafc;font-size:16px;font-weight:600;margin:0;font-family:monospace;">{{ $email ?? 'user@example.com' }}</p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding:16px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:middle;">
                                                                <img src="{{ asset('images/email-icons/vpn_key.png') }}" alt="Password" style="width:24px;height:24px;">
                                                            </td>
                                                            <td style="vertical-align:middle;">
                                                                <p style="color:#94a3b8;font-size:14px;margin:0 0 4px;">Temporary Password</p>
                                                                <div style="background-color:#1e293b;border-radius:6px;padding:12px;border:1px solid #334155;">
                                                                    <p style="color:#f8fafc;font-size:16px;font-weight:600;margin:0;font-family:monospace;">{{ $password ?? 'TempPass123!' }}</p>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                        <div style="background-color:#fef3c7;border-left:4px solid #f59e0b;border-radius:6px;padding:16px;margin-top:24px;">
                                            <table cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <td style="padding-right:12px;vertical-align:top;">
                                                        <img src="{{ asset('images/email-icons/warning.png') }}" alt="Warning" style="width:20px;height:20px;">
                                                    </td>
                                                    <td>
                                                        <p style="color:#92400e;font-size:13px;margin:0;font-weight:600;">
                                                            Important: Please change your password after your first login for security reasons.
                                                        </p>
                                                    </td>
                                                </tr>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Login Button -->
                    <tr>
                        <td style="padding:32px 24px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="text-align:center;">
                                        <a href="{{ $loginUrl ?? url('/login') }}" style="display:inline-block;background-color:#6467f2;color:#ffffff;padding:14px 40px;border-radius:8px;text-decoration:none;font-size:16px;font-weight:700;box-shadow:0 4px 12px rgba(100,103,242,0.3);">
                                            <img src="{{ asset('images/email-icons/login.png') }}" alt="Login" style="width:20px;height:20px;vertical-align:middle;margin-right:8px;">
                                            Login to Your Account
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Getting Started -->
                    <tr>
                        <td style="padding:0 24px 48px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0f172a;border-radius:8px;padding:24px;border:1px solid #334155;">
                                <tr>
                                    <td>
                                        <h4 style="color:#f8fafc;font-size:18px;font-weight:700;margin:0 0 16px;text-align:center;">Getting Started</h4>
                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="padding:8px 0;">
                                                    <table cellpadding="0" cellspacing="0">
                                                        <tr>
                                                            <td style="padding-right:12px;vertical-align:top;padding-top:2px;">
                                                                <div style="width:24px;height:24px;background-color:#6467f2;border-radius:50%;text-align:center;line-height:24px;">
                                                                    <span style="color:#ffffff;font-size:12px;font-weight:700;">1</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:14px;margin:0;font-weight:600;">Login with your credentials</p>
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
                                                                <div style="width:24px;height:24px;background-color:#6467f2;border-radius:50%;text-align:center;line-height:24px;">
                                                                    <span style="color:#ffffff;font-size:12px;font-weight:700;">2</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:14px;margin:0;font-weight:600;">Change your temporary password</p>
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
                                                                <div style="width:24px;height:24px;background-color:#6467f2;border-radius:50%;text-align:center;line-height:24px;">
                                                                    <span style="color:#ffffff;font-size:12px;font-weight:700;">3</span>
                                                                </div>
                                                            </td>
                                                            <td>
                                                                <p style="color:#cbd5e1;font-size:14px;margin:0;font-weight:600;">Complete your profile and start exploring</p>
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
                    {{-- <tr>
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
                    </tr> --}}

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
