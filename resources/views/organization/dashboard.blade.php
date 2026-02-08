@extends('layouts.organization')

@section('title', 'Dashboard - ' . config('app.name'))

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Welcome back, {{ auth()->user()->name }}!</p>
    </div>

    <!-- Stats Cards -->
    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Events</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_events'] ?? 0 }}</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-900/30">
                    <svg class="h-6 w-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Bookings</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_bookings'] ?? 0 }}</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-50 dark:bg-green-900/30">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Revenue</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">₹{{ number_format($stats['revenue'] ?? 0) }}</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-50 dark:bg-purple-900/30">
                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Team Members</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['team_members'] ?? 0 }}</h3>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-50 dark:bg-orange-900/30">
                    <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="col-span-12 xl:col-span-8">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Bookings</h3>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-700 dark:border-gray-800 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Event</th>
                                <th scope="col" class="px-6 py-3">Client</th>
                                <th scope="col" class="px-6 py-3">Date</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                                <th scope="col" class="px-6 py-3">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings ?? [] as $booking)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $booking->event->title }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $booking->name }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $booking->booked_at_date->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                        @if($booking->status === 'confirmed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                        @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                        @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                        @endif">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">₹{{ number_format($booking->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No bookings yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Status -->
    <div class="col-span-12 xl:col-span-4">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Subscription</h3>
            </div>
            <div class="p-6">
                @if($subscription ?? null)
                    <div class="text-center">
                        <div class="inline-flex h-16 w-16 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-900/30">
                            <svg class="h-8 w-8 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        <h4 class="mt-4 text-xl font-bold text-gray-900 dark:text-white">{{ $subscription->plan->name }}</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ ucfirst($subscription->billing_cycle) }}</p>
                        <div class="mt-4 text-3xl font-bold text-gray-900 dark:text-white">₹{{ number_format($subscription->amount) }}</div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">per {{ $subscription->billing_cycle === 'monthly' ? 'month' : 'year' }}</p>

                        <div class="mt-6 space-y-2 text-left">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ ucfirst($subscription->status) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500 dark:text-gray-400">Next billing:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ $subscription->current_period_end->format('M d, Y') }}</span>
                            </div>
                        </div>

                        <a href="{{ route('organization.subscription') }}" class="mt-6 inline-block w-full rounded-lg bg-brand-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                            Manage Subscription
                        </a>
                    </div>
                @else
                    <div class="text-center">
                        <p class="text-gray-500 dark:text-gray-400">No active subscription</p>
                        <a href="{{ route('organization.subscription') }}" class="mt-4 inline-block rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                            Choose a Plan
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
