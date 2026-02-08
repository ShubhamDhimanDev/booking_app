@extends('layouts.organization')

@section('title', 'Bookings - ' . config('app.name'))

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Bookings</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and manage all bookings</p>
            </div>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="col-span-12 sm:col-span-6 lg:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Bookings</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 lg:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Confirmed</p>
            <h3 class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['confirmed'] ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 lg:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending</p>
            <h3 class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 lg:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Cancelled</p>
            <h3 class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['cancelled'] ?? 0 }}</h3>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" action="{{ route('organization.bookings.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Email or name..." class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label for="event" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Event</label>
                    <select id="event" name="event" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">All Events</option>
                        @foreach($events ?? [] as $event)
                        <option value="{{ $event->id }}" {{ request('event') == $event->id ? 'selected' : '' }}>{{ $event->title }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">All Status</option>
                        <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-lg bg-gray-700 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings List -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-4">Client</th>
                            <th scope="col" class="px-6 py-4">Event</th>
                            <th scope="col" class="px-6 py-4">Date & Time</th>
                            <th scope="col" class="px-6 py-4">Amount</th>
                            <th scope="col" class="px-6 py-4">Payment</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $booking)
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $booking->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->email }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $booking->event->title }}</td>
                            <td class="px-6 py-4">
                                <div class="text-gray-700 dark:text-gray-300">{{ $booking->booked_at_date->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $booking->booked_at_time->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">₹{{ number_format($booking->amount, 2) }}</td>
                            <td class="px-6 py-4">
                                @if($booking->payment_status === 'completed')
                                    <span class="text-green-600 dark:text-green-400 text-xs">Paid</span>
                                @elseif($booking->payment_status === 'pending')
                                    <span class="text-yellow-600 dark:text-yellow-400 text-xs">Pending</span>
                                @else
                                    <span class="text-gray-500 dark:text-gray-400 text-xs">{{ ucfirst($booking->payment_status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                    @if($booking->status === 'confirmed') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($booking->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                    @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @endif">
                                    {{ ucfirst($booking->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('organization.bookings.show', $booking) }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @can('manage bookings')
                                    @if($booking->status === 'pending')
                                    <form method="POST" action="{{ route('organization.bookings.confirm', $booking) }}">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300" title="Confirm">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                    @if($booking->status !== 'cancelled')
                                    <form method="POST" action="{{ route('organization.bookings.cancel', $booking) }}" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                        @csrf
                                        <button type="submit" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300" title="Cancel">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No bookings found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($bookings->hasPages())
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                {{ $bookings->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
