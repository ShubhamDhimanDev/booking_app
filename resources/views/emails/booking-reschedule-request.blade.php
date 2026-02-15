<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Re-schedule Meeting</title>
	<style>
		body { font-family: 'Manrope', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color:#0b1220; color:#cbd5e1; }
		.container { max-width:960px; margin:0 auto; padding:40px 16px; }
		.card { background-color:#0f172a; border-radius:12px; overflow:hidden; border:1px solid #334155; box-shadow:0 8px 30px rgba(0,0,0,0.3); }
		.card-header { padding:48px 24px 32px; text-align:center; background:linear-gradient(to bottom, rgba(100,103,242,0.04), transparent); }
		.brand { display:inline-block; width:64px; height:64px; background-color:rgba(100,103,242,0.06); border-radius:50%; margin-bottom:24px; line-height:64px; }
		.brand img { width:40px; height:40px; vertical-align:middle; }
		.title { color:#f8fafc; font-size:28px; font-weight:800; margin:0 0 8px; }
		.lead { color:#94a3b8; font-size:15px; margin:0 auto; max-width:600px; }
		.card-body { padding:32px 24px; border-top:1px solid #334155; border-bottom:1px solid #334155; }
		.btn { display:inline-block; background-color:#6467f2; color:#fff; padding:14px 36px; border-radius:8px; text-decoration:none; font-weight:700; box-shadow:0 6px 18px rgba(100,103,242,0.24); }
		.footer { padding:32px 24px; text-align:center; color:#64748b; font-size:13px; }
	</style>
</head>
<body>
	<div class="container">
		<div class="card">
			<div class="card-header">
				<div class="brand">
					<img src="{{ asset('images/AC-Logo.png') }}" alt="Logo">
				</div>
				<h1 class="title">Re-schedule Meeting Request</h1>
				<p class="lead">Hi {{ $bookerName }}, the meeting for <strong style="color:#f8fafc">{{ $eventTitle }}</strong> needs to be re-scheduled.</p>
			</div>

			<div class="card-body" style="background-color:#0f172a;">
				<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#0b1220;border-radius:8px;padding:28px;">
					<tr>
						<td style="color:#cbd5e1;font-size:15px;line-height:1.6;">
							<p style="margin:0 0 12px;">We've cancelled the existing Google Calendar event and removed the Meet/Calendar links so you can pick a new suitable date and time.</p>
							@if(!empty($note))
								<p style="margin:0 0 12px;color:#e6edf3;">{{ $note }}</p>
							@endif
							<p style="margin:0 0 20px;">Click the button below to choose a new slot. If prompted, sign in with the email you used to book.</p>
						</td>
					</tr>
					<tr>
						<td style="padding-top:18px;text-align:center;">
							<a href="{{ $rescheduleLink }}" class="btn">Re-schedule Meeting</a>
						</td>
					</tr>
				</table>
			</div>

			<div class="footer">
				<p style="margin:0 0 8px;">If you have questions, please reply to this email.</p>
				<p style="margin:0;color:#475569;">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
			</div>
		</div>
	</div>
</body>
</html>
