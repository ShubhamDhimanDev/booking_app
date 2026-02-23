@extends('layouts.organization')

@section('title', 'Payment Checkout - ' . config('app.name'))

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Complete Your Payment</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Subscribe to {{ $plan->name }} plan</p>
    </div>

    <!-- Payment Details -->
    <div class="col-span-12 lg:col-span-8">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Order Summary</h3>
            </div>
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $plan->name }} Plan</h4>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $cycle === 'monthly' ? 'Monthly' : 'Yearly' }} subscription</p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">₹{{ number_format($amount, 2) }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $cycle === 'monthly' ? 'per month' : 'per year' }}</div>
                    </div>
                </div>

                <!-- Plan Features -->
                <div class="mt-6">
                    <h5 class="font-semibold text-gray-900 dark:text-white">What's Included:</h5>
                    <ul class="mt-3 space-y-2">
                        @foreach($plan->features_list ?? [] as $feature)
                        <li class="flex items-start">
                            <svg class="mr-2 mt-0.5 h-5 w-5 flex-shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>

                <!-- Payment Button -->
                <div class="mt-8">
                    <button id="razorpay-button" class="w-full rounded-lg bg-brand-600 px-6 py-3 text-base font-medium text-white hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2 dark:bg-brand-500 dark:hover:bg-brand-600">
                        <span class="flex items-center justify-center">
                            <svg class="mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Pay ₹{{ number_format($amount, 2) }}
                        </span>
                    </button>
                    <p class="mt-3 text-center text-xs text-gray-500 dark:text-gray-400">
                        <svg class="inline h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        Secure payment powered by {{ ucfirst($gateway) }}
                    </p>
                </div>

                <!-- Cancel -->
                <div class="mt-4 text-center">
                    <a href="{{ route('organization.subscription') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200">
                        Cancel and go back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing Information -->
    <div class="col-span-12 lg:col-span-4">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Billing Details</h3>
            </div>
            <div class="p-6">
                <dl class="space-y-3">
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Organization</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $organization->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Email</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $organization->contact_email }}</dd>
                    </div>
                    @if($organization->contact_phone)
                    <div>
                        <dt class="text-sm text-gray-500 dark:text-gray-400">Phone</dt>
                        <dd class="mt-1 text-sm font-medium text-gray-900 dark:text-white">{{ $organization->contact_phone }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
        </div>

        <!-- Security Notice -->
        <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/30">
            <div class="flex">
                <svg class="h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="ml-3">
                    <h4 class="text-sm font-medium text-blue-800 dark:text-blue-400">Secure Payment</h4>
                    <p class="mt-1 text-xs text-blue-700 dark:text-blue-300">Your payment information is encrypted and secure. We never store your card details.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Razorpay Checkout Script -->
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
    document.getElementById('razorpay-button').onclick = function(e) {
        e.preventDefault();

        var options = {
            "key": "{{ config('services.razorpay.key') }}",
            "amount": "{{ $amount * 100 }}", // Amount in paise
            "currency": "INR",
            "name": "{{ config('app.name') }}",
            "description": "{{ $plan->name }} Plan - {{ ucfirst($cycle) }} Subscription",
            "order_id": "{{ $orderId }}",
            "handler": function (response) {
                // Create form and submit
                var form = document.createElement('form');
                form.method = 'POST';
                form.action = "{{ route('organization.subscription.payment-callback') }}";

                // Add CSRF token
                var csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = "{{ csrf_token() }}";
                form.appendChild(csrfInput);

                // Add payment details
                var orderIdInput = document.createElement('input');
                orderIdInput.type = 'hidden';
                orderIdInput.name = 'razorpay_order_id';
                orderIdInput.value = response.razorpay_order_id;
                form.appendChild(orderIdInput);

                var paymentIdInput = document.createElement('input');
                paymentIdInput.type = 'hidden';
                paymentIdInput.name = 'razorpay_payment_id';
                paymentIdInput.value = response.razorpay_payment_id;
                form.appendChild(paymentIdInput);

                var signatureInput = document.createElement('input');
                signatureInput.type = 'hidden';
                signatureInput.name = 'razorpay_signature';
                signatureInput.value = response.razorpay_signature;
                form.appendChild(signatureInput);

                document.body.appendChild(form);
                form.submit();
            },
            "prefill": {
                "name": "{{ $organization->name }}",
                "email": "{{ $organization->contact_email }}",
                "contact": "{{ $organization->contact_phone ?? '' }}"
            },
            "theme": {
                "color": "#6366f1"
            },
            "modal": {
                "ondismiss": function() {
                    console.log('Payment cancelled by user');
                }
            }
        };

        var rzp = new Razorpay(options);
        rzp.on('payment.failed', function (response) {
            alert('Payment failed: ' + response.error.description);
            console.error(response.error);
        });

        rzp.open();
    };
</script>
@endsection
