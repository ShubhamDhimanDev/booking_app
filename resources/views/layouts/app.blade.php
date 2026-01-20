<!DOCTYPE html>
<html lang="en" class="scroll-smooth {{ App\Services\ThemeService::isDarkModeEnabled() ? 'dark' : 'light' }}">
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

    {!! \App\Services\TrackingService::getBaseScript() !!}
    {!! \App\Services\TrackingService::getGoogleBaseScript() !!}

    @stack('head-scripts')

    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .dark .glass-card {
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .profile-dropdown {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            margin-top: 0.5rem;
            min-width: 12rem;
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            z-index: 100;
        }
        .dark .profile-dropdown {
            background: #1e293b;
            border-color: #334155;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
        .profile-dropdown.show {
            display: block;
            animation: slideDown 0.2s ease-out;
        }
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
    @yield('additional-styles')
    @stack('styles')
</head>
<body class="@auth bg-background-light dark:bg-background-dark @else bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 @endauth min-h-screen antialiased text-slate-900 dark:text-slate-100 transition-colors duration-300">

    @stack('body-scripts')

    <!-- Modern Header -->
    <header class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-b border-slate-200 dark:border-slate-700 sticky top-0 z-50 shadow-sm">
        <div class="@auth max-w-7xl @else max-w-6xl @endauth mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <a href="/" class="flex items-center space-x-3 group">
                    <div class="w-11 h-11 bg-gradient-to-br from-primary to-indigo-700 rounded-2xl flex items-center justify-center shadow-lg shadow-primary/30 group-hover:shadow-xl group-hover:shadow-primary/40 transition-all">
                        <span class="material-icons-round text-white text-xl">event_available</span>
                    </div>
                    <span class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">{{ config('app.name') }}</span>
                </a>

                @auth
                {{-- Authenticated User Navigation --}}
                <div class="flex items-center gap-6">
                    <div class="hidden md:flex gap-6">
                        <a class="text-sm font-medium {{ request()->routeIs('user.bookings.*') ? 'font-semibold text-primary border-b-2 border-primary pb-1' : 'text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary' }} transition-colors"
                           href="{{ route('user.bookings.index') }}">My Bookings</a>
                        <a class="text-sm font-medium {{ request()->routeIs('transactions.*') ? 'font-semibold text-primary border-b-2 border-primary pb-1' : 'text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary' }} transition-colors"
                           href="{{ route('transactions.index') }}">Transactions</a>
                    </div>
                    <div class="relative flex items-center gap-3 pl-6 border-l border-slate-200 dark:border-slate-700">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-500 dark:text-slate-400">Member</p>
                        </div>
                        <button onclick="toggleProfileDropdown()" class="relative focus:outline-none">
                            @if(auth()->user()->avatar)
                            <img alt="Profile" class="h-9 w-9 rounded-full ring-2 ring-primary/20 cursor-pointer hover:ring-4 transition-all" src="{{ auth()->user()->avatar }}" />
                            @else
                            <div class="h-9 w-9 rounded-full ring-2 ring-primary/20 bg-primary text-white flex items-center justify-center font-bold text-sm cursor-pointer hover:ring-4 transition-all">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            @endif
                        </button>
                        <div id="profileDropdown" class="profile-dropdown">
                            <a href="{{ route('user.bookings.index') }}" class="flex md:hidden items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-slate-700 dark:text-slate-200 {{ request()->routeIs('user.bookings.*') ? 'bg-primary/10 text-primary dark:bg-primary/20' : '' }}">
                                <span class="material-icons-round text-lg {{ request()->routeIs('user.bookings.*') ? 'text-primary' : 'text-slate-500 dark:text-slate-400' }}">event_note</span>
                                <span class="text-sm font-medium">My Bookings</span>
                            </a>
                            <a href="{{ route('transactions.index') }}" class="flex md:hidden items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-slate-700 dark:text-slate-200 {{ request()->routeIs('transactions.*') ? 'bg-primary/10 text-primary dark:bg-primary/20' : '' }} border-b border-slate-100 dark:border-slate-700">
                                <span class="material-icons-round text-lg {{ request()->routeIs('transactions.*') ? 'text-primary' : 'text-slate-500 dark:text-slate-400' }}">receipt_long</span>
                                <span class="text-sm font-medium">Transactions</span>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-slate-700 dark:text-slate-200">
                                <span class="material-icons-round text-lg text-primary">person</span>
                                <span class="text-sm font-medium">Profile</span>
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="m-0">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors text-slate-700 dark:text-slate-200 border-t border-slate-100 dark:border-slate-700">
                                    <span class="material-icons-round text-lg text-red-500">logout</span>
                                    <span class="text-sm font-medium">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                {{-- Guest Navigation --}}
                <div class="flex items-center space-x-3">
                    <div class="hidden sm:flex items-center space-x-3 @yield('badge-color', 'bg-blue-50 dark:bg-blue-900/30') px-4 py-2 rounded-full border @yield('badge-border', 'border-blue-200 dark:border-blue-700')">
                        <span class="material-icons-round @yield('badge-text-color', 'text-blue-600 dark:text-blue-400') text-lg">@yield('badge-icon', 'verified_user')</span>
                        <span class="text-sm @yield('badge-text-color', 'text-blue-700 dark:text-blue-300') font-semibold">@yield('badge-text', 'Secure Booking')</span>
                    </div>
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary transition-colors">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-lg transition-all shadow-sm hover:shadow-md">
                        <span class="material-icons-round text-sm">person_add</span>
                        Sign Up
                    </a>
                </div>
                @endauth
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
    <main class="@auth max-w-7xl @else max-w-6xl @endauth mx-auto px-4 sm:px-6 lg:px-8 @auth py-10 @else py-8 sm:py-12 @endauth">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white/80 dark:bg-slate-900/80 backdrop-blur-lg border-t border-slate-200 dark:border-slate-700 @auth mt-20 @else mt-20 @endauth">
        <div class="@auth max-w-7xl @else max-w-6xl @endauth mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="flex flex-col sm:flex-row items-center justify-between space-y-4 sm:space-y-0">
                <p class="text-sm text-slate-600 dark:text-slate-400 font-medium">© {{ date('Y') }} {{ config('app.name') }}@auth  Platform @endauth. All rights reserved.</p>
                <div class="flex items-center space-x-6 text-sm">
                    <a href="#" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">@auth Support @else Help @endauth</a>
                    <a href="#" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">@guest Contact @endguest Privacy</a>
                    <a href="#" class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary font-medium transition-colors">@guest Privacy @else Terms @endauth</a>
                </div>
            </div>
        </div>
    </footer>

    @auth
    <script>
        function toggleProfileDropdown() {
            const dropdown = document.getElementById('profileDropdown');
            dropdown.classList.toggle('show');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('profileDropdown');
            const button = event.target.closest('button[onclick="toggleProfileDropdown()"]');

            if (!button && !dropdown.contains(event.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
    @endauth

    @stack('scripts')
</body>
</html>
