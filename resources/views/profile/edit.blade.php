@extends('layouts.user')

@section('title', 'Edit Profile - MeetFlow')

@section('content')
<div class="mb-10">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
        <span class="material-icons-round text-primary text-4xl">account_circle</span>
        My Profile
    </h1>
    <p class="mt-2 text-slate-500 dark:text-slate-400 max-w-2xl">
        Manage your account information and security settings.
    </p>
</div>

@if(session('alert_type'))
<div class="mb-6 p-4 rounded-xl border-l-4 {{ session('alert_type') === 'success' ? 'bg-emerald-50 dark:bg-emerald-900/20 border-emerald-500' : 'bg-red-50 dark:bg-red-900/20 border-red-500' }}">
    <div class="flex items-start gap-3 {{ session('alert_type') === 'success' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
        <span class="material-icons-round text-xl">{{ session('alert_type') === 'success' ? 'check_circle' : 'error' }}</span>
        <div>
            <strong class="font-bold block mb-1">{{ session('alert_type') === 'success' ? 'Success' : 'Error' }}</strong>
            <p class="text-sm">{{ session('alert_message') }}</p>
        </div>
    </div>
</div>
@endif

@if($errors->any())
<div class="mb-6 p-4 rounded-xl border-l-4 bg-red-50 dark:bg-red-900/20 border-red-500">
    <div class="flex items-start gap-3 text-red-700 dark:text-red-400">
        <span class="material-icons-round text-xl">error</span>
        <div>
            <strong class="font-bold block mb-2">Please fix the following errors:</strong>
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Profile Info Card -->
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-700 sticky top-24">
            <div class="flex flex-col items-center text-center">
                @if(auth()->user()->avatar)
                <img alt="Profile" class="h-24 w-24 rounded-full ring-4 ring-primary/20 mb-4" src="{{ auth()->user()->avatar }}" />
                @else
                <div class="h-24 w-24 rounded-full ring-4 ring-primary/20 bg-primary text-white flex items-center justify-center font-bold text-3xl mb-4">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                @endif
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-1">{{ auth()->user()->name }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mb-1">{{ auth()->user()->email }}</p>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary/10 text-primary rounded-full text-xs font-bold mt-3">
                    <span class="material-icons-round text-xs">verified</span>
                    Member Since {{ auth()->user()->created_at->format('M Y') }}
                </span>
            </div>
        </div>
    </div>

    <!-- Profile Edit Forms -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Personal Information -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-700">
                <div class="bg-primary/10 p-2 rounded-xl">
                    <span class="material-icons-round text-primary">person</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Personal Information</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Update your account details</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            <span class="flex items-center gap-2">
                                <span class="material-icons-round text-sm">badge</span>
                                Full Name
                            </span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                            placeholder="Enter your full name" />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            <span class="flex items-center gap-2">
                                <span class="material-icons-round text-sm">email</span>
                                Email Address
                            </span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                            placeholder="your@email.com" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-primary hover:bg-indigo-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-primary/25 transform active:scale-[0.98]">
                        <span class="material-icons-round text-lg">save</span>
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Security Settings -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-100 dark:border-slate-700">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-100 dark:border-slate-700">
                <div class="bg-amber-100 dark:bg-amber-900/30 p-2 rounded-xl">
                    <span class="material-icons-round text-amber-600 dark:text-amber-400">lock</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white">Security Settings</h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400">Change your password</p>
                </div>
            </div>

            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="name" value="{{ auth()->user()->name }}">
                <input type="hidden" name="email" value="{{ auth()->user()->email }}">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            <span class="flex items-center gap-2">
                                <span class="material-icons-round text-sm">lock</span>
                                New Password
                            </span>
                        </label>
                        <input type="password" name="password"
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                            placeholder="Enter new password" />
                        <p class="mt-2 text-xs text-slate-500 dark:text-slate-400">Leave blank to keep current password</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            <span class="flex items-center gap-2">
                                <span class="material-icons-round text-sm">lock_outline</span>
                                Confirm New Password
                            </span>
                        </label>
                        <input type="password" name="password_confirmation"
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                            placeholder="Confirm new password" />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-amber-500 hover:bg-amber-600 text-white font-semibold rounded-xl transition-all shadow-lg shadow-amber-500/25 transform active:scale-[0.98]">
                        <span class="material-icons-round text-lg">security</span>
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Danger Zone -->
        <div class="bg-red-50 dark:bg-red-900/20 rounded-3xl p-6 border-2 border-red-200 dark:border-red-800">
            <div class="flex items-center gap-3 mb-4">
                <div class="bg-red-100 dark:bg-red-900/50 p-2 rounded-xl">
                    <span class="material-icons-round text-red-600 dark:text-red-400">warning</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-red-900 dark:text-red-300">Danger Zone</h2>
                    <p class="text-sm text-red-700 dark:text-red-400">Permanently delete your account</p>
                </div>
            </div>

            <p class="text-sm text-red-700 dark:text-red-300 mb-4">
                Once you delete your account, there is no going back. All your bookings, transactions, and data will be permanently removed.
            </p>

            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="flex items-center gap-2 px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-red-500/25 transform active:scale-[0.98]">
                    <span class="material-icons-round text-lg">delete_forever</span>
                    Delete Account
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
