<!DOCTYPE html>
<html class="{{ App\Services\ThemeService::isDarkModeEnabled() ? 'dark' : 'light' }}" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MeetFlow')</title>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&amp;family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />

    @vite(['resources/css/app.css'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .font-display {
            font-family: 'Playfair Display', serif;
        }
    </style>
    @stack('styles')
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 transition-colors duration-300">
    <div class="min-h-screen flex">
        <!-- Left Side - Hero Section -->
        <div class="hidden lg:flex lg:w-1/2 relative bg-primary overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-primary via-indigo-700 to-indigo-900 opacity-90"></div>
            <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-accent/20 rounded-full blur-3xl"></div>
            <div class="relative z-10 w-full flex flex-col justify-center items-center p-16 text-white text-center">
                <div class="mb-12">
                    <div
                        class="inline-flex items-center justify-center p-4 bg-white/10 backdrop-blur-md rounded-2xl mb-6">
                        <span class="material-icons-outlined text-5xl">event_available</span>
                    </div>
                    <h1 class="font-display text-6xl mb-4 leading-tight">Master Your <br />Time and Craft.</h1>
                    <p class="text-xl text-indigo-100 max-w-md mx-auto font-light leading-relaxed">
                        Join our community of professionals and enthusiasts. Seamlessly book sessions, manage events,
                        and grow with us.
                    </p>
                </div>
                <div class="mt-auto bg-white/5 backdrop-blur-sm p-8 rounded-3xl border border-white/10 max-w-sm">
                    <div class="flex items-center gap-1 mb-3 text-amber-400">
                        <span class="material-icons-outlined text-sm">star</span>
                        <span class="material-icons-outlined text-sm">star</span>
                        <span class="material-icons-outlined text-sm">star</span>
                        <span class="material-icons-outlined text-sm">star</span>
                        <span class="material-icons-outlined text-sm">star</span>
                    </div>
                    <p class="text-lg italic text-indigo-50 leading-snug mb-4">
                        "The most elegant booking experience I've used in years. It just works and looks beautiful."
                    </p>
                    <div class="flex items-center gap-3">
                        <img alt="User avatar" class="w-10 h-10 rounded-full bg-white/20"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAD9KEzF-zAORGc2JdMozYOq4lAwMd-mcXj2xQz-4xlUEbVVEMEFpYedWMymi2Hos9NmQmJ7ctf9viyMEJE6RXvKvQrG3gPF_rFAagnKEVpL3a8ZNwTZEEWwyvOuy-jvey3vNDKKIwhyoph3WIsl6MaMRF4o9ektO8xLFBjKAO8Vw_6nVPvNphjz-FvWwRRWPtSCHjMq-BScKwAOrpnz0FCZxoVaPkcYuvrGQ6ZRYtEKz7LyPD_nGPjJ-xUy4WLZdGMeBaVpMd6CBM" />
                        <div class="text-left">
                            <p class="font-semibold text-sm">Shubham Dhiman</p>
                            <p class="text-xs text-indigo-200">Creative Director</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form Content -->
        <div
            class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 md:px-24 xl:px-48 bg-background-light dark:bg-background-dark">
            <div class="max-w-md w-full mx-auto">
                <!-- Logo -->
                <div class="mb-10 flex items-center justify-between">
                    <div class="flex items-center gap-2 group cursor-pointer">
                        <div
                            class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary/20">
                            <span class="material-icons-outlined">insights</span>
                        </div>
                        <span
                            class="font-display text-2xl font-bold tracking-tight text-slate-800 dark:text-white">{{ config('app.name', 'MeetFlow') }}</span>
                    </div>
                </div>

                <!-- Page Content -->
                @yield('content')

                <!-- Footer Links -->
                <div
                    class="mt-16 pt-8 border-t border-slate-100 dark:border-slate-800 flex flex-wrap justify-center gap-6 text-xs text-slate-400 uppercase tracking-widest">
                    <a class="hover:text-primary transition-colors" href="#">Privacy Policy</a>
                    <a class="hover:text-primary transition-colors" href="#">Terms of Service</a>
                    <a class="hover:text-primary transition-colors" href="#">Help Center</a>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
