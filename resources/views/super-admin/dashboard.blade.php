@extends('layouts.super-admin')

@section('title', 'Super Admin Dashboard - ' . config('app.name'))

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Super Admin Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Platform overview and key metrics</p>
    </div>

    <!-- Platform Stats -->
    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Organizations</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_organizations'] ?? 0 }}</h3>
                    <p class="mt-1 text-xs text-green-600 dark:text-green-400">+{{ $stats['new_orgs_this_month'] ?? 0 }} this month</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-brand-50 dark:bg-brand-900/30">
                    <svg class="h-6 w-6 text-brand-600 dark:text-brand-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Subscriptions</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['active_subscriptions'] ?? 0 }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stats['subscription_rate'] ?? 0 }}% conversion</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-50 dark:bg-green-900/30">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="col-span-12 sm:col-span-6 xl:col-span-3">
        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Monthly Revenue</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">₹{{ number_format($stats['monthly_revenue'] ?? 0) }}</h3>
                    <p class="mt-1 text-xs text-green-600 dark:text-green-400">+{{ $stats['revenue_growth'] ?? 0 }}% from last month</p>
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
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                    <h3 class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $stats['total_users'] ?? 0 }}</h3>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Across all organizations</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-orange-50 dark:bg-orange-900/30">
                    <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Organizations -->
    <div class="col-span-12 xl:col-span-8">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Organizations</h3>
                <a href="{{ route('super-admin.organizations.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                    View all →
                </a>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-700 dark:border-gray-800 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Name</th>
                                <th scope="col" class="px-6 py-3">Owner</th>
                                <th scope="col" class="px-6 py-3">Plan</th>
                                <th scope="col" class="px-6 py-3">Joined</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrganizations ?? [] as $org)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $org->name }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $org->owner->name }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                    @if($org->activeSubscription)
                                        {{ $org->activeSubscription->plan->name }}
                                    @else
                                        <span class="text-gray-400">No plan</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $org->created_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                        @if($org->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                        @else bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400
                                        @endif">
                                        {{ ucfirst($org->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No organizations yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription Plans Overview -->
    <div class="col-span-12 xl:col-span-4">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Plans Distribution</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @forelse($planDistribution ?? [] as $plan)
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $plan->name }}</span>
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $plan->subscriptions_count }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700">
                            <div class="bg-brand-600 h-2 rounded-full dark:bg-brand-500" style="width: {{ $plan->percentage }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-gray-500 dark:text-gray-400">No subscriptions yet</p>
                    @endforelse
                </div>

                <a href="{{ route('super-admin.plans.index') }}" class="mt-6 inline-block w-full rounded-lg bg-brand-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                    Manage Plans
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Subscriptions -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Recent Subscriptions</h3>
                <a href="{{ route('super-admin.subscriptions.index') }}" class="text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                    View all →
                </a>
            </div>
            <div class="p-6">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-200 text-xs uppercase text-gray-700 dark:border-gray-800 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">Organization</th>
                                <th scope="col" class="px-6 py-3">Plan</th>
                                <th scope="col" class="px-6 py-3">Billing Cycle</th>
                                <th scope="col" class="px-6 py-3">Amount</th>
                                <th scope="col" class="px-6 py-3">Started</th>
                                <th scope="col" class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentSubscriptions ?? [] as $subscription)
                            <tr class="border-b border-gray-200 dark:border-gray-800">
                                <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">{{ $subscription->organization->name }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $subscription->plan->name }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ ucfirst($subscription->billing_cycle) }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">₹{{ number_format($subscription->amount, 2) }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $subscription->started_at->format('M d, Y') }}</td>
                                <td class="px-6 py-4">
                                    <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                        @if($subscription->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                        @elseif($subscription->status === 'trial') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                                        @elseif($subscription->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                        @endif">
                                        {{ ucfirst($subscription->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                                    No subscriptions yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
