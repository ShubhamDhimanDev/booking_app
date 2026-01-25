@extends('layouts.app')

@section('title', $event->user->name . ' | ' . $event->title . ' - Enter Details')

@section('header-icon', 'event_note')

@section('badge-bg', 'bg-blue-50 dark:bg-blue-900/30')
@section('badge-border', 'border-blue-200 dark:border-blue-700')
@section('badge-icon-color', 'text-blue-600 dark:text-blue-400')
@section('badge-text-color', 'text-blue-700 dark:text-blue-300')
@section('badge-text', 'Secure Booking')

@push('head-scripts')
    {!! \App\Services\TrackingService::getEventScript('InitiateCheckout', [
        'content_name' => $event->title,
        'content_ids' => [$event->id],
        'value' => $event->price,
        'currency' => 'INR'
    ]) !!}
    {!! \App\Services\TrackingService::getGoogleEventScript('begin_checkout', [
        'event_name' => $event->title,
        'event_id' => $event->id,
        'value' => $event->price,
        'currency' => 'INR'
    ]) !!}
@endpush

@section('loader')
    <div id="loader" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-white/80 dark:bg-slate-900/80 backdrop-blur-md">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl text-center border border-slate-200 dark:border-slate-700">
            <div class="w-16 h-16 border-4 border-slate-200 dark:border-slate-700 border-t-primary rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-lg font-bold text-slate-900 dark:text-white">Processing your booking...</p>
        </div>
    </div>
@endsection

@section('additional-styles')
    <style>
        /* Progress step animation */
        .progress-step {
            transition: all 0.3s ease;
        }

        .progress-step.active {
            transform: scale(1.05);
        }
    </style>
@endsection

