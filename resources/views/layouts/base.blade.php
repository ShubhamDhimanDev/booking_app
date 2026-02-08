<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'MeetFlow'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=outfit:300,400,500,600,700,800,900" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body
    x-data="{
        page: '{{ $page ?? 'dashboard' }}',
        loaded: true,
        darkMode: false,
        stickyMenu: false,
        sidebarToggle: false,
        scrollTop: false
    }"
    x-init="
        darkMode = JSON.parse(localStorage.getItem('darkMode'));
        $watch('darkMode', value => localStorage.setItem('darkMode', JSON.stringify(value)))"
    :class="{'dark bg-gray-900': darkMode === true}"
>
    <!-- ===== Preloader Start ===== -->
    <div
        x-show="loaded"
        x-init="window.addEventListener('DOMContentLoaded', () => {setTimeout(() => loaded = false, 500)})"
        class="fixed left-0 top-0 z-99999 flex h-screen w-screen items-center justify-center bg-white dark:bg-black"
    >
        <div class="h-16 w-16 animate-spin rounded-full border-4 border-solid border-brand-500 border-t-transparent"></div>
    </div>
    <!-- ===== Preloader End ===== -->

    <!-- ===== Page Wrapper Start ===== -->
    <div class="flex h-screen overflow-hidden">
        <!-- ===== Sidebar Start ===== -->
        @yield('sidebar')
        <!-- ===== Sidebar End ===== -->

        <!-- ===== Content Area Start ===== -->
        <div class="relative flex flex-col flex-1 overflow-x-hidden overflow-y-auto">
            <!-- Small Device Overlay Start -->
            <div
                x-cloak
                :class="sidebarToggle ? 'translate-x-0' : '-translate-x-full'"
                @click="sidebarToggle = !sidebarToggle"
                class="fixed inset-0 z-9998 bg-black/40 transition-all duration-200 lg:hidden"
            ></div>
            <!-- Small Device Overlay End -->

            <!-- ===== Header Start ===== -->
            @include('partials.header')
            <!-- ===== Header End ===== -->

            <!-- ===== Main Content Start ===== -->
            <main>
                <div class="p-4 mx-auto max-w-screen-2xl md:p-6">
                    @if (session('success'))
                        <div class="mb-4 p-4 rounded-lg bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 p-4 rounded-lg bg-red-50 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                            {{ session('error') }}
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
            <!-- ===== Main Content End ===== -->
        </div>
        <!-- ===== Content Area End ===== -->
    </div>
    <!-- ===== Page Wrapper End ===== -->

    @stack('scripts')
</body>
</html>
