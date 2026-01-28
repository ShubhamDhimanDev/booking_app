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
                        class="inline-flex items-center justify-center mb-6">
                        <img src="{{ asset('images/AC-Logo.png') }}" alt="logo" class="w-12 h-12 object-cover" />
                    </div>
                    <h1 class="font-display text-6xl mb-4 leading-tight">Transform Your Life <br />Through Energy.</h1>
                    <p class="text-xl text-indigo-100 max-w-md mx-auto font-light leading-relaxed">
                        Experience energy-based astrology with no birth charts needed. Join thousands who discovered clarity, purpose, and transformation through personalized guidance.
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
                        "Chaitanya revealed truths I never knew and transformed my life with powerful energy work. His guidance continues to light my path."
                    </p>
                    <div class="flex items-center gap-3">
                        <img alt="User avatar" class="w-10 h-10 rounded-full bg-white/20"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAD9KEzF-zAORGc2JdMozYOq4lAwMd-mcXj2xQz-4xlUEbVVEMEFpYedWMymi2Hos9NmQmJ7ctf9viyMEJE6RXvKvQrG3gPF_rFAagnKEVpL3a8ZNwTZEEWwyvOuy-jvey3vNDKKIwhyoph3WIsl6MaMRF4o9ektO8xLFBjKAO8Vw_6nVPvNphjz-FvWwRRWPtSCHjMq-BScKwAOrpnz0FCZxoVaPkcYuvrGQ6ZRYtEKz7LyPD_nGPjJ-xUy4WLZdGMeBaVpMd6CBM" />
                        <div class="text-left">
                            <p class="font-semibold text-sm">Sweekrutha</p>
                            <p class="text-xs text-indigo-200">Verified Client</p>
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
                            class="inline-flex items-center justify-center">
                            <img src="{{ asset('images/AC-Logo.png') }}" alt="logo" class="w-12  object-cover" />
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
                     For Help Contact - <a href="tel:+916366282505" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">+91 6366282505</a>
                    <a href="https://astrochaitanya.com/privacy-policy/" target="_blank" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">Privacy Policy</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Floating Theme Toggle Button -->
    <button id="themeToggle" style="bottom: 30px; right: 30px;"
            class="fixed w-14 h-14 bg-white dark:bg-slate-800 rounded-full shadow-lg hover:shadow-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center transition-all duration-300 hover:scale-110 z-50 group">
        <span id="themeIcon" class="material-icons-outlined text-amber-500 dark:text-slate-300 transition-all duration-300 group-hover:rotate-180 text-2xl"></span>
    </button>

    <script>
        // Theme Toggle Functionality for Auth Pages (Guest only)
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const htmlElement = document.documentElement;

            if (!themeToggle || !themeIcon) {
                console.error('Theme toggle elements not found');
                return;
            }

            // Initialize theme on page load
            function initializeTheme() {
                // Check localStorage for guests
                const savedTheme = localStorage.getItem('theme');
                const isDark = savedTheme === 'dark';

                // Apply the saved theme
                if (isDark) {
                    htmlElement.classList.add('dark');
                    htmlElement.classList.remove('light');
                } else {
                    htmlElement.classList.add('light');
                    htmlElement.classList.remove('dark');
                }

                updateIcon(isDark);
            }

            // Update the icon based on theme
            function updateIcon(isDark) {
                themeIcon.textContent = isDark ? 'light_mode' : 'dark_mode';
            }

            // Toggle theme
            function toggleTheme() {
                const isDark = htmlElement.classList.contains('dark');
                const newTheme = isDark ? 'light' : 'dark';

                // Toggle classes with smooth transition
                if (isDark) {
                    htmlElement.classList.remove('dark');
                    htmlElement.classList.add('light');
                } else {
                    htmlElement.classList.remove('light');
                    htmlElement.classList.add('dark');
                }

                updateIcon(!isDark);

                // Save to localStorage for guests
                localStorage.setItem('theme', newTheme);
            }

            // Event listener
            themeToggle.addEventListener('click', toggleTheme);

            // Initialize on load
            initializeTheme();
        });
    </script>

    @stack('scripts')
</body>

</html>
