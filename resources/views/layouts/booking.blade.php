<!DOCTYPE html>
<html lang="en" class="scroll-smooth {{ App\Services\ThemeService::isDarkModeEnabled() ? 'dark' : 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - {{ config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    animation: {
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="{{ asset('style.css') }}">

    @stack('head-scripts')

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        @yield('additional-styles')
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 min-h-screen antialiased transition-colors duration-300">

    @stack('body-scripts')

    <!-- Modern Header -->
    <header class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b border-slate-200 dark:border-slate-700 sticky top-0 z-50 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <a href="/" class="flex items-center space-x-3 group">
                    <div class="w-11 h-11 bg-gradient-to-br from-primary to-indigo-700 rounded-2xl flex items-center justify-center shadow-lg shadow-primary/30 group-hover:shadow-xl group-hover:shadow-primary/40 transition-all">
                        <span class="material-icons-round text-white text-xl">@yield('header-icon', 'event_note')</span>
                    </div>
                    <span class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">{{ config('app.name') }}</span>
                </a>
                <div class="flex items-center space-x-3 @yield('badge-color', 'bg-blue-50 dark:bg-blue-900/30') px-4 py-2 rounded-full border @yield('badge-border', 'border-blue-200 dark:border-blue-700')">
                    <span class="material-icons-round @yield('badge-text-color', 'text-blue-600 dark:text-blue-400') text-lg">@yield('badge-icon', 'verified_user')</span>
                    <span class="hidden sm:inline text-sm @yield('badge-text-color', 'text-blue-700 dark:text-blue-300') font-semibold">@yield('badge-text', 'Secure Booking')</span>
                </div>
            </div>
        </div>
    </header>

    @if(View::hasSection('loader'))
        <!-- Loader -->
        <div id="loader" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-white/80 dark:bg-slate-900/80 backdrop-blur-md">
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl text-center border border-slate-200 dark:border-slate-700">
                <div class="w-16 h-16 border-4 border-slate-200 dark:border-slate-700 border-t-primary rounded-full animate-spin mx-auto mb-4"></div>
                <p class="text-lg font-bold text-slate-900 dark:text-white">@yield('loader-text', 'Please wait...')</p>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-t border-slate-200 dark:border-slate-700 mt-20">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">© {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
                <div class="flex items-center space-x-6 text-sm">
                    <a href="#" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">Help</a>
                    <a href="#" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">Contact</a>
                    <a href="#" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">Privacy</a>
                </div>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
