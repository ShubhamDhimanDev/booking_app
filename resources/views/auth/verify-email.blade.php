@extends('layouts.auth')

@section('title', 'Verify Email - MeetFlow')

@section('content')
<div class="mb-10">
    <h2 class="font-display text-4xl font-semibold text-slate-900 dark:text-white mb-2">Verify Email</h2>
    <p class="text-slate-500 dark:text-slate-400">Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?</p>
</div>

@if(session('status') === 'verification-link-sent')
    <div class="mb-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-300">
        A new verification link has been sent to your email address.
    </div>
@endif

<div class="space-y-4">
    <form action="{{ route('verification.send') }}" method="POST">
        @csrf
        <button
            class="w-full bg-primary hover:bg-indigo-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-primary/25 transform active:scale-[0.98]"
            type="submit">
            Resend Verification Email
        </button>
    </form>

    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button
            class="w-full border border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold py-3.5 px-4 rounded-xl transition-all"
            type="submit">
            Logout
        </button>
    </form>
</div>

<p class="mt-10 text-center text-slate-500 dark:text-slate-400">
    <a class="font-semibold text-primary hover:underline" href="{{ route('login') }}">Back to login</a>
</p>
@endsection
