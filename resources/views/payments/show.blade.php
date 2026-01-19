@extends('layouts.app')

@section('title', 'Complete Payment - ' . config('app.name'))

@section('header-icon', 'event')

@section('badge-bg', 'bg-emerald-50 dark:bg-emerald-900/30')
@section('badge-border', 'border-emerald-200 dark:border-emerald-700')
@section('badge-icon-color', 'text-emerald-600 dark:text-emerald-400')
@section('badge-text-color', 'text-emerald-700 dark:text-emerald-300')
@section('badge-text', 'Secure Checkout')

@push('head-scripts')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {!! \App\Services\TrackingService::getEventScript('AddPaymentInfo', [
        'content_name' => $booking->event->title,
        'content_ids' => [$booking->event->id],
        'value' => $booking->event->price ?? 500,
        'currency' => 'INR'
    ]) !!}
@endpush

@section('additional-styles')
    <style>
        * {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .gradient-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            animation: shimmer 3s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .amount-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: pulse-slow 3s ease-in-out infinite;
        }

        .verifying-overlay {
            backdrop-filter: blur(12px);
            background: rgba(255, 255, 255, 0.95);
        }

        .spinner {
            border: 4px solid #e5e7eb;
            border-top: 4px solid #6366f1;
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .trust-badge {
            animation: fadeIn 0.5s ease-in forwards;
            opacity: 0;
            transform: translateY(20px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .payment-card {
            box-shadow: 0 20px 60px rgba(99, 102, 241, 0.15);
            transition: all 0.3s ease;
        }

        .payment-card:hover {
            box-shadow: 0 25px 80px rgba(99, 102, 241, 0.25);
            transform: translateY(-2px);
        }

        @keyframes slideIn {
            from {
                transform: translateX(100px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .error-toast {
            animation: slideIn 0.3s ease-out;
        }

        .progress-step {
            transition: all 0.3s ease;
        }

        .progress-step.active {
            transform: scale(1.1);
        }

        .booking-detail-card {
            transition: all 0.3s ease;
        }

        .booking-detail-card:hover {
            transform: translateX(4px);
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
                    <span class="ml-2 text-sm font-bold text-emerald-700">Details</span>
                </div>
                <div class="w-16 sm:w-32 h-1 bg-gradient-to-r from-emerald-500 to-primary rounded-full"></div>
                <div class="flex items-center progress-step active">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-indigo-700 flex items-center justify-center ring-4 ring-primary/20 shadow-lg shadow-primary/40 animate-pulse">
                        <span class="material-icons-round text-white text-lg">payment</span>
                    </div>
                    <span class="ml-2 text-sm font-bold text-slate-900 dark:text-white">Payment</span>
                </div>
                <div class="w-16 sm:w-32 h-1 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
                <div class="flex items-center progress-step">
                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                        <span class="material-icons-round text-slate-400 dark:text-slate-500 text-sm">done_all</span>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-slate-400 dark:text-slate-500 hidden sm:inline">Confirmation</span>
                </div>
            </div>
        </div>

        <!-- Host Info Banner -->
        <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 sm:p-8 mb-8 hover:shadow-xl transition-all duration-300">
            <div class="flex items-center space-x-4 sm:space-x-6">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-br from-primary to-indigo-700 text-white text-2xl font-bold flex items-center justify-center ring-4 ring-primary/10 shadow-lg shadow-primary/20">
                        @if($booking->event->user->avatar)
                            <img src="{{ asset($booking->event->user->avatar) }}" alt="{{ $booking->event->user->name }}" class="w-full h-full rounded-2xl object-cover">
                        @else
                            {{ substr($booking->event->user->name, 0, 1) }}
                        @endif
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $booking->event->title }}</h2>
                    <p class="text-slate-600 dark:text-slate-400 mt-1.5 flex items-center gap-2">
                        <span class="material-icons-round text-primary text-sm">person</span>
                        <span class="font-medium">{{ $booking->event->user->name }}</span>
                    </p>
                </div>
                <div class="hidden sm:flex flex-col items-end space-y-1 bg-slate-50 dark:bg-slate-700/50 px-4 py-3 rounded-2xl">
                    <span class="material-icons-round text-primary text-2xl">schedule</span>
                    <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $booking->event->duration }} min</span>
                </div>
            </div>
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
            <!-- Left Column: Booking Details (3 columns) -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Booking Details Section -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 sm:p-8 hover:shadow-xl transition-all duration-300">
                    <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center">
                        <span class="material-icons-round text-primary mr-2 text-2xl">event_note</span>
                        Booking Summary
                    </h2>

                    <!-- Date & Time - Most Important -->
                    <div class="bg-gradient-to-br from-primary/10 via-indigo-50 to-purple-50 dark:from-primary/20 dark:via-slate-700 dark:to-purple-900/20 rounded-2xl p-6 mb-6 border-2 border-primary/20 dark:border-primary/30 shadow-lg shadow-primary/5 booking-detail-card">
                        <div class="flex items-start space-x-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-indigo-700 flex items-center justify-center flex-shrink-0 shadow-lg shadow-primary/30">
                                <span class="material-icons-round text-white text-2xl">calendar_today</span>
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-bold text-primary dark:text-primary uppercase tracking-wider mb-2">Scheduled for</p>
                                <p class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ \Carbon\Carbon::parse($booking->booked_at_date)->format('l, F j, Y') }}</p>
                                <div class="flex items-center mt-3 space-x-2 bg-white/70 dark:bg-slate-800/70 px-3 py-2 rounded-xl inline-flex">
                                    <span class="material-icons-round text-primary text-lg">access_time</span>
                                    <p class="text-lg font-bold text-slate-900 dark:text-white">{{ \Carbon\Carbon::parse($booking->booked_at_time, 'UTC')->format('g:i A') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- User Information -->
                    <div class="border-t border-slate-100 dark:border-slate-700 pt-6">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-4 flex items-center">
                            <span class="material-icons-round text-primary text-sm mr-1">badge</span>
                            Your Contact Details
                        </h3>
                        <div class="space-y-4">
                            <div class="flex items-center space-x-4 booking-detail-card bg-slate-50 dark:bg-slate-700/50 p-3 rounded-xl">
                                <div class="w-10 h-10 rounded-xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center">
                                    <span class="material-icons-round text-primary text-lg">person</span>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Name</p>
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $booking->booker_name }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4 booking-detail-card bg-slate-50 dark:bg-slate-700/50 p-3 rounded-xl">
                                <div class="w-10 h-10 rounded-xl bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center">
                                    <span class="material-icons-round text-blue-600 dark:text-blue-400 text-lg">email</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Email</p>
                                    <p class="font-bold text-slate-900 dark:text-white truncate">{{ $booking->booker_email }}</p>
                                </div>
                            </div>
                            @if($booking->booker && $booking->booker->phone)
                            <div class="flex items-center space-x-4 booking-detail-card bg-slate-50 dark:bg-slate-700/50 p-3 rounded-xl">
                                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center">
                                    <span class="material-icons-round text-emerald-600 dark:text-emerald-400 text-lg">phone</span>
                                </div>
                                <div>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase tracking-wider">Phone</p>
                                    <p class="font-bold text-slate-900 dark:text-white">{{ $booking->booker->phone }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    @if($booking->event->description)
                    <div class="mt-6 pt-6 border-t border-slate-100 dark:border-slate-700">
                        <h3 class="text-sm font-bold text-slate-700 dark:text-slate-300 mb-3 flex items-center">
                            <span class="material-icons-round text-primary text-sm mr-1">info</span>
                            About this event
                        </h3>
                        <div class="bg-gradient-to-br from-slate-50 to-blue-50 dark:from-slate-700/50 dark:to-slate-600/50 p-4 rounded-2xl border border-slate-100 dark:border-slate-600">
                            <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed">{!! nl2br(e($booking->event->description)) !!}</p>
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Trust Badges -->
                <div class="grid grid-cols-3 gap-3 sm:gap-4">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-emerald-900/30 dark:to-green-900/30 rounded-2xl p-4 text-center shadow-sm border border-green-100 dark:border-emerald-700/50 trust-badge hover:shadow-md transition-all cursor-default group">
                        <span class="material-icons-round text-emerald-600 dark:text-emerald-400 text-3xl mb-2 group-hover:scale-110 transition-transform">verified_user</span>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Secure Payment</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/30 dark:to-cyan-900/30 rounded-2xl p-4 text-center shadow-sm border border-blue-100 dark:border-blue-700/50 trust-badge hover:shadow-md transition-all cursor-default group" style="animation-delay: 0.1s">
                        <span class="material-icons-round text-blue-600 dark:text-blue-400 text-3xl mb-2 group-hover:scale-110 transition-transform">support_agent</span>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">24/7 Support</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/30 dark:to-indigo-900/30 rounded-2xl p-4 text-center shadow-sm border border-purple-100 dark:border-purple-700/50 trust-badge hover:shadow-md transition-all cursor-default group" style="animation-delay: 0.2s">
                        <span class="material-icons-round text-purple-600 dark:text-purple-400 text-3xl mb-2 group-hover:scale-110 transition-transform">event_available</span>
                        <p class="text-xs font-bold text-slate-700 dark:text-slate-300">Instant Confirm</p>
                    </div>
                </div>
            </div>

            <!-- Right Column: Payment Card (2 columns) -->
            <div class="lg:col-span-2">
                <div class="lg:sticky lg:top-24">
                    <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-2xl border border-slate-100 dark:border-slate-700 p-6 sm:p-8 payment-card">
                        <h2 class="text-xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center">
                            <span class="material-icons-round text-emerald-600 dark:text-emerald-400 mr-2 text-2xl">payments</span>
                            Payment
                        </h2>

                        <!-- Amount Display -->
                        <div class="bg-gradient-to-br from-primary/5 via-indigo-50 to-purple-50 dark:from-primary/20 dark:via-slate-700 dark:to-purple-900/20 rounded-2xl p-8 mb-6 text-center border-2 border-primary/20 dark:border-primary/30 shadow-inner">
                            <p class="text-xs font-bold text-primary dark:text-primary uppercase tracking-wider mb-3">Total Amount</p>

                            <!-- Original Price (shown when discount applied) -->
                            <div id="originalPriceSection" class="hidden mb-2">
                                <p class="text-2xl font-bold text-slate-400 dark:text-slate-500 line-through">₹<span id="originalPrice">{{ $booking->event->price ?? 500 }}</span></p>
                            </div>

                            <!-- Final Price -->
                            <h3 class="text-5xl font-black amount-display mb-2">₹<span id="finalAmount">{{ $booking->event->price ?? 500 }}</span></h3>

                            <!-- Discount Badge -->
                            <div id="discountBadge" class="hidden mb-3">
                                <span class="inline-flex items-center gap-1 bg-emerald-500 text-white px-3 py-1 rounded-full text-sm font-bold">
                                    <span class="material-icons-round text-sm">local_offer</span>
                                    <span id="discountText">Saved ₹0</span>
                                </span>
                            </div>

                            <p class="text-xs text-slate-600 dark:text-slate-400 font-medium bg-white/60 dark:bg-slate-800/60 px-3 py-1 rounded-full inline-block">Inclusive of all taxes</p>
                        </div>

                        <!-- Promo Code Section -->
                        <div class="mb-6">
                            <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-2xl p-5 border border-amber-100 dark:border-amber-700">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center">
                                        <span class="material-icons-round text-amber-600 dark:text-amber-400 mr-2 text-lg">discount</span>
                                        Have a promo code?
                                    </h3>
                                </div>

                                <div class="flex gap-2">
                                    <div class="flex-1 relative">
                                        <input
                                            type="text"
                                            id="promoCode"
                                            placeholder="Enter code"
                                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white font-semibold uppercase placeholder-slate-400 dark:placeholder-slate-500 focus:border-primary focus:ring-2 focus:ring-primary/20 outline-none transition-all"
                                            maxlength="20"
                                        >
                                        <span class="material-icons-round absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500 text-lg">local_offer</span>
                                    </div>
                                    <button
                                        id="applyPromoBtn"
                                        class="px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white font-bold rounded-xl transition-all shadow-lg shadow-amber-500/30 hover:shadow-xl hover:shadow-amber-500/40 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2"
                                    >
                                        <span id="applyBtnText">Apply</span>
                                        <span id="applyBtnSpinner" class="hidden">
                                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </span>
                                    </button>
                                </div>

                                <!-- Success Message -->
                                <div id="promoSuccess" class="hidden mt-3 flex items-start gap-2 bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-700 rounded-lg p-3">
                                    <span class="material-icons-round text-emerald-600 dark:text-emerald-400 text-lg">check_circle</span>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300" id="promoSuccessText"></p>
                                        <button id="removePromoBtn" class="text-xs text-emerald-600 dark:text-emerald-400 hover:underline mt-1">Remove code</button>
                                    </div>
                                </div>

                                <!-- Error Message -->
                                <div id="promoError" class="hidden mt-3 flex items-start gap-2 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 rounded-lg p-3">
                                    <span class="material-icons-round text-red-600 dark:text-red-400 text-lg">error</span>
                                    <p class="text-sm font-semibold text-red-700 dark:text-red-300" id="promoErrorText"></p>
                                </div>
                            </div>
                        </div>

                        @if($booking->status == 'confirmed')
                            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6 flex items-center space-x-3">
                                <span class="material-icons-round text-green-600">check_circle</span>
                                <p class="text-sm font-medium text-green-800">Payment completed successfully!</p>
                            </div>
                        @else
                            <!-- Pay Button -->
                            <button id="payBtn" class="w-full gradient-bg hover:opacity-95 text-white font-extrabold py-5 rounded-2xl transition-all duration-300 flex items-center justify-center space-x-3 mb-5 shadow-2xl shadow-primary/40 hover:shadow-3xl hover:shadow-primary/50 transform hover:-translate-y-1 hover:scale-[1.02] relative overflow-hidden group">
                                <span class="material-icons-round text-xl">lock</span>
                                <span class="text-xl">Pay ₹<span id="payBtnAmount">{{ $booking->event->price ?? 500 }}</span></span>
                            </button>

                            <!-- Security Badge -->
                            <div class="flex items-center justify-center space-x-2 bg-emerald-50 dark:bg-emerald-900/30 px-4 py-3 rounded-xl mb-6 border border-emerald-100 dark:border-emerald-700">
                                <span class="material-icons-round text-lg text-emerald-600 dark:text-emerald-400">verified</span>
                                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">
                                    Powered by
                                    @php
                                        $activeGateway = app(\App\Services\PaymentGatewayManager::class)->getActiveGateway()->getName();
                                    @endphp
                                    {{ ucfirst($activeGateway) }}
                                </p>
                            </div>
                        @endif

                        <!-- What Happens Next -->
                        <div class="bg-gradient-to-br from-slate-50 to-blue-50 dark:from-slate-700/50 dark:to-slate-600/50 rounded-2xl p-5 border border-slate-100 dark:border-slate-600">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center mb-4">
                                <span class="material-icons-round text-primary mr-2">tips_and_updates</span>
                                What happens next?
                            </h3>
                            <div class="space-y-3">
                                <div class="flex items-start space-x-3 booking-detail-card">
                                    <div class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                                        <span class="material-icons-round text-emerald-600 dark:text-emerald-400 text-lg">check_circle</span>
                                    </div>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 font-medium pt-1">Instant email confirmation</p>
                                </div>
                                <div class="flex items-start space-x-3 booking-detail-card">
                                    <div class="w-8 h-8 rounded-xl bg-blue-500/10 dark:bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                                        <span class="material-icons-round text-blue-600 dark:text-blue-400 text-lg">link</span>
                                    </div>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 font-medium pt-1">Meeting link sent before event</p>
                                </div>
                                <div class="flex items-start space-x-3 booking-detail-card">
                                    <div class="w-8 h-8 rounded-xl bg-purple-500/10 dark:bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                                        <span class="material-icons-round text-purple-600 dark:text-purple-400 text-lg">notifications_active</span>
                                    </div>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 font-medium pt-1">Automated reminders</p>
                                </div>
                                <div class="flex items-start space-x-3 booking-detail-card">
                                    <div class="w-8 h-8 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                                        <span class="material-icons-round text-amber-600 dark:text-amber-400 text-lg">sync</span>
                                    </div>
                                    <p class="text-sm text-slate-700 dark:text-slate-300 font-medium pt-1">Free reschedule available</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="mt-12 text-center">
            <p class="text-xs text-slate-500 dark:text-slate-400">
                By completing this payment, you agree to our
                <a href="#" class="text-primary hover:underline">Terms of Service</a> and
                <a href="#" class="text-primary hover:underline">Privacy Policy</a>
            </p>
        </div>
    </main>
@endsection

@push('scripts')
    <!-- Error Message Toast -->
    <div id="errorBox" class="hidden fixed top-4 right-4 bg-white dark:bg-slate-800 border-l-4 border-red-500 shadow-xl px-6 py-4 rounded-lg max-w-md z-50 error-toast">
        <div class="flex items-start space-x-3">
            <span class="material-icons-round text-red-500 dark:text-red-400 text-xl">error</span>
            <div class="flex-1">
                <p class="font-semibold text-slate-900 dark:text-white mb-1">Payment Failed</p>
                <p id="errorMessage" class="text-sm text-slate-600 dark:text-slate-400"></p>
            </div>
            <button onclick="document.getElementById('errorBox').classList.add('hidden')" class="text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300">
                <span class="material-icons-round text-sm">close</span>
            </button>
        </div>
    </div>

    <!-- Verifying Overlay -->
    <div id="verifyingOverlay" class="hidden fixed inset-0 z-50 flex items-center justify-center verifying-overlay">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-12 shadow-2xl text-center max-w-md mx-4 border-2 border-slate-200 dark:border-slate-700">
            <div class="spinner mx-auto mb-8"></div>
            <h3 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-4 tracking-tight">Verifying Payment</h3>
            <p class="text-base text-slate-600 dark:text-slate-400 mb-3 font-medium">Please wait while we confirm your transaction...</p>
            <div class="flex items-center justify-center space-x-2 mt-6 bg-emerald-50 dark:bg-emerald-900/30 px-4 py-3 rounded-xl border border-emerald-100 dark:border-emerald-700">
                <span class="material-icons-round text-emerald-600 dark:text-emerald-400">lock</span>
                <p class="text-sm font-bold text-emerald-700 dark:text-emerald-300">Secure payment processing</p>
            </div>
            <div class="flex items-center justify-center space-x-2 mt-6 bg-amber-50 dark:bg-amber-900/30 px-4 py-3 rounded-xl border border-amber-200 dark:border-amber-700">
                <span class="material-icons-round text-amber-600 dark:text-amber-400 text-lg">warning</span>
                <p class="text-sm text-amber-800 dark:text-amber-300 font-bold">Do not close or refresh this page</p>
            </div>
        </div>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://secure.payu.in/_payment_options_v2.js"></script>
    <script>
    const payBtn = document.getElementById('payBtn');
    const errorBox = document.getElementById('errorBox');
    const errorMessage = document.getElementById('errorMessage');
    const verifyingOverlay = document.getElementById('verifyingOverlay');
    const bookingId = {{ $booking->id }};

    // Promo code state
    let appliedPromoCode = null;
    let originalAmount = {{ $booking->event->price ?? 500 }};
    let discountedAmount = originalAmount;
    let discountValue = 0;

    // Promo Code Elements
    const promoCodeInput = document.getElementById('promoCode');
    const applyPromoBtn = document.getElementById('applyPromoBtn');
    const removePromoBtn = document.getElementById('removePromoBtn');
    const promoSuccess = document.getElementById('promoSuccess');
    const promoError = document.getElementById('promoError');
    const promoSuccessText = document.getElementById('promoSuccessText');
    const promoErrorText = document.getElementById('promoErrorText');
    const applyBtnText = document.getElementById('applyBtnText');
    const applyBtnSpinner = document.getElementById('applyBtnSpinner');

    // Amount display elements
    const originalPriceSection = document.getElementById('originalPriceSection');
    const originalPriceEl = document.getElementById('originalPrice');
    const finalAmountEl = document.getElementById('finalAmount');
    const payBtnAmountEl = document.getElementById('payBtnAmount');
    const discountBadge = document.getElementById('discountBadge');
    const discountText = document.getElementById('discountText');

    // Apply Promo Code
    if (applyPromoBtn) {
        applyPromoBtn.addEventListener('click', async function() {
            const code = promoCodeInput.value.trim().toUpperCase();

            if (!code) {
                showPromoError('Please enter a promo code');
                return;
            }

            // Reset messages
            promoSuccess.classList.add('hidden');
            promoError.classList.add('hidden');

            // Show loading state
            applyPromoBtn.disabled = true;
            applyBtnText.classList.add('hidden');
            applyBtnSpinner.classList.remove('hidden');

            try {
                const response = await fetch('/validate-promo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        promo_code: code,
                        booking_id: bookingId,
                        amount: originalAmount
                    })
                });

                const data = await response.json();

                if (data.success) {
                    appliedPromoCode = code;
                    discountedAmount = data.discounted_amount;
                    discountValue = data.discount_value;

                    updatePriceDisplay();
                    showPromoSuccess(data.message);
                    promoCodeInput.disabled = true;
                } else {
                    showPromoError(data.message || 'Invalid promo code');
                }
            } catch (error) {
                showPromoError('Failed to validate promo code. Please try again.');
            } finally {
                applyPromoBtn.disabled = false;
                applyBtnText.classList.remove('hidden');
                applyBtnSpinner.classList.add('hidden');
            }
        });
    }

    // Remove Promo Code
    if (removePromoBtn) {
        removePromoBtn.addEventListener('click', function() {
            appliedPromoCode = null;
            discountedAmount = originalAmount;
            discountValue = 0;

            promoCodeInput.value = '';
            promoCodeInput.disabled = false;
            promoSuccess.classList.add('hidden');
            promoError.classList.add('hidden');

            updatePriceDisplay();
        });
    }

    // Enter key support for promo code
    if (promoCodeInput) {
        promoCodeInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                applyPromoBtn.click();
            }
        });
    }

    function updatePriceDisplay() {
        if (discountValue > 0) {
            // Show original price with strikethrough
            originalPriceSection.classList.remove('hidden');
            originalPriceEl.textContent = originalAmount;

            // Update final amount
            finalAmountEl.textContent = discountedAmount;
            payBtnAmountEl.textContent = discountedAmount;

            // Show discount badge
            discountBadge.classList.remove('hidden');
            discountText.textContent = `Saved ₹${discountValue}`;
        } else {
            // Hide discount UI
            originalPriceSection.classList.add('hidden');
            discountBadge.classList.add('hidden');

            // Reset to original amount
            finalAmountEl.textContent = originalAmount;
            payBtnAmountEl.textContent = originalAmount;
        }
    }

    function showPromoSuccess(message) {
        promoSuccess.classList.remove('hidden');
        promoSuccessText.textContent = message;
        promoError.classList.add('hidden');
    }

    function showPromoError(message) {
        promoError.classList.remove('hidden');
        promoErrorText.textContent = message;
        promoSuccess.classList.add('hidden');
    }

    if (payBtn) {
        payBtn.addEventListener('click', async function () {
            payBtn.disabled = true;
            errorBox.classList.add('hidden');

            try {
                const requestBody = {
                    amount: discountedAmount,
                    booking_id: bookingId,
                    product_info: '{{ addslashes($booking->event->title) }}',
                    first_name: '{{ addslashes($booking->booker_name) }}',
                    email: '{{ $booking->booker_email }}'
                };

                // Include promo code if applied
                if (appliedPromoCode) {
                    requestBody.promo_code = appliedPromoCode;
                }

                const response = await fetch('/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(requestBody)
                });

                const data = await response.json();

                if (data.gateway === 'payu') {
                    handlePayUPayment(data);
                } else {
                    handleRazorpayPayment(data);
                }

            } catch (error) {
                errorMessage.textContent = error.message || 'Payment failed. Please try again.';
                errorBox.classList.remove('hidden');
                payBtn.disabled = false;
            }
        });
    }

    // Razorpay Handler
    function handleRazorpayPayment(data) {
        const options = {
            key: data.key,
            amount: discountedAmount * 100,
            currency: 'INR',
            name: '{{ addslashes($booking->event->title) }}',
            description: 'Booking #' + bookingId,
            order_id: data.order_id,
            prefill: {
                name: '{{ addslashes($booking->booker_name) }}',
                email: '{{ $booking->booker_email }}'
            },
            handler: async function (response) {
                verifyingOverlay.classList.remove('hidden');

                try {
                    const verifyBody = {
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature,
                        booking_id: bookingId,
                        amount: discountedAmount
                    };

                    // Include promo code if applied
                    if (appliedPromoCode) {
                        verifyBody.promo_code = appliedPromoCode;
                    }

                    const verifyResponse = await fetch('/verify-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify(verifyBody)
                    });

                    const result = await verifyResponse.json();

                    if (result.success) {
                        window.location.href = `/payment/thankyou/${bookingId}`;
                    } else {
                        throw new Error(result.message || 'Payment verification failed');
                    }
                } catch (error) {
                    verifyingOverlay.classList.add('hidden');
                    errorMessage.textContent = error.message;
                    errorBox.classList.remove('hidden');
                    payBtn.disabled = false;
                }
            },
            modal: {
                ondismiss: function() {
                    payBtn.disabled = false;
                }
            },
            theme: {
                color: '#6366f1'
            }
        };

        const rzp = new Razorpay(options);
        rzp.open();
    }

    // PayU Handler
    function handlePayUPayment(data) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = data.payu_url || 'https://secure.payu.in/_payment';

        for (const key in data) {
            if (key !== 'gateway' && key !== 'payu_url' && key !== 'success' && data[key] !== null) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key];
                form.appendChild(input);
            }
        }

        const bookingInput = document.createElement('input');
        bookingInput.type = 'hidden';
        bookingInput.name = 'udf1';
        bookingInput.value = bookingId;
        form.appendChild(bookingInput);

        // Add promo code if applied
        if (appliedPromoCode) {
            const promoInput = document.createElement('input');
            promoInput.type = 'hidden';
            promoInput.name = 'udf2';
            promoInput.value = appliedPromoCode;
            form.appendChild(promoInput);
        }

        document.body.appendChild(form);
        form.submit();
    }

    // Track page view
    if (typeof fbq === 'function') {
        fbq('trackCustom', 'ViewPaymentPage', {
            booking_id: bookingId,
            amount: originalAmount
        });
    }
    </script>
@endpush
