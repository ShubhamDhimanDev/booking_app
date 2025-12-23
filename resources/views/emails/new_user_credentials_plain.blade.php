Hello {{ $user->name }},

Your account has been created.

Email: {{ $user->email }}
Password: {{ $password }}

You can log in at: {{ url('/login') }}

Please change your password after logging in.

Regards,
The Team
