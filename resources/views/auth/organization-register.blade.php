@extends('layouts.auth')

@section('title', 'Create Organization - MeetFlow')

@section('content')
<div class="mb-10">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center">
            <span class="material-icons-outlined text-white text-2xl">business_center</span>
        </div>
        <div>
            <h2 class="font-display text-4xl font-semibold text-slate-900 dark:text-white">Create Organization</h2>
        </div>
    </div>
    <p class="text-slate-500 dark:text-slate-400">Start your journey with a <span class="font-semibold text-primary">14-day free trial</span> — no credit card required.</p>
</div>

@if($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('organization.register.store') }}" method="POST" class="space-y-6">
    @csrf

    <!-- Personal Information Section -->
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
            <span class="material-icons-outlined text-primary">person</span>
            Your Information
        </h3>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="name">Full Name</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <span class="material-icons-outlined text-xl">badge</span>
                </span>
                <input
                    class="block w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                    id="name" name="name" placeholder="John Doe" required type="text" value="{{ old('name') }}" />
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="email">Email Address</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <span class="material-icons-outlined text-xl">mail</span>
                </span>
                <input
                    class="block w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                    id="email" name="email" placeholder="john@company.com" required type="email" value="{{ old('email') }}" />
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="phone">Phone Number (Optional)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <span class="material-icons-outlined text-xl">phone</span>
                </span>
                <input
                    class="block w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                    id="phone" name="phone" placeholder="+1 234 567 8900" type="tel" value="{{ old('phone') }}" />
            </div>
        </div>
    </div>

    <div class="border-t border-slate-200 dark:border-slate-700"></div>

    <!-- Organization Information Section -->
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
            <span class="material-icons-outlined text-primary">business</span>
            Organization Details
        </h3>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="organization_name">Organization Name</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <span class="material-icons-outlined text-xl">apartment</span>
                </span>
                <input
                    class="block w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                    id="organization_name" name="organization_name" placeholder="Acme Inc." required type="text" value="{{ old('organization_name') }}" />
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="organization_website">Website (Optional)</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <span class="material-icons-outlined text-xl">language</span>
                </span>
                <input
                    class="block w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                    id="organization_website" name="organization_website" placeholder="https://example.com" type="url" value="{{ old('organization_website') }}" />
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="organization_description">Description (Optional)</label>
            <div class="relative">
                <textarea
                    class="block w-full px-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none resize-none"
                    id="organization_description" name="organization_description" placeholder="Tell us about your organization..." rows="3">{{ old('organization_description') }}</textarea>
            </div>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">This helps us personalize your experience</p>
        </div>
    </div>

    <div class="border-t border-slate-200 dark:border-slate-700"></div>

    <!-- Security Section -->
    <div class="space-y-4">
        <h3 class="text-lg font-semibold text-slate-900 dark:text-white flex items-center gap-2">
            <span class="material-icons-outlined text-primary">lock</span>
            Security
        </h3>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="password">Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <span class="material-icons-outlined text-xl">lock</span>
                </span>
                <input
                    class="block w-full pl-10 pr-12 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                    id="password" name="password" placeholder="••••••••" required type="password" />
                <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                    type="button" onclick="togglePassword('password')">
                    <span class="material-icons-outlined text-xl" id="password-icon">visibility</span>
                </button>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="password_confirmation">Confirm Password</label>
            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <span class="material-icons-outlined text-xl">lock</span>
                </span>
                <input
                    class="block w-full pl-10 pr-12 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                    id="password_confirmation" name="password_confirmation" placeholder="••••••••" required type="password" />
                <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                    type="button" onclick="togglePassword('password_confirmation')">
                    <span class="material-icons-outlined text-xl" id="password_confirmation-icon">visibility</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Trial Information -->
    <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 dark:from-purple-900/20 dark:to-pink-900/20 rounded-xl border border-purple-200 dark:border-purple-800">
        <div class="flex gap-3">
            <div class="flex-shrink-0">
                <span class="material-icons-outlined text-purple-600 dark:text-purple-400">verified</span>
            </div>
            <div class="flex-1 min-w-0">
                <h4 class="font-semibold text-slate-900 dark:text-white mb-1">14-Day Free Trial</h4>
                <ul class="text-sm text-slate-600 dark:text-slate-400 space-y-1">
                    <li class="flex items-center gap-2">
                        <span class="material-icons text-sm text-green-600">check_circle</span>
                        Full access to all features
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="material-icons text-sm text-green-600">check_circle</span>
                        No credit card required
                    </li>
                    <li class="flex items-center gap-2">
                        <span class="material-icons text-sm text-green-600">check_circle</span>
                        Cancel anytime
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <button
        class="w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-purple-500/25 transform active:scale-[0.98]"
        type="submit">
        Create Organization & Start Free Trial
    </button>
</form>

<div class="mt-10 space-y-4">
    <p class="text-center text-slate-500 dark:text-slate-400">
        Already have an account?
        <a class="font-semibold text-primary hover:underline" href="{{ route('login') }}">Sign in</a>
    </p>

    <div class="relative">
        <div class="absolute inset-0 flex items-center">
            <div class="w-full border-t border-slate-200 dark:border-slate-700"></div>
        </div>
        <div class="relative flex justify-center text-sm">
            <span class="px-4 bg-white dark:bg-slate-900 text-slate-500 dark:text-slate-400">or</span>
        </div>
    </div>

    <p class="text-center text-slate-500 dark:text-slate-400">
        Just want to book events?
        <a class="font-semibold text-primary hover:underline" href="{{ route('register') }}">Create a guest account</a>
    </p>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(fieldId) {
    const passwordInput = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '-icon');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.textContent = 'visibility_off';
    } else {
        passwordInput.type = 'password';
        icon.textContent = 'visibility';
    }
}
</script>
@endpush
