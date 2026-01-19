<!DOCTYPE html>
<html lang="en" class="{{ App\Services\ThemeService::isDarkModeEnabled() ? 'dark' : 'light' }}">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'MeetFlow')</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#6366f1",
                        "background-light": "#f8fafc",
                        "background-dark": "#0f172a",
                    },
                    fontFamily: {
                        display: ["Plus Jakarta Sans", "sans-serif"],
                    },
                    borderRadius: {
                        DEFAULT: "0.75rem",
                    },
                },
            },
        };
    </script>
    <style>
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
    @stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen transition-colors duration-300">
    <nav class="sticky top-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center gap-2">
                    <div class="bg-primary p-2 rounded-lg">
                        <span class="material-icons-round text-white">event_available</span>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-slate-800 dark:text-white">MeetFlow</span>
                </div>
                <div class="flex items-center gap-6">
                    <div class="hidden md:flex gap-6">
                        <a class="text-sm font-medium {{ request()->routeIs('user.bookings.*') ? 'font-semibold text-primary border-b-2 border-primary pb-1' : 'text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary' }}"
                           href="{{ route('user.bookings.index') }}">My Bookings</a>
                        <a class="text-sm font-medium {{ request()->routeIs('transactions.*') ? 'font-semibold text-primary border-b-2 border-primary pb-1' : 'text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary' }}"
                           href="{{ route('transactions.index') }}">Transactions</a>
                    </div>
                    <div class="relative flex items-center gap-3 pl-6 border-l border-slate-200 dark:border-slate-700">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-semibold text-slate-900 dark:text-white">{{ auth()->user()->name }}</p>
                            <p class="text-[10px] text-slate-500">Member</p>
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
            </div>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        @yield('content')
    </main>

    <footer class="mt-20 pb-10 text-center text-slate-400 dark:text-slate-500 text-sm">
        <p>© {{ date('Y') }} MeetFlow Platform. All rights reserved.</p>
        <div class="flex justify-center gap-4 mt-2">
            <a class="hover:text-primary" href="#">Support</a>
            <span class="text-slate-300">•</span>
            <a class="hover:text-primary" href="#">Privacy</a>
            <span class="text-slate-300">•</span>
            <a class="hover:text-primary" href="#">Terms</a>
        </div>
    </footer>

    {!! \App\Services\TrackingService::getBaseScript() !!}

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

    @stack('scripts')
</body>
</html>
