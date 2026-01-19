@extends('layouts.booking')

@section('title', 'Booking Confirmed - ' . $booking->event->title)

@section('header-icon', 'event_note')

@section('badge-bg', 'bg-emerald-50 dark:bg-emerald-900/30')
@section('badge-border', 'border-emerald-200 dark:border-emerald-700')
@section('badge-icon-color', 'text-emerald-600 dark:text-emerald-400')
@section('badge-text-color', 'text-emerald-700 dark:text-emerald-300')
@section('badge-text', 'Booking Confirmed')

@push('head-scripts')
    {!! \App\Services\TrackingService::getBaseScript() !!}
    {!! \App\Services\TrackingService::getEventScript('Purchase', [
        'content_name' => $booking->event->title,
        'content_ids' => [$booking->event->id],
        'value' => $booking->payment->amount ?? $booking->event->price ?? 500,
        'currency' => 'INR',
        'transaction_id' => $booking->payment->transaction_id ?? $booking->id
    ]) !!}
@endpush

@section('additional-styles')
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        @keyframes scaleIn {
            from { transform: scale(0); }
            to { transform: scale(1); }
        }

        .success-icon {
            animation: scaleIn 0.5s ease;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease forwards;
        }
    </style>
@endsection

@section('content')

        <!-- Success Banner -->
        <div class="text-center mb-10 fade-in-up">
            <div class="success-icon w-20 h-20 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center shadow-lg shadow-emerald-500/30 mx-auto mb-6">
                <span class="material-icons-round text-white text-5xl">check</span>
            </div>
            <h1 class="text-4xl font-extrabold text-slate-900 dark:text-white mb-3 tracking-tight">Booking Confirmed!</h1>
            <p class="text-lg text-slate-600 dark:text-slate-400 font-medium">Your payment was successful</p>
        </div>

        <!-- Progress Indicator -->
        <div class="mb-10">
            <div class="flex items-center justify-center space-x-2 sm:space-x-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <span class="material-icons-round text-white text-sm">check</span>
                    </div>
                    <span class="ml-2 text-sm font-bold text-emerald-700 dark:text-emerald-400">Select Time</span>
                </div>
                <div class="w-16 sm:w-32 h-1 bg-gradient-to-r from-emerald-500 to-primary rounded-full"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <span class="material-icons-round text-white text-sm">check</span>
                    </div>
                    <span class="ml-2 text-sm font-bold text-emerald-700 dark:text-emerald-400 hidden sm:inline">Details</span>
                </div>
                <div class="w-16 sm:w-32 h-1 bg-gradient-to-r from-primary to-emerald-500 rounded-full"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg shadow-emerald-500/30">
                        <span class="material-icons-round text-white text-lg">check</span>
                    </div>
                    <span class="ml-2 text-sm font-bold text-emerald-700 dark:text-emerald-400 hidden sm:inline">Payment</span>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">

            <!-- Left Column - Booking Details -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 sm:p-8 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <span class="material-icons-round text-primary text-3xl">event_available</span>
                        Booking Details
                    </h3>

                    <!-- Confirmation Email Notice -->
                    <div class="bg-emerald-50 dark:bg-emerald-900/30 border-l-4 border-emerald-500 px-4 py-3 rounded-xl mb-6">
                        <div class="flex items-start space-x-3">
                            <span class="material-icons-round text-emerald-600 dark:text-emerald-400 text-xl">mark_email_read</span>
                            <p class="text-sm text-emerald-800 dark:text-emerald-300 font-medium">
                                Confirmation email sent to <strong>{{ $booking->booker_email }}</strong>
                            </p>
                        </div>
                    </div>

                    <!-- Booker Info -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-2xl p-4 mb-4">
                        <div class="flex items-start space-x-3">
                            <span class="material-icons-round text-slate-600 dark:text-slate-400 text-xl">person</span>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Booked by</p>
                                <p class="text-base font-bold text-slate-900 dark:text-white">{{ $booking->booker_name }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400">{{ $booking->booker_email }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Event Info -->
                    <div class="bg-slate-50 dark:bg-slate-700/50 rounded-2xl p-4 mb-4">
                        <div class="flex items-start space-x-3">
                            <span class="material-icons-round text-slate-600 dark:text-slate-400 text-xl">event_note</span>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Event</p>
                                <p class="text-base font-bold text-slate-900 dark:text-white">{{ $booking->event->title }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">with {{ $booking->event->user->name }}</p>
                                <p class="text-sm text-slate-600 dark:text-slate-400 flex items-center gap-1 mt-1">
                                    <span class="material-icons-round text-sm">schedule</span>
                                    {{ $booking->event->duration }} minutes
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Date & Time -->
                    <div class="bg-gradient-to-br from-primary/5 via-indigo-50 to-purple-50 dark:from-primary/20 dark:via-slate-700 dark:to-purple-900/20 rounded-2xl p-4 mb-4 border-2 border-primary/20 dark:border-primary/30">
                        <div class="flex items-start space-x-3">
                            <span class="material-icons-round text-primary text-xl">calendar_today</span>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-primary uppercase tracking-wider mb-1">Scheduled Time</p>
                                <p class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                    <span class="material-icons-round text-sm">event</span>
                                    {{ \Carbon\Carbon::parse($booking->booked_at_date)->format('l, F j, Y') }}
                                </p>
                                <p class="text-base font-bold text-slate-900 dark:text-white flex items-center gap-2 mt-1">
                                    <span class="material-icons-round text-sm">access_time</span>
                                    {{ \Carbon\Carbon::parse($booking->booked_at_time, 'UTC')->format('g:i A') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Info -->
                    @if($booking->payment)
                        <div class="bg-slate-50 dark:bg-slate-700/50 rounded-2xl p-4">
                            <div class="flex items-start space-x-3">
                                <span class="material-icons-round text-slate-600 dark:text-slate-400 text-xl">payments</span>
                                <div class="flex-1">
                                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Payment</p>
                                    <p class="text-xl font-extrabold text-slate-900 dark:text-white">₹{{ $booking->payment->amount ?? $booking->event->price }}</p>
                                    @if($booking->payment->transaction_id)
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 font-medium">
                                            Transaction ID: {{ $booking->payment->transaction_id }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column - Next Steps -->
            <div class="space-y-6">
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 sm:p-8 hover:shadow-xl transition-all duration-300">
                    <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                        <span class="material-icons-round text-primary text-3xl">rocket_launch</span>
                        Next Steps
                    </h3>

                    <div class="space-y-3">
                        @if($booking->meet_link)
                            <a href="{{ $booking->meet_link }}" target="_blank" class="flex items-center justify-center gap-2 w-full px-6 py-4 rounded-2xl bg-gradient-to-r from-emerald-500 to-green-600 hover:opacity-95 text-white font-extrabold transition-all duration-300 shadow-lg shadow-emerald-500/30 hover:shadow-xl hover:shadow-emerald-500/40">
                                <span class="material-icons-round text-xl">videocam</span>
                                Join Google Meet
                            </a>
                        @endif

                        @if($booking->calendar_link)
                            <a href="{{ $booking->calendar_link }}" target="_blank" class="flex items-center justify-center gap-2 w-full px-6 py-4 rounded-2xl bg-gradient-to-r from-primary to-indigo-700 hover:opacity-95 text-white font-extrabold transition-all duration-300 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40">
                                <span class="material-icons-round text-xl">event</span>
                                Add to Google Calendar
                            </a>
                        @endif

                        <a href="/e/{{ $booking->event->slug }}" class="flex items-center justify-center gap-2 w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold transition-all shadow-sm hover:shadow-md">
                            <span class="material-icons-round text-xl">arrow_back</span>
                            Back to Event Page
                        </a>

                        @auth
                            <a href="{{ route('user.bookings.index') }}" class="flex items-center justify-center gap-2 w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold transition-all shadow-sm hover:shadow-md">
                                <span class="material-icons-round text-xl">list_alt</span>
                                View My Bookings
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 w-full px-6 py-4 rounded-2xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-300 font-bold transition-all shadow-sm hover:shadow-md">
                                <span class="material-icons-round text-xl">login</span>
                                Login to Manage Bookings
                            </a>
                        @endauth
                    </div>

                    <!-- Tip Box -->
                    <div class="mt-6 bg-amber-50 dark:bg-amber-900/30 border-l-4 border-amber-500 px-4 py-4 rounded-xl">
                        <div class="flex items-start space-x-3">
                            <span class="material-icons-round text-amber-600 dark:text-amber-400 text-xl">lightbulb</span>
                            <p class="text-sm text-amber-800 dark:text-amber-300 font-medium leading-relaxed">
                                <strong>Tip:</strong> Add this event to your calendar to avoid missing it. You'll receive a reminder email 24 hours before the meeting.
                            </p>
                        </div>
                    </div>

                    <!-- Reschedule Info -->
                    <div class="mt-6 pt-6 border-t border-slate-200 dark:border-slate-700">
                        <p class="text-sm text-slate-600 dark:text-slate-400 text-center">
                            Need to reschedule?
                            @auth
                                <a href="{{ route('user.bookings.index') }}" class="text-primary hover:text-indigo-700 dark:hover:text-indigo-400 font-bold ml-1 transition-colors">Go to My Bookings</a>
                            @else
                                <a href="{{ route('login') }}" class="text-primary hover:text-indigo-700 dark:hover:text-indigo-400 font-bold ml-1 transition-colors">Login to your account</a>
                            @endauth
                        </p>
                    </div>
                </div>
            </div>

        </div>
@endsection
