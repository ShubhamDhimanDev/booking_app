@extends('layouts.super-admin')

@section('title', 'Organizations - Super Admin')

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Organizations</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage all organizations on the platform</p>
            </div>
            <a href="{{ route('super-admin.organizations.create') }}" class="mt-4 sm:mt-0 inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add Organization
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="col-span-12 sm:col-span-6 lg:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Organizations</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 lg:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Active</p>
            <h3 class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['active'] ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 lg:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">With Subscriptions</p>
            <h3 class="mt-1 text-2xl font-bold text-brand-600 dark:text-brand-400">{{ $stats['subscribed'] ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 lg:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">This Month</p>
            <h3 class="mt-1 text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['this_month'] ?? 0 }}</h3>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" action="{{ route('super-admin.organizations.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Organization name..." class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>

                <div>
                    <label for="has_subscription" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subscription</label>
                    <select id="has_subscription" name="has_subscription" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">All</option>
                        <option value="1" {{ request('has_subscription') === '1' ? 'selected' : '' }}>With Subscription</option>
                        <option value="0" {{ request('has_subscription') === '0' ? 'selected' : '' }}>No Subscription</option>
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

    <!-- Organizations List -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-4">Organization</th>
                            <th scope="col" class="px-6 py-4">Owner</th>
                            <th scope="col" class="px-6 py-4">Plan</th>
                            <th scope="col" class="px-6 py-4">Members</th>
                            <th scope="col" class="px-6 py-4">Events</th>
                            <th scope="col" class="px-6 py-4">Joined</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($organizations as $org)
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $org->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $org->subdomain }}.{{ config('app.domain') }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-gray-700 dark:text-gray-300">{{ $org->owner->name }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $org->owner->email }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                @if($org->activeSubscription)
                                    {{ $org->activeSubscription->plan->name }}
                                @else
                                    <span class="text-gray-400">No plan</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $org->users_count }}</td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $org->events_count }}</td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $org->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                    @if($org->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($org->status === 'suspended') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                    @endif">
                                    {{ ucfirst($org->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('super-admin.organizations.show', $org) }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    <a href="{{ route('super-admin.organizations.edit', $org) }}" class="text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @if($org->status === 'active')
                                    <form method="POST" action="{{ route('super-admin.organizations.suspend', $org) }}" onsubmit="return confirm('Are you sure you want to suspend this organization?');">
                                        @csrf
                                        <button type="submit" class="text-yellow-600 hover:text-yellow-700 dark:text-yellow-400 dark:hover:text-yellow-300" title="Suspend">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                    @else
                                    <form method="POST" action="{{ route('super-admin.organizations.activate', $org) }}">
                                        @csrf
                                        <button type="submit" class="text-green-600 hover:text-green-700 dark:text-green-400 dark:hover:text-green-300" title="Activate">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No organizations found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($organizations->hasPages())
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                {{ $organizations->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
