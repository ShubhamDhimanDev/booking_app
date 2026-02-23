@extends('layouts.organization')

@section('title', 'Subscription - ' . config('app.name'))

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Subscription</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Manage your subscription plan</p>
    </div>

    @if($subscription ?? null)
    <!-- Current Subscription -->
    <div class="col-span-12 lg:col-span-8">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Current Plan</h3>
            </div>
            <div class="p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $subscription->plan->name }}</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $subscription->plan->description }}</p>
                        <div class="mt-4 flex items-baseline">
                            <span class="text-3xl font-bold text-gray-900 dark:text-white">₹{{ number_format($subscription->amount) }}</span>
                            <span class="ml-2 text-gray-500 dark:text-gray-400">/ {{ $subscription->billing_cycle === 'monthly' ? 'month' : 'year' }}</span>
                        </div>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <span class="rounded-full px-4 py-2 text-sm font-medium
                            @if($subscription->status === 'active') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                            @elseif($subscription->status === 'trial') bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400
                            @elseif($subscription->status === 'cancelled') bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                            @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                            @endif">
                            {{ ucfirst($subscription->status) }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Started</p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $subscription->current_period_start->format('M d, Y') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Next Billing Date</p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $subscription->current_period_end->format('M d, Y') }}</p>
                    </div>
                    @if($subscription->trial_ends_at)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Trial Ends</p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ $subscription->trial_ends_at->format('M d, Y') }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Payment Method</p>
                        <p class="mt-1 font-medium text-gray-900 dark:text-white">{{ ucfirst($subscription->gateway) }}</p>
                    </div>
                </div>

                <!-- Plan Features -->
                <div class="mt-6">
                    <h5 class="font-semibold text-gray-900 dark:text-white">Plan Features</h5>
                    <ul class="mt-3 space-y-2">
                        @foreach($subscription->plan->features_list as $feature)
                        <li class="flex items-start">
                            <svg class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex flex-col gap-2 sm:flex-row">
                    @if($subscription->status === 'active' && !$subscription->cancel_at_period_end)
                    <a href="{{ route('organization.subscription.change-plan') }}" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                        Change Plan
                    </a>
                    <form method="POST" action="{{ route('organization.subscription.cancel') }}" onsubmit="return confirm('Are you sure you want to cancel your subscription?');">
                        @csrf
                        <button type="submit" class="inline-flex w-full items-center justify-center rounded-lg border border-red-600 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-400 dark:text-red-400 dark:hover:bg-red-900/30">
                            Cancel Subscription
                        </button>
                    </form>
                    @elseif($subscription->cancel_at_period_end)
                    <div class="rounded-lg bg-yellow-50 p-4 dark:bg-yellow-900/30">
                        <p class="text-sm text-yellow-800 dark:text-yellow-400">
                            Your subscription will be cancelled on {{ $subscription->current_period_end->format('M d, Y') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('organization.subscription.resume') }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                            Resume Subscription
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Information -->
    <div class="col-span-12 lg:col-span-4">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Billing Info</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Subscription ID</p>
                        <p class="mt-1 font-mono text-sm text-gray-900 dark:text-white">{{ $subscription->gateway_subscription_id }}</p>
                    </div>
                    @if($subscription->gateway_customer_id)
                    <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Customer ID</p>
                        <p class="mt-1 font-mono text-sm text-gray-900 dark:text-white">{{ $subscription->gateway_customer_id }}</p>
                    </div>
                    @endif
                </div>

                <a href="{{ route('organization.invoices.index') }}" class="mt-6 inline-block w-full rounded-lg border border-gray-300 px-4 py-2 text-center text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                    View Invoices
                </a>
            </div>
        </div>
    </div>

    @else
    <!-- No Subscription - Show Available Plans -->
    <div class="col-span-12">
        <div class="rounded-lg border border-yellow-200 bg-yellow-50 p-4 dark:border-yellow-800 dark:bg-yellow-900/30">
            <p class="text-yellow-800 dark:text-yellow-400">You don't have an active subscription. Choose a plan below to get started.</p>
        </div>
    </div>

    <!-- Available Plans -->
    <div class="col-span-12">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3">
            @forelse($plans ?? [] as $plan)
            <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->name }}</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ $plan->description }}</p>

                <!-- Monthly Pricing -->
                <div class="mt-4">
                    <div class="flex items-baseline">
                        <span class="text-3xl font-bold text-gray-900 dark:text-white">₹{{ number_format($plan->price_monthly) }}</span>
                        <span class="ml-2 text-gray-500 dark:text-gray-400">/ month</span>
                    </div>
                    @if($plan->price_yearly)
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        or ₹{{ number_format($plan->price_yearly) }}/year <span class="text-green-600 dark:text-green-400">(Save {{ round((1 - ($plan->price_yearly / 12) / $plan->price_monthly) * 100) }}%)</span>
                    </p>
                    @endif
                </div>

                <!-- Features -->
                <ul class="mt-6 space-y-2">
                    @foreach($plan->features_list as $feature)
                    <li class="flex items-start">
                        <svg class="mr-2 h-5 w-5 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>

                <!-- Subscribe Button -->
                <form method="POST" action="{{ route('organization.subscription.subscribe') }}" class="mt-6">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $plan->id }}">
                    <input type="hidden" name="cycle" value="monthly">
                    <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                        Subscribe Monthly
                    </button>
                </form>
                @if($plan->price_yearly)
                <form method="POST" action="{{ route('organization.subscription.subscribe') }}" class="mt-2">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $plan->id }}">
                    <input type="hidden" name="cycle" value="yearly">
                    <button type="submit" class="w-full rounded-lg border border-brand-600 px-4 py-2 text-sm font-medium text-brand-600 hover:bg-brand-50 dark:border-brand-400 dark:text-brand-400 dark:hover:bg-brand-900/30">
                        Subscribe Yearly
                    </button>
                </form>
                @endif
            </div>
            @empty
            <div class="col-span-3 text-center text-gray-500 dark:text-gray-400">
                No plans available
            </div>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection
