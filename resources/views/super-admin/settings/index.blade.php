@extends('layouts.super-admin')

@section('title', 'System Settings - ' . config('app.name'))

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">System Settings</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure global platform settings</p>
    </div>

    <!-- Payment Gateway Settings -->
    <div class="col-span-12 lg:col-span-8">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Payment Gateway Settings</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Configure which payment gateway is used for subscriptions</p>
            </div>
            <div class="p-6">
                <form method="POST" action="{{ route('super-admin.settings.payment') }}">
                    @csrf
                    @method('PATCH')

                    <!-- Default Gateway Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Default Payment Gateway
                        </label>
                        <select name="default_payment_gateway" class="mt-1 block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white" required>
                            <option value="razorpay" {{ $settings['default_payment_gateway'] === 'razorpay' ? 'selected' : '' }}>Razorpay (India)</option>
                            <option value="stripe" {{ $settings['default_payment_gateway'] === 'stripe' ? 'selected' : '' }}>Stripe (Global)</option>
                            <option value="paypal" {{ $settings['default_payment_gateway'] === 'paypal' ? 'selected' : '' }}>PayPal (Global)</option>
                        </select>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">This gateway will be used for all subscription payments</p>
                    </div>

                    <!-- Gateway Status -->
                    <div class="space-y-4">
                        <h4 class="font-medium text-gray-900 dark:text-white">Gateway Status</h4>

                        <!-- Razorpay -->
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-100 dark:bg-blue-900/30">
                                    <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-900 dark:text-white">Razorpay</p>
                                        @if(config('services.razorpay.key'))
                                        <span class="ml-2 rounded-full bg-green-100 px-2 py-1 text-xs text-green-800 dark:bg-green-900/30 dark:text-green-400">Configured</span>
                                        @else
                                        <span class="ml-2 rounded-full bg-yellow-100 px-2 py-1 text-xs text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Not Configured</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Primary payment gateway for India</p>
                                </div>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" name="razorpay_enabled" value="1" {{ $settings['razorpay_enabled'] ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable</span>
                            </label>
                        </div>

                        <!-- Stripe -->
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-purple-100 dark:bg-purple-900/30">
                                    <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-900 dark:text-white">Stripe</p>
                                        <span class="ml-2 rounded-full bg-yellow-100 px-2 py-1 text-xs text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Coming Soon</span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Global payment gateway</p>
                                </div>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" name="stripe_enabled" value="1" {{ $settings['stripe_enabled'] ? 'checked' : '' }} disabled
                                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable</span>
                            </label>
                        </div>

                        <!-- PayPal -->
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 p-4 dark:border-gray-700">
                            <div class="flex items-center">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-100 dark:bg-indigo-900/30">
                                    <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="flex items-center">
                                        <p class="font-medium text-gray-900 dark:text-white">PayPal</p>
                                        <span class="ml-2 rounded-full bg-yellow-100 px-2 py-1 text-xs text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400">Coming Soon</span>
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Global payment gateway</p>
                                </div>
                            </div>
                            <label class="flex items-center">
                                <input type="checkbox" name="paypal_enabled" value="1" {{ $settings['paypal_enabled'] ? 'checked' : '' }} disabled
                                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Enable</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 dark:bg-brand-500 dark:hover:bg-brand-600">
                            Save Payment Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Cache Management -->
    <div class="col-span-12 lg:col-span-4">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Cache Management</h3>
            </div>
            <div class="p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400">Clear application cache to apply setting changes</p>
                <form method="POST" action="{{ route('super-admin.settings.cache-clear') }}" class="mt-4">
                    @csrf
                    <button type="submit" class="w-full rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-gray-800">
                        Clear Cache
                    </button>
                </form>
            </div>
        </div>

        <!-- Webhook Configuration -->
        <div class="mt-6 rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Webhook URLs</h3>
            </div>
            <div class="p-6">
                <div class="space-y-3">
                    <div>
                        <label class="text-xs font-medium text-gray-500 dark:text-gray-400">Razorpay Subscription Webhook</label>
                        <div class="mt-1 flex items-center rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 dark:border-gray-700 dark:bg-gray-800">
                            <input type="text" readonly value="{{ route('webhooks.subscription', 'razorpay') }}" class="flex-1 border-0 bg-transparent text-xs text-gray-600 dark:text-gray-400 focus:outline-none">
                            <button onclick="navigator.clipboard.writeText('{{ route('webhooks.subscription', 'razorpay') }}')" class="ml-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">Configure these URLs in your payment gateway dashboard to receive webhook notifications</p>
            </div>
        </div>
    </div>
</div>
@endsection
