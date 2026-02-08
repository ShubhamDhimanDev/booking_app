@extends('layouts.super-admin')

@section('title', 'Subscription Plans - Super Admin')

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription Plans</h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage subscription plans and pricing</p>
            </div>
            <a href="{{ route('super-admin.plans.create') }}" class="mt-4 sm:mt-0 inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Plan
            </a>
        </div>
    </div>

    <!-- Plans Grid -->
    <div class="col-span-12">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($plans as $plan)
            <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                        <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                            {{ $plan->status === 'active' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400' }}">
                            {{ ucfirst($plan->status) }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>
                </div>

                <div class="p-6">
                    <!-- Pricing -->
                    <div class="mb-4">
                        <div class="flex items-baseline">
                            <span class="text-3xl font-bold text-gray-900 dark:text-white">₹{{ number_format($plan->monthly_price) }}</span>
                            <span class="ml-2 text-gray-500 dark:text-gray-400">/ month</span>
                        </div>
                        @if($plan->yearly_price)
                        <div class="mt-2 flex items-baseline text-sm">
                            <span class="font-semibold text-gray-700 dark:text-gray-300">₹{{ number_format($plan->yearly_price) }}</span>
                            <span class="ml-2 text-gray-500 dark:text-gray-400">/ year</span>
                            <span class="ml-2 text-green-600 dark:text-green-400">(Save {{ round((1 - ($plan->yearly_price / 12) / $plan->monthly_price) * 100) }}%)</span>
                        </div>
                        @endif
                    </div>

                    <!-- Limits -->
                    <div class="space-y-2 border-t border-gray-200 pt-4 dark:border-gray-800">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Max Events</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ is_null($plan->max_events) ? 'Unlimited' : number_format($plan->max_events) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Max Team Members</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ is_null($plan->max_team_members) ? 'Unlimited' : number_format($plan->max_team_members) }}
                            </span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Max Bookings/Month</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ is_null($plan->max_bookings_per_month) ? 'Unlimited' : number_format($plan->max_bookings_per_month) }}
                            </span>
                        </div>
                    </div>

                    <!-- Features -->
                    @if($plan->features)
                    <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Features</p>
                        <ul class="mt-2 space-y-1">
                            @foreach($plan->features as $feature)
                            <li class="flex items-start text-sm text-gray-700 dark:text-gray-300">
                                <svg class="mr-2 h-4 w-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ $feature }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <!-- Stats -->
                    <div class="mt-4 border-t border-gray-200 pt-4 dark:border-gray-800">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 dark:text-gray-400">Subscriptions</span>
                            <span class="font-semibold text-brand-600 dark:text-brand-400">{{ $plan->subscriptions_count }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="mt-6 flex gap-2">
                        <a href="{{ route('super-admin.plans.edit', $plan) }}" class="flex-1 rounded-lg bg-brand-600 px-4 py-2 text-center text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                            Edit Plan
                        </a>
                        @if($plan->status === 'active')
                        <form method="POST" action="{{ route('super-admin.plans.deactivate', $plan) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                                Deactivate
                            </button>
                        </form>
                        @else
                        <form method="POST" action="{{ route('super-admin.plans.activate', $plan) }}" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full rounded-lg border border-green-600 px-4 py-2 text-sm font-medium text-green-600 hover:bg-green-50 dark:border-green-400 dark:text-green-400 dark:hover:bg-green-900/30">
                                Activate
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 rounded-lg border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No subscription plans found</p>
                <a href="{{ route('super-admin.plans.create') }}" class="mt-4 inline-block text-sm font-medium text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300">
                    Create your first plan →
                </a>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
