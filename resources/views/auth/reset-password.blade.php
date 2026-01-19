@extends('layouts.auth')

@section('title', 'Reset Password - MeetFlow')

@section('content')
<div class="mb-10">
    <h2 class="font-display text-4xl font-semibold text-slate-900 dark:text-white mb-2">Reset Password</h2>
    <p class="text-slate-500 dark:text-slate-400">Enter your new password below.</p>
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

<form action="{{ route('password.update') }}" method="POST" class="space-y-6">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">
    <input type="hidden" name="email" value="{{ $request->email }}">
    
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="password">New Password</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <span class="material-icons-outlined text-xl">lock</span>
            </span>
            <input
                class="block w-full pl-10 pr-12 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                id="password" name="password" placeholder="" required type="password" />
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
                id="password_confirmation" name="password_confirmation" placeholder="" required type="password" />
            <button class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-200"
                type="button" onclick="togglePassword('password_confirmation')">
                <span class="material-icons-outlined text-xl" id="password_confirmation-icon">visibility</span>
            </button>
        </div>
    </div>
    <button
        class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary/25 transform active:scale-[0.98]"
        type="submit">
        Reset Password
    </button>
</form>
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
