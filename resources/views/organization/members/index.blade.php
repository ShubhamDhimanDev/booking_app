@extends('layouts.organization')

@section('title', 'Team Members - ' . config('app.name'))

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Team Members</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your team and their roles</p>
            </div>
            @can('invite users')
            <a href="{{ route('organization.team.invite') }}" class="mt-4 sm:mt-0 inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Invite Member
            </a>
            @endcan
        </div>
    </div>

    <!-- Stats -->
    <div class="col-span-12 sm:col-span-6 lg:col-span-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Total Members</p>
            <h3 class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 lg:col-span-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Active Members</p>
            <h3 class="mt-1 text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['active'] ?? 0 }}</h3>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 lg:col-span-4">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <p class="text-sm text-gray-500 dark:text-gray-400">Pending Invites</p>
            <h3 class="mt-1 text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['pending'] ?? 0 }}</h3>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" action="{{ route('organization.team.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search</label>
                    <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Name or email..." class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Role</label>
                    <select id="role" name="role" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">All Roles</option>
                        @foreach($roles ?? [] as $role)
                        <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>{{ ucfirst($role->name) }}</option>
                        @endforeach
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

    <!-- Team Members List -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-4">Member</th>
                            <th scope="col" class="px-6 py-4">Role</th>
                            <th scope="col" class="px-6 py-4">Events</th>
                            <th scope="col" class="px-6 py-4">Joined</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($members as $member)
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-brand-900/30 dark:text-brand-400">
                                        {{ strtoupper(substr($member->name, 0, 1)) }}
                                    </div>
                                    <div class="ml-3">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $member->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $member->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium bg-brand-100 text-brand-800 dark:bg-brand-900/30 dark:text-brand-400">
                                    {{ ucfirst($member->getRoleNames()->first() ?? 'Member') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $member->events_count ?? 0 }}</td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $member->created_at->format('M d, Y') }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                    {{ $member->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' }}">
                                    {{ ucfirst($member->status ?? 'active') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('organization.team.show', $member) }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @can('edit users')
                                    @if($member->id !== auth()->user()->organization->owner_id)
                                    <a href="{{ route('organization.team.edit', $member) }}" class="text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                                    @endif
                                    @endcan
                                    @can('delete users')
                                    @if($member->id !== auth()->user()->organization->owner_id)
                                    <form method="POST" action="{{ route('organization.team.remove', $member) }}" onsubmit="return confirm('Are you sure you want to remove this member?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
                            <td colspan="6" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No team members found</p>
                                @can('invite users')
                                <a href="{{ route('organization.team.invite') }}" class="mt-4 inline-block text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                                    Invite your first team member →
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($members->hasPages())
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                {{ $members->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
