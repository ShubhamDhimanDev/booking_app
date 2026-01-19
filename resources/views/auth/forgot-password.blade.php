@extends('layouts.auth')

@section('title', 'Forgot Password - MeetFlow')

@section('content')
<div class="mb-10">
    <h2 class="font-display text-4xl font-semibold text-slate-900 dark:text-white mb-2">Forgot Password?</h2>
    <p class="text-slate-500 dark:text-slate-400">No problem. Just let us know your email address and we will email you a password reset link.</p>
</div>

@if(session('status'))
    <div class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300">
        {{ session('status') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('password.email') }}" method="POST" class="space-y-6">
    @csrf
    <div>
        <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2" for="email">Email Address</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                <span class="material-icons-outlined text-xl">mail</span>
            </span>
            <input
                class="block w-full pl-10 pr-4 py-3 border border-slate-200 dark:border-slate-700 rounded-xl bg-white dark:bg-slate-800 focus:ring-2 focus:ring-primary focus:border-transparent transition-all outline-none"
                id="email" name="email" placeholder="name@company.com" required type="email" value="{{ old('email') }}" />
        </div>
    </div>
    <button
        class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary/25 transform active:scale-[0.98]"
        type="submit">
        Send Reset Link
    </button>
</form>

<p class="mt-10 text-center text-slate-500 dark:text-slate-400">
    Remember your password?
    <a class="font-semibold text-primary hover:underline" href="{{ route('login') }}">Back to login</a>
</p>
@endsection