@section('content')

        <!-- Progress Indicator -->
        <div class="mb-10">
            <div class="flex items-center justify-center space-x-2 sm:space-x-4">
                <div class="flex items-center progress-step">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <span class="material-icons-round text-white text-sm">check</span>
                    </div>
                    <span class="ml-2 text-sm font-bold text-emerald-700 dark:text-emerald-400">Select Time</span>
                </div>
                <div class="w-16 sm:w-32 h-1 bg-gradient-to-r from-emerald-500 to-primary rounded-full"></div>
                <div class="flex items-center progress-step active">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-indigo-700 flex items-center justify-center ring-4 ring-primary/20 shadow-lg shadow-primary/40 animate-pulse">
                        <span class="material-icons-round text-white text-lg">badge</span>
                    </div>
                    <span class="ml-2 text-sm font-bold text-slate-900 dark:text-white">Details</span>
                </div>
                <div class="w-16 sm:w-32 h-1 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
                <div class="flex items-center progress-step">
                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                        <span class="material-icons-round text-slate-400 dark:text-slate-500 text-lg">payment</span>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-slate-400 dark:text-slate-500 hidden sm:inline">Payment</span>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8">

            <!-- Left Column - Event Info -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 hover:shadow-xl transition-all duration-300">
                    <div class="flex flex-col space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-indigo-700 text-white text-2xl font-bold flex items-center justify-center ring-4 ring-primary/10 shadow-lg shadow-primary/20">
                                    {{ strtoupper(substr($event->user->name, 0, 1)) }}
                                </div>
                            </div>
                            <div>
                                <h4 class="text-xs font-semibold text-primary uppercase tracking-wider mb-1">{{ $event->user->name }}</h4>
                                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $event->title }}</h2>
                            </div>
                        </div>

                        <div class="inline-flex items-center gap-2 bg-slate-50 dark:bg-slate-700/50 px-4 py-2 rounded-full text-sm font-bold text-slate-700 dark:text-slate-300 w-fit">
                            <span class="material-icons-round text-primary text-lg">schedule</span>
                            <span>{{ $event->duration }} minutes</span>
                        </div>

                        <div class="pt-2">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Description</h3>
                            <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{!! $event->description !!}</div>
                        </div>
                    </div>
                </div>

                <!-- Selected Time Card -->
                <div class="bg-gradient-to-br from-primary/5 via-indigo-50 to-purple-50 dark:from-primary/20 dark:via-slate-700 dark:to-purple-900/20 rounded-2xl p-6 border-2 border-primary/20 dark:border-primary/30 shadow-lg">
                    <h4 class="text-sm font-bold text-primary uppercase tracking-wider mb-3 flex items-center gap-2">
                        <span class="material-icons-round text-lg">schedule</span>
                        Selected Time
                    </h4>
                    <div class="space-y-2">
                        <p class="flex items-center gap-2 text-slate-900 dark:text-white font-bold">
                            <span class="material-icons-round text-primary">calendar_today</span>
                            {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}
                        </p>
                        <p class="flex items-center gap-2 text-slate-900 dark:text-white font-bold">
                            <span class="material-icons-round text-primary">access_time</span>
                            {{ \Carbon\Carbon::parse($time, 'UTC')->format('g:i A') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Column - Details Form -->
            <div class="lg:col-span-3 bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 hover:shadow-xl transition-all duration-300">
            <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <span class="material-icons-round text-primary text-3xl">badge</span>
                Enter Your Details
            </h3>

            @if($errors->any())
                <div class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-800 dark:text-red-300 px-6 py-4 rounded-xl mb-6 shadow-sm">
                    <div class="flex items-start space-x-3">
                        <span class="material-icons-round text-red-500 dark:text-red-400">error</span>
                        <div class="flex-1">
                            <strong class="font-bold block mb-2">Error:</strong>
                            <ul class="list-disc list-inside space-y-1 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('bookings.store', $event->slug) }}" class="space-y-5">
                @csrf
                <input type="hidden" name="booked_at_date" value="{{ $date }}">
                <input type="hidden" name="booked_at_time" value="{{ $time }}">
                @if(request()->has('followup_token'))
                    <input type="hidden" name="followup_token" value="{{ request('followup_token') }}">
                @endif

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round text-primary text-sm">person</span>
                            Full Name *
                        </span>
                    </label>
                    <input
                        type="text"
                        name="booker_name"
                        value="{{ old('booker_name', auth()->user()->name ?? '') }}"
                        placeholder="Enter your full name"
                        required
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round text-primary text-sm">email</span>
                            Email Address *
                        </span>
                    </label>
                    <input
                        type="email"
                        name="booker_email"
                        value="{{ old('booker_email', auth()->user()->email ?? '') }}"
                        placeholder="your@email.com"
                        required
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                    >
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round text-primary text-sm">phone</span>
                            Phone Number
                        </span>
                    </label>
                    <input
                        type="tel"
                        name="phone"
                        value="{{ old('phone', auth()->user()->phone ?? '') }}"
                        placeholder="+91 XXXXXXXXXX"
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                    >
                </div>

                {{-- <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round text-primary text-sm">cake</span>
                            Date of Birth
                        </span>
                    </label>
                    <input
                        type="date"
                        name="dob"
                        value="{{ old('dob', auth()->user()->dob ?? '') }}"
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all"
                    >
                </div> --}}

                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round text-primary text-sm">notes</span>
                            Additional Notes
                        </span>
                    </label>
                    <textarea
                        name="notes"
                        rows="3"
                        placeholder="Any specific agenda or requirements?"
                        class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none"
                    >{{ old('notes') }}</textarea>
                </div>

                <div class="flex flex-col sm:flex-row gap-3 pt-4">
                    <a href="{{ route('events.show.public', $event->slug) }}" class="inline-flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold transition-all shadow-sm hover:shadow-md text-center">
                        <span class="material-icons-round">arrow_back</span>
                        Back
                    </a>
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-6 py-4 rounded-2xl bg-gradient-to-r from-primary to-indigo-700 hover:opacity-95 text-white font-extrabold transition-all duration-300 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40">
                        <span class="material-icons-round">arrow_forward</span>
                        Continue to Payment
                    </button>
                </div>
            </form>
        </div>

        </div>
        <!-- End Two Column Layout -->
@endsection
