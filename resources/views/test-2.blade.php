<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email Sender - {{ config('app.name') }}</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 min-h-screen" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="max-w-2xl mx-auto px-4 py-12">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-primary to-purple-600 rounded-2xl mb-4 shadow-lg">
                <span class="material-icons text-white text-3xl">mail</span>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Email Test Tool</h1>
            <p class="text-gray-600 text-lg">Send a test email to verify your email configuration</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border-2 border-green-300 text-green-800 px-5 py-4 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <span class="material-icons text-green-600 mr-3">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-gradient-to-r from-red-50 to-pink-50 border-2 border-red-300 text-red-800 px-5 py-4 rounded-xl shadow-sm">
                <div class="flex items-center">
                    <span class="material-icons text-red-600 mr-3">error</span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Test Form -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 mb-6">
            <div class="flex items-center mb-6 pb-4 border-b border-gray-200">
                <span class="material-icons text-primary text-2xl mr-3">mark_email_read</span>
                <h2 class="text-xl font-bold text-gray-900">Send Booking Confirmation Email</h2>
            </div>
            <form action="{{ route('test.send-email') }}" method="POST" class="space-y-6">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                        <span class="material-icons text-primary text-lg mr-2">email</span>
                        Recipient Email Address
                    </label>
                    <input
                        type="email"
                        name="email"
                        id="email"
                        required
                        value="{{ old('email') }}"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition"
                        placeholder="recipient@example.com"
                    >
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <span class="material-icons text-sm mr-1">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Name Input -->
                <div>
                    <label for="name" class="flex items-center text-sm font-semibold text-gray-700 mb-2">
                        <span class="material-icons text-primary text-lg mr-2">person</span>
                        Booker Name (Optional)
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name', 'Alex') }}"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:ring-2 focus:ring-primary focus:border-primary transition"
                        placeholder="Alex Johnson"
                    >
                    @error('name')
                        <p class="mt-2 text-sm text-red-600 flex items-center">
                            <span class="material-icons text-sm mr-1">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-primary to-purple-600 hover:from-primary hover:to-purple-700 text-white font-bold py-4 px-6 rounded-xl transition duration-200 flex items-center justify-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
                    >
                        <span class="material-icons mr-2">send</span>
                        Send Confirmation Email
                    </button>
                </div>
            </form>

            <!-- Preview Info -->
            <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-l-4 border-primary rounded-lg p-4">
                <div class="flex items-start">
                    <span class="material-icons text-primary text-lg mr-3 mt-0.5">preview</span>
                    <div class="text-sm text-gray-700">
                        <p class="font-semibold mb-2">Email Preview Includes:</p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            <li>Booking confirmation with ✅ success icon</li>
                            <li>Event: 30-Minute Consultation</li>
                            <li>Date: January 30, 2026</li>
                            <li>Time: 2:00 PM - 3:00 PM</li>
                            <li>Google Meet link</li>
                            <li>View booking details button</li>
                        </ul>
                    </div>
                </div>
            </div>
            </h3>
            <ul class="text-sm text-gray-700 space-y-3">
                <li class="flex items-start">
                    <span class="material-icons text-primary text-sm mr-2 mt-0.5">check</span>
                    <span>Sends a beautiful booking confirmation email with inline CSS</span>
                </li>
                <li class="flex items-start">
                    <span class="material-icons text-primary text-sm mr-2 mt-0.5">check</span>
                    <span>No CDN dependencies - works in all email clients</span>
                </li>
                <li class="flex items-start">
                    <span class="material-icons text-primary text-sm mr-2 mt-0.5">check</span>
                    <span>Uses emoji icons that display everywhere</span>
                </li>
                <li class="flex items-start">
                    <span class="material-icons text-primary text-sm mr-2 mt-0.5">check</span>
                    <span>Check your <code class="bg-white px-2 py-0.5 rounded border border-gray-300 font-mono text-xs">.env</code> file to verify MAIL_* configuration</span>
                </li>
                <li class="flex items-start">
                    <span class="material-icons text-primary text-sm mr-2 mt-0.5">check</span>
                    <span>If using queue, make sure to run: <code class="bg-white px-2 py-1 rounded border border-gray-300 font-mono text-xs">php artisan queue:work</code></span>
                </li>
                <li class="flex items-start">
                    <span class="material-icons text-primary text-sm mr-2 mt-0.5">check</span>
                    <span>Current mail driver: <strong class="text-primary">{{ config('mail.default') }}</strong></span>
                </li>
            </ul>
        </div>

        <!-- Back Button -->
        <div class="mt-6 text-center">
            <a href="/" class="text-primary hover:text-purple-600 font-semibold inline-flex items-center transition">
                <span class="material-icons text-lg mr-1">arrow_back</span>
                Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
