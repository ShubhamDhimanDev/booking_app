<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Login - MeetFlow</title>
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link
        href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400..900;1,400..900&amp;family=Plus+Jakarta+Sans:ital,wght@0,200..800;1,200..800&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Outlined" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#6366f1", // Indigo
                        "background-light": "#faf9f6", // Soft Cream
                        "background-dark": "#0f172a", // Dark Blue/Slate
                        accent: "#b45309", // Warm Amber
                    },
                    fontFamily: {
                        display: ["Playfair Display", "serif"],
                        sans: ["Plus Jakarta Sans", "sans-serif"],
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

        .font-display {
            font-family: 'Playfair Display', serif;
        }
    </style>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-900 dark:text-slate-100 transition-colors duration-300">
    <div class="min-h-screen flex">
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
        <div
            class="w-full lg:w-1/2 flex flex-col justify-center px-8 sm:px-16 md:px-24 xl:px-48 bg-background-light dark:bg-background-dark">
            <div class="max-w-md w-full mx-auto">
                <div class="mb-10 flex items-center justify-between">
                    <div class="flex items-center gap-2 group cursor-pointer">
                        <div
                            class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center text-white shadow-lg shadow-primary/20">
                            <span class="material-icons-outlined">insights</span>
                        </div>
                        <span
                            class="font-display text-2xl font-bold tracking-tight text-slate-800 dark:text-white">MeetFlow</span>
                    </div>
                    <button class="p-2 rounded-full hover:bg-slate-200 dark:hover:bg-slate-800 transition-colors"
                        onclick="document.documentElement.classList.toggle('dark')">
                        <span class="material-icons-outlined text-slate-600 dark:text-slate-400">dark_mode</span>
                    </button>
                </div>
                <div class="mb-10">
                    <h2 class="font-display text-4xl font-semibold text-slate-900 dark:text-white mb-2">Welcome Back
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400">Please enter your details to sign in to your account.
                    </p>
                </div>
                <form action="#" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2"
                            for="email">Email Address</label>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-icons-outlined text-xl">mail</span>
                            </span>
                            <input
                                class="block w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                                id="email" name="email" placeholder="name@company.com" required=""
                                type="email" />
                        </div>
                    </div>
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300"
                                for="password">Password</label>
                            <a class="text-xs font-semibold text-primary hover:underline" href="#">Forgot
                                password?</a>
                        </div>
                        <div class="relative">
                            <span
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <span class="material-icons-outlined text-xl">lock</span>
                            </span>
                            <input
                                class="block w-full pl-10 pr-12 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                                id="password" name="password" placeholder="••••••••" required="" type="password" />
                            <button
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                                type="button">
                                <span class="material-icons-outlined text-xl">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input class="h-4 w-4 text-primary focus:ring-primary border-slate-300 rounded" id="remember-me"
                            name="remember-me" type="checkbox" />
                        <label class="ml-2 block text-sm text-slate-600 dark:text-slate-400" for="remember-me">
                            Remember me for 30 days
                        </label>
                    </div>
                    <button
                        class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary/25 transform active:scale-[0.98]"
                        type="submit">
                        Sign In
                    </button>
                </form>
                <div class="relative my-8">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-background-light dark:bg-background-dark text-slate-500">Or continue
                            with</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <button
                        class="flex items-center justify-center gap-2 py-3 px-4 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors font-medium">
                        <img alt="Google" class="w-5 h-5"
                            src="https://lh3.googleusercontent.com/aida-public/AB6AXuAHiexu7A4DsB_wR5W10RKtGeyk42TookyjMNFUq9fdInsV9NDjZbay6pXj5RG4GZjsq7vM7qKjVhITD4d1gzHhrR8h4aZVeilr57RB8pY2FLJ0uoNroqPhaIMpAJ_iqfOU0-MzR44YJ6rG0JQ0-wAK6KjKzA6XD3KhuHLJ-NysipLQORGOeS988wmM1M6Rg2qM-aBvbHzss31CXmXH2yKGfhTWEbw0b-4tPjF7wbfd-_6pVkcvZ6LWiGfFXds56yhhHRurtIVxMgE" />
                        <span>Google</span>
                    </button>
                    <button
                        class="flex items-center justify-center gap-2 py-3 px-4 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors font-medium">
                        <span class="material-icons-outlined text-indigo-600">apple</span>
                        <span>Apple ID</span>
                    </button>
                </div>
                <p class="mt-10 text-center text-slate-500 dark:text-slate-400">
                    Don't have an account?
                    <a class="font-semibold text-primary hover:underline" href="#">Create an account</a>
                </p>
                <div
                    class="mt-16 pt-8 border-t border-slate-100 dark:border-slate-800 flex flex-wrap justify-center gap-6 text-xs text-slate-400 uppercase tracking-widest">
                    <a class="hover:text-primary transition-colors" href="#">Privacy Policy</a>
                    <a class="hover:text-primary transition-colors" href="#">Terms of Service</a>
                    <a class="hover:text-primary transition-colors" href="#">Help Center</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
