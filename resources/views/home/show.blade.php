@extends('layouts.app')

@section('title', $region === 'in' ? config('app.name') . ' — India' : config('app.name') . ' — US')

@push('head-scripts')
    <link rel="canonical" href="{{ $region === 'in' ? route('home.in') : route('home.us') }}">
    <link rel="alternate" hreflang="en-in" href="{{ route('home.in') }}">
    <link rel="alternate" hreflang="en-us" href="{{ route('home.us') }}">
@endpush

@section('content')
<div class="max-w-2xl mx-auto px-4 py-12">
    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden">

        @if($event)
            <!-- Hero -->
            <div class="bg-gradient-to-br from-indigo-50 to-primary/10 dark:from-indigo-900/20 dark:to-primary/10 p-12 text-center border-b border-indigo-200 dark:border-indigo-700">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-indigo-100 dark:bg-indigo-900/50 mb-6">
                    <span class="material-icons-round text-primary dark:text-indigo-300" style="font-size: 64px;">event_available</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2">{{ $event->title }}</h1>
                <p class="text-slate-600 dark:text-slate-300">Reserve your spot in just a couple of minutes.</p>
            </div>

            <div class="p-8 text-center">
                <a href="{{ route('events.show.public', $event) }}"
                   class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-indigo-700 hover:opacity-95 text-white font-bold px-8 py-3 rounded-xl transition-all duration-300 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40">
                    <span class="material-icons-round">event</span>
                    Book a Session
                </a>
            </div>
        @else
            <!-- Coming soon -->
            <div class="bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900/40 dark:to-slate-800/40 p-12 text-center border-b border-slate-200 dark:border-slate-700">
                <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-slate-100 dark:bg-slate-700 mb-6">
                    <span class="material-icons-round text-slate-500 dark:text-slate-300" style="font-size: 64px;">schedule</span>
                </div>
                <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-2">Coming Soon</h1>
            </div>

            <div class="p-8 text-center">
                <p class="text-lg text-slate-700 dark:text-slate-300">
                    We're not open for bookings in this region yet — check back soon.
                </p>
            </div>
        @endif

        <!-- Switch region -->
        <div class="pb-8 text-center">
            @if($region === 'in')
                <a href="{{ route('home.us') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary transition-colors">Not in India? View the US site &rarr;</a>
            @else
                <a href="{{ route('home.in') }}" class="text-sm text-slate-500 dark:text-slate-400 hover:text-primary transition-colors">Not in the US? View the India site &rarr;</a>
            @endif
        </div>
    </div>
</div>
@endsection
