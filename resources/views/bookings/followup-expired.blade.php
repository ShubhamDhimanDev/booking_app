@extends('layouts.app')

@section('title', 'Follow-up Invitation Expired')

@section('header-icon', 'event_busy')

@section('badge-bg', 'bg-orange-50 dark:bg-orange-900/30')
@section('badge-border', 'border-orange-200 dark:border-orange-700')
@section('badge-icon-color', 'text-orange-600 dark:text-orange-400')
@section('badge-text-color', 'text-orange-700 dark:text-orange-300')
@section('badge-text', 'Invitation Status')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">

        <!-- Icon Header -->
        <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-orange-900/20 dark:to-red-900/20 p-12 text-center border-b border-orange-200 dark:border-orange-700">
            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-orange-100 dark:bg-orange-900/50 mb-6">
                <span class="material-icons-round text-orange-600 dark:text-orange-400" style="font-size: 64px;">event_busy</span>
            </div>
            <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2">Invitation Expired</h1>
        </div>

        <!-- Message Content -->
        <div class="p-8 text-center">
            <p class="text-lg text-slate-700 dark:text-slate-300 mb-8">
                {{ $message ?? 'This follow-up invitation is no longer valid.' }}
            </p>

            <div class="bg-slate-50 dark:bg-slate-700/50 rounded-2xl p-6 mb-8">
                <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-3">What can you do?</h3>
                <ul class="text-left text-slate-700 dark:text-slate-300 space-y-2">
                    <li class="flex items-start gap-2">
                        <span class="material-icons-round text-primary text-sm mt-0.5">check_circle</span>
                        <span>Contact the organizer directly to request a new follow-up invitation</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="material-icons-round text-primary text-sm mt-0.5">check_circle</span>
                        <span>Check your email for any new invitations</span>
                    </li>
                </ul>
            </div>

            <a href="{{ url('/') }}"
               class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-indigo-700 hover:opacity-95 text-white font-bold px-8 py-3 rounded-xl transition-all duration-300 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40">
                <span class="material-icons-round">home</span>
                Return to Home
            </a>
        </div>
    </div>
</div>
@endsection
