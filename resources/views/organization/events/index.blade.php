@extends('layouts.organization')

@section('title', 'Events - ' . config('app.name'))

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Events</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your booking events</p>
            </div>
            @can('create events')
            <a href="{{ route('organization.events.create') }}" class="mt-4 sm:mt-0 inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Event
            </a>
            @endcan
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" action="{{ route('organization.events.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Event title..." class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Type</label>
                    <select id="type" name="type" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">All Types</option>
                        <option value="one_on_one" {{ request('type') === 'one_on_one' ? 'selected' : '' }}>One-on-One</option>
                        <option value="group" {{ request('type') === 'group' ? 'selected' : '' }}>Group</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-lg bg-gray-700 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Events List -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-4">Event</th>
                            <th scope="col" class="px-6 py-4">Type</th>
                            <th scope="col" class="px-6 py-4">Duration</th>
                            <th scope="col" class="px-6 py-4">Price</th>
                            <th scope="col" class="px-6 py-4">Bookings</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events as $event)
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $event->title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($event->description, 50) }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                {{ $event->type === 'one_on_one' ? 'One-on-One' : 'Group' }}
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $event->duration }} min</td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                @if($event->price > 0)
                                    ₹{{ number_format($event->price, 2) }}
                                @else
                                    <span class="text-green-600 dark:text-green-400">Free</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $event->bookings_count }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $event->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' }}">
                                    {{ ucfirst($event->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('organization.events.show', $event) }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @can('edit events')
                                    <a href="{{ route('organization.events.edit', $event) }}" class="text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @endcan
                                    @can('delete events')
                                    <form method="POST" action="{{ route('organization.events.destroy', $event) }}" onsubmit="return confirm('Are you sure you want to delete this event?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No events found</p>
                                @can('create events')
                                <a href="{{ route('organization.events.create') }}" class="mt-4 inline-block text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                    Create your first event →
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($events->hasPages())
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                {{ $events->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
