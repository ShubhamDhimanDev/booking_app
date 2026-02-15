@extends('layouts.app')

@section('title', 'Payment Failed' . ($booking ? ' - ' . $booking->event->title : ''))

@section('header-icon', 'error_outline')

@section('badge-bg', 'bg-red-50 dark:bg-red-900/30')
@section('badge-border', 'border-red-200 dark:border-red-700')
@section('badge-icon-color', 'text-red-600 dark:text-red-400')
@section('badge-text-color', 'text-red-700 dark:text-red-300')
@section('badge-text', 'Payment Failed')

@section('additional-styles')
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
            20%, 40%, 60%, 80% { transform: translateX(10px); }
        }

        .error-icon {
            animation: shake 0.6s ease;
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

        <!-- Error Banner -->
        <div class="text-center mb-10 fade-in-up">
            <div class="error-icon w-20 h-20 bg-gradient-to-br from-red-500 to-red-600 rounded-full flex items-center justify-center shadow-lg shadow-red-500/30 mx-auto mb-6">
                <span class="material-icons-round text-white text-5xl">close</span>
            </div>
            <h1 class="text-4xl font-extrabold text-slate-900 dark:text-white mb-3 tracking-tight">Payment Failed</h1>
            <p class="text-lg text-slate-600 dark:text-slate-400 font-medium">We couldn't process your payment</p>
        </div>

        @if($booking)
        <!-- Progress Indicator -->
        <div class="mb-10">
            <div class="flex items-center justify-center space-x-2 sm:space-x-4">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-indigo-600 flex items-center justify-center shadow-lg shadow-primary/30">
                        <span class="material-icons-round text-white text-sm">check</span>
                    </div>
                    <span class="ml-2 text-sm font-bold text-primary dark:text-primary hidden sm:inline">Select Time</span>
                </div>
                <div class="w-16 sm:w-32 h-1 bg-gradient-to-r from-primary to-indigo-600 rounded-full"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-indigo-600 flex items-center justify-center shadow-lg shadow-primary/30">
                        <span class="material-icons-round text-white text-sm">check</span>
                    </div>
                    <span class="ml-2 text-sm font-bold text-primary dark:text-primary hidden sm:inline">Details</span>
                </div>
                <div class="w-16 sm:w-32 h-1 bg-slate-300 dark:bg-slate-700 rounded-full"></div>
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center shadow-lg shadow-red-500/30">
                        <span class="material-icons-round text-white text-lg">close</span>
                    </div>
                    <span class="ml-2 text-sm font-bold text-red-600 dark:text-red-400 hidden sm:inline">Payment</span>
                </div>
            </div>
        </div>
        @endif

        <!-- Error Details Card -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 p-8 mb-8 fade-in-up" style="animation-delay: 0.1s;">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/30 rounded-full flex items-center justify-center">
                        <span class="material-icons-round text-red-600 dark:text-red-400 text-2xl">info</span>
                    </div>
                </div>
                <div class="flex-1">
                    <h2 class="text-xl font-bold text-slate-900 dark:text-white mb-2">What happened?</h2>
                    <p class="text-slate-600 dark:text-slate-400 mb-4">
                        {{ $errorMessage }}
                    </p>
                    <div class="bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                        <p class="text-sm text-slate-700 dark:text-slate-300 mb-2"><strong>Common reasons for payment failure:</strong></p>
                        <ul class="text-sm text-slate-600 dark:text-slate-400 space-y-1 list-disc list-inside">
                            <li>Insufficient funds in your account</li>
                            <li>Incorrect card details or expired card</li>
                            <li>Bank declined the transaction</li>
                            <li>Network connectivity issues</li>
                            <li>Payment cancelled by user</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if($booking)
        <!-- Booking Summary -->
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-slate-200 dark:border-slate-700 overflow-hidden mb-8 fade-in-up" style="animation-delay: 0.2s;">
            <div class="bg-gradient-to-r from-primary to-indigo-600 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center">
                    <span class="material-icons-round mr-2">event_note</span>
                    Booking Details
                </h2>
            </div>
            <div class="p-6 space-y-4">
                <div class="flex items-start space-x-3">
                    <span class="material-icons-round text-primary mt-1">event</span>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Event</p>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $booking->event->title }}</p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="material-icons-round text-primary mt-1">schedule</span>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Date & Time</p>
                        <p class="font-semibold text-slate-900 dark:text-white">
                            {{ \Carbon\Carbon::parse($booking->booked_at_date)->format('F j, Y') }} at {{ \Carbon\Carbon::parse($booking->booked_at_time)->format('g:i A') }}
                        </p>
                    </div>
                </div>
                <div class="flex items-start space-x-3">
                    <span class="material-icons-round text-primary mt-1">person</span>
                    <div>
                        <p class="text-sm text-slate-500 dark:text-slate-400">Organizer</p>
                        <p class="font-semibold text-slate-900 dark:text-white">{{ $booking->event->user->name }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 fade-in-up" style="animation-delay: 0.3s;">
            @if($booking)
            <a href="{{ route('payment.page', ['booking' => $booking->id]) }}" class="flex-1 bg-gradient-to-r from-primary to-indigo-600 text-white px-6 py-4 rounded-xl font-bold text-center hover:shadow-xl hover:shadow-primary/30 transition-all duration-300 flex items-center justify-center space-x-2">
                <span class="material-icons-round">refresh</span>
                <span>Retry Payment</span>
            </a>
            @endif
            <a href="{{ route('events.show.public', ['event' => $booking->event->slug ?? '#']) }}" class="flex-1 bg-white dark:bg-slate-800 text-slate-900 dark:text-white border-2 border-slate-300 dark:border-slate-600 px-6 py-4 rounded-xl font-bold text-center hover:bg-slate-50 dark:hover:bg-slate-700 transition-all duration-300 flex items-center justify-center space-x-2">
                <span class="material-icons-round">arrow_back</span>
                <span>Go Back</span>
            </a>
        </div>

        <!-- Support Information -->
        <div class="mt-8 p-6 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 fade-in-up" style="animation-delay: 0.4s;">
            <div class="flex items-start space-x-3">
                <span class="material-icons-round text-primary text-2xl">support_agent</span>
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white mb-1">Need Help?</h3>
                    <p class="text-sm text-slate-600 dark:text-slate-400">
                        If you continue to face issues with payment, please contact our support team. We're here to help!
                    </p>
                </div>
            </div>
        </div>

@endsection
