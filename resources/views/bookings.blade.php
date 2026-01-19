<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>My Bookings | AstroChaitanya Style</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
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
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 min-h-screen transition-colors duration-300">
    <nav
        class="sticky top-0 z-50 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200 dark:border-slate-800">
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
                        <a class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary"
                            href="#">Dashboard</a>
                        <a class="text-sm font-semibold text-primary border-b-2 border-primary pb-1" href="#">My
                            Bookings</a>
                        <a class="text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary"
                            href="#">Transactions</a>
                    </div>
                    <div class="flex items-center gap-3 pl-6 border-l border-slate-200 dark:border-slate-700">
                        <div class="text-right hidden sm:block">
                            <p class="text-xs font-semibold text-slate-900 dark:text-white">Shubham Dhiman</p>
                            <p class="text-[10px] text-slate-500">Pro Member</p>
                        </div>
                        <img alt="Profile" class="h-9 w-9 rounded-full ring-2 ring-primary/20"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuBJL-EHMDVg-UwrCfnnzsFa2egrApRL7zdp7v1aE0JnLmLCdIyYA_Lna4vyvJzRHtdNzQbdKr9Yz4eqW0SgFH1hXkjYV6pFw-4FUW9UHIvpLPwnsohi2g_pqEB2OihzteqkLUkoJZhGkW4ng_8oT0jwwDZM9iQ631Tkz05sUlAzDi4dprl-Hq6tNjAuCTyTlq5JtpBce7BkzXf-9QSAUb7FyerbIa10BmyFkwXGprabw7JTJEYVI0E6ds7tvVaTyTzizjsbfSJ57VA" />
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="mb-10 flex flex-col md:flex-row md:items-end justify-between gap-4">
            <div>
                <h1
                    class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
                    <span class="material-icons-round text-primary text-4xl">dashboard_customize</span>
                    My Bookings
                </h1>
                <p class="mt-2 text-slate-500 dark:text-slate-400 max-w-2xl">
                    Track, manage, and reschedule your upcoming professional consultations and events in one beautiful
                    workspace.
                </p>
            </div>
            <div class="flex gap-3">
                <div class="relative">
                    <input
                        class="pl-10 pr-4 py-2 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all w-full md:w-64"
                        placeholder="Search events..." type="text" />
                    <span class="material-icons-round absolute left-3 top-2.5 text-slate-400 text-sm">search</span>
                </div>
                <button
                    class="flex items-center gap-2 px-4 py-2 bg-primary text-white rounded-full text-sm font-semibold hover:bg-opacity-90 transition-all shadow-lg shadow-primary/25">
                    <span class="material-icons-round text-sm">add</span>
                    New Booking
                </button>
            </div>
        </div>
        <div class="flex flex-wrap gap-2 mb-8">
            <button class="px-4 py-1.5 rounded-full text-xs font-bold bg-primary text-white">All Events (24)</button>
            <button
                class="px-4 py-1.5 rounded-full text-xs font-bold bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors">Pending
                (1)</button>
            <button
                class="px-4 py-1.5 rounded-full text-xs font-bold bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors">Confirmed
                (18)</button>
            <button
                class="px-4 py-1.5 rounded-full text-xs font-bold bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 hover:border-primary transition-colors">Completed
                (5)</button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div
                class="group bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm hover:shadow-2xl transition-all duration-300 border border-slate-100 dark:border-slate-700 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4">
                    <span
                        class="flex items-center gap-1.5 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Pending Payment
                    </span>
                </div>
                <div class="mb-6">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">#BK-9042</span>
                    <h3
                        class="text-xl font-bold text-slate-800 dark:text-white mt-1 group-hover:text-primary transition-colors">
                        Strategic Design Consult</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">with Shubham Dhiman</p>
                </div>
                <div class="space-y-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-xl">calendar_today</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Date</p>
                            <p class="text-sm font-semibold dark:text-slate-200">Wed, 28 Jan 2026</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-xl">schedule</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Time</p>
                            <p class="text-sm font-semibold dark:text-slate-200">03:00 AM (60 min)</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-cyan-500 hover:bg-cyan-600 text-white rounded-2xl text-sm font-bold transition-all shadow-lg shadow-cyan-500/20">
                        <span class="material-icons-round text-lg">payments</span>
                        Pay Now
                    </button>
                    <button
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-600 dark:text-slate-300 rounded-2xl text-sm font-bold transition-all">
                        <span class="material-icons-round text-lg">sync</span>
                        Reschedule
                    </button>
                </div>
            </div>
            <div
                class="group bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm hover:shadow-2xl transition-all duration-300 border border-slate-100 dark:border-slate-700 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4">
                    <span
                        class="flex items-center gap-1.5 px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Confirmed
                    </span>
                </div>
                <div class="mb-6">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">#BK-9041</span>
                    <h3
                        class="text-xl font-bold text-slate-800 dark:text-white mt-1 group-hover:text-primary transition-colors">
                        System Architecture Review</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">with Shubham Dhiman</p>
                </div>
                <div class="space-y-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-xl">calendar_today</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Date</p>
                            <p class="text-sm font-semibold dark:text-slate-200">Fri, 31 Jan 2026</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-xl">schedule</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Time</p>
                            <p class="text-sm font-semibold dark:text-slate-200">03:00 AM (60 min)</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-2xl text-sm font-bold transition-all shadow-lg shadow-primary/20">
                        <span class="material-icons-round text-lg">videocam</span>
                        Join Meeting
                    </button>
                    <button
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-600 dark:text-slate-300 rounded-2xl text-sm font-bold transition-all">
                        <span class="material-icons-round text-lg">sync</span>
                        Reschedule
                    </button>
                </div>
            </div>
            <div
                class="group bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm hover:shadow-2xl transition-all duration-300 border border-slate-100 dark:border-slate-700 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4">
                    <span
                        class="flex items-center gap-1.5 px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Confirmed
                    </span>
                </div>
                <div class="mb-6">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">#BK-9040</span>
                    <h3
                        class="text-xl font-bold text-slate-800 dark:text-white mt-1 group-hover:text-primary transition-colors">
                        Frontend Optimization</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">with Shubham Dhiman</p>
                </div>
                <div class="space-y-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-xl">calendar_today</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Date</p>
                            <p class="text-sm font-semibold dark:text-slate-200">Sat, 30 Jan 2026</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-xl">schedule</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Time</p>
                            <p class="text-sm font-semibold dark:text-slate-200">03:00 AM (60 min)</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-2xl text-sm font-bold transition-all shadow-lg shadow-primary/20">
                        <span class="material-icons-round text-lg">videocam</span>
                        Join Meeting
                    </button>
                    <button
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-600 dark:text-slate-300 rounded-2xl text-sm font-bold transition-all">
                        <span class="material-icons-round text-lg">sync</span>
                        Reschedule
                    </button>
                </div>
            </div>
            <div
                class="group bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm hover:shadow-2xl transition-all duration-300 border border-slate-100 dark:border-slate-700 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4">
                    <span
                        class="flex items-center gap-1.5 px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Confirmed
                    </span>
                </div>
                <div class="mb-6">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">#BK-9039</span>
                    <h3
                        class="text-xl font-bold text-slate-800 dark:text-white mt-1 group-hover:text-primary transition-colors">
                        Project Discovery Call</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">with Shubham Dhiman</p>
                </div>
                <div class="space-y-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-xl">calendar_today</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Date</p>
                            <p class="text-sm font-semibold dark:text-slate-200">Fri, 22 Jan 2026</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-xl">schedule</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Time</p>
                            <p class="text-sm font-semibold dark:text-slate-200">03:00 AM (60 min)</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-2xl text-sm font-bold transition-all shadow-lg shadow-primary/20">
                        <span class="material-icons-round text-lg">videocam</span>
                        Join Meeting
                    </button>
                    <button
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-600 dark:text-slate-300 rounded-2xl text-sm font-bold transition-all">
                        <span class="material-icons-round text-lg">sync</span>
                        Reschedule
                    </button>
                </div>
            </div>
            <div
                class="group bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm hover:shadow-2xl transition-all duration-300 border border-slate-100 dark:border-slate-700 relative overflow-hidden">
                <div class="absolute top-0 right-0 p-4">
                    <span
                        class="flex items-center gap-1.5 px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Confirmed
                    </span>
                </div>
                <div class="mb-6">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">#BK-9038</span>
                    <h3
                        class="text-xl font-bold text-slate-800 dark:text-white mt-1 group-hover:text-primary transition-colors">
                        API Integration Workshop</h3>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">with Shubham Dhiman</p>
                </div>
                <div class="space-y-4 mb-8">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-xl">calendar_today</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Date</p>
                            <p class="text-sm font-semibold dark:text-slate-200">Tue, 20 Jan 2026</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                            <span class="material-icons-round text-xl">schedule</span>
                        </div>
                        <div>
                            <p class="text-[10px] uppercase font-bold text-slate-400">Time</p>
                            <p class="text-sm font-semibold dark:text-slate-200">11:00 AM (60 min)</p>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                    <button
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-2xl text-sm font-bold transition-all shadow-lg shadow-primary/20">
                        <span class="material-icons-round text-lg">videocam</span>
                        Join Meeting
                    </button>
                    <button
                        class="flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-600 dark:text-slate-300 rounded-2xl text-sm font-bold transition-all">
                        <span class="material-icons-round text-lg">sync</span>
                        Reschedule
                    </button>
                </div>
            </div>
            <div
                class="group bg-slate-50/50 dark:bg-slate-800/30 rounded-3xl p-6 border-2 border-dashed border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-center gap-4 min-h-[350px]">
                <div
                    class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                    <span class="material-icons-round text-3xl">add</span>
                </div>
                <div>
                    <h3 class="font-bold text-slate-600 dark:text-slate-400">Book another session</h3>
                    <p class="text-xs text-slate-400 mt-1 max-w-[200px]">Unlock more insights and grow your expertise
                        today.</p>
                </div>
                <button
                    class="px-6 py-2 bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700 rounded-full text-xs font-bold hover:border-primary hover:text-primary transition-all shadow-sm">
                    View Mentors
                </button>
            </div>
        </div>
        <div
            class="mt-12 flex flex-col sm:flex-row items-center justify-between gap-6 pt-8 border-t border-slate-200 dark:border-slate-800">
            <p class="text-sm text-slate-500 dark:text-slate-400">Showing 1-5 of 24 bookings</p>
            <div class="flex gap-2">
                <button
                    class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-primary hover:border-primary transition-all">
                    <span class="material-icons-round">chevron_left</span>
                </button>
                <button
                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-primary text-white font-bold shadow-lg shadow-primary/20">1</button>
                <button
                    class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-primary hover:border-primary transition-all">2</button>
                <button
                    class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-600 dark:text-slate-400 hover:text-primary hover:border-primary transition-all">3</button>
                <button
                    class="w-10 h-10 flex items-center justify-center rounded-xl border border-slate-200 dark:border-slate-700 text-slate-400 hover:text-primary hover:border-primary transition-all">
                    <span class="material-icons-round">chevron_right</span>
                </button>
            </div>
        </div>
    </main>
    <footer class="mt-20 pb-10 text-center text-slate-400 dark:text-slate-500 text-sm">
        <p>© 2026 MeetFlow Platform. All rights reserved.</p>
        <div class="flex justify-center gap-4 mt-2">
            <a class="hover:text-primary" href="#">Support</a>
            <span class="text-slate-300">•</span>
            <a class="hover:text-primary" href="#">Privacy</a>
            <span class="text-slate-300">•</span>
            <a class="hover:text-primary" href="#">Terms</a>
        </div>
    </footer>
    <button
        class="fixed bottom-8 right-8 w-12 h-12 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-full shadow-2xl flex items-center justify-center text-slate-600 dark:text-yellow-400 hover:scale-110 transition-transform z-50"
        onclick="document.documentElement.classList.toggle('dark')">
        <span class="material-icons-round">dark_mode</span>
    </button>

</body>

</html>
