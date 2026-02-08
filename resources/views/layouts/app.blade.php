<!DOCTYPE html>
<html lang="en" class="scroll-smooth light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

    @vite(['resources/css/app.css'])

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
    </style>
    @yield('additional-styles')
    @stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark min-h-screen antialiased text-slate-900 dark:text-slate-100 transition-colors duration-300">

    @stack('body-scripts')

    <!-- Simple Header (Optional - can be overridden) -->
    @if(!View::hasSection('no-header'))
    <header class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b border-slate-200 dark:border-slate-700 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <a href="/" class="flex items-center space-x-3 group">
                    <div class="w-11 h-11 bg-gradient-to-br from-primary to-indigo-700 rounded-2xl flex items-center justify-center shadow-lg shadow-primary/30 group-hover:shadow-xl group-hover:shadow-primary/40 transition-all">
                        <span class="material-icons-round text-white text-xl">event_available</span>
                    </div>
                    <span class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">{{ config('app.name') }}</span>
                </a>

                @yield('header-actions')
            </div>
        </div>
    </header>
    @endif

    <!-- Main Content -->
    <main class="@yield('container-class', 'max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10')">
        @if(session('success'))
            <div class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-200 px-4 py-3 rounded-lg flex items-center gap-3">
                <span class="material-icons-round text-green-600 dark:text-green-400">check_circle</span>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-4 py-3 rounded-lg flex items-center gap-3">
                <span class="material-icons-round text-red-600 dark:text-red-400">error</span>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Floating Theme Toggle Button -->
    @if(!View::hasSection('no-theme-toggle'))
    <button id="themeToggle" style="bottom: 30px; right: 30px;"
            class="fixed w-14 h-14 bg-white dark:bg-slate-800 rounded-full shadow-lg hover:shadow-xl border border-slate-200 dark:border-slate-700 flex items-center justify-center transition-all duration-300 hover:scale-110 z-50 group">
        <span id="themeIcon" class="material-icons-round text-amber-500 dark:text-slate-300 transition-all duration-300 group-hover:rotate-180 text-2xl"></span>
    </button>
    @endif

    <!-- Footer (Optional - can be overridden) -->
    @if(!View::hasSection('no-footer'))
    <footer class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-t border-slate-200 dark:border-slate-700 mt-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="flex items-center space-x-6 text-sm">
                    <a href="#" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">Privacy Policy</a>
                    <a href="#" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">Terms of Service</a>
                    <a href="#" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">Help Center</a>
                </div>
            </div>
        </div>
    </footer>
    @endif

    <script>
        // Theme Toggle Functionality
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const themeIcon = document.getElementById('themeIcon');
            const htmlElement = document.documentElement;
            const isAuthenticated = {{ auth()->check() ? 'true' : 'false' }};

            if (!themeToggle || !themeIcon) {
                return; // Theme toggle might be disabled
            }

            // Initialize theme on page load
            function initializeTheme() {
                let isDark = false;

                if (isAuthenticated) {
                    // For authenticated users, check server-rendered class
                    isDark = htmlElement.classList.contains('dark');
                } else {
                    // For guests, check localStorage
                    const savedTheme = localStorage.getItem('theme');
                    isDark = savedTheme === 'dark';

                    // Apply the saved theme
                    if (isDark) {
                        htmlElement.classList.add('dark');
                        htmlElement.classList.remove('light');
                    } else {
                        htmlElement.classList.add('light');
                        htmlElement.classList.remove('dark');
                    }
                }

                updateIcon(isDark);
            }

            // Update the icon based on theme
            function updateIcon(isDark) {
                themeIcon.textContent = isDark ? 'light_mode' : 'dark_mode';
            }

            // Toggle theme
            async function toggleTheme() {
                const isDark = htmlElement.classList.contains('dark');

                // Toggle classes with smooth transition
                if (isDark) {
                    htmlElement.classList.remove('dark');
                    htmlElement.classList.add('light');
                } else {
                    htmlElement.classList.remove('light');
                    htmlElement.classList.add('dark');
                }

                updateIcon(!isDark);

                // Save preference
                if (isAuthenticated) {
                    // Save to server for authenticated users
                    try {
                        await fetch('/api/user/theme', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({ dark_mode: !isDark })
                        });
                    } catch (error) {
                        console.error('Failed to save theme preference:', error);
                    }
                } else {
                    // Save to localStorage for guests
                    const newTheme = isDark ? 'light' : 'dark';
                    localStorage.setItem('theme', newTheme);
                }
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
