@extends('layouts.organization')

@section('title', 'Invoices - ' . config('app.name'))

@section('content')
<div class="grid grid-cols-12 gap-4 md:gap-6">
    <!-- Page Header -->
    <div class="col-span-12">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Invoices</h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">View and download your invoices</p>
    </div>

    <!-- Filters -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form method="GET" action="{{ route('organization.invoices.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <select id="status" name="status" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">All Status</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>

                <div>
                    <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">From Date</label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div>
                    <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">To Date</label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-900 focus:border-brand-500 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                </div>

                <div class="flex items-end">
                    <button type="submit" class="w-full rounded-lg bg-gray-700 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800 dark:bg-gray-700 dark:hover:bg-gray-600">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices List -->
    <div class="col-span-12">
        <div class="rounded-lg border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50 text-xs uppercase text-gray-700 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th scope="col" class="px-6 py-4">Invoice #</th>
                            <th scope="col" class="px-6 py-4">Date</th>
                            <th scope="col" class="px-6 py-4">Description</th>
                            <th scope="col" class="px-6 py-4">Amount</th>
                            <th scope="col" class="px-6 py-4">Payment Method</th>
                            <th scope="col" class="px-6 py-4">Status</th>
                            <th scope="col" class="px-6 py-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $invoice)
                        <tr class="border-b border-gray-200 dark:border-gray-800">
                            <td class="px-6 py-4 font-mono text-sm text-gray-900 dark:text-white">{{ $invoice->invoice_number }}</td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $invoice->invoice_date->format('M d, Y') }}</td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">
                                {{ $invoice->description ?? 'Subscription Payment' }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-900 dark:text-white">₹{{ number_format($invoice->amount, 2) }}</td>
                            <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ ucfirst($invoice->gateway) }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-medium
                                    @if($invoice->status === 'paid') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400
                                    @elseif($invoice->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400
                                    @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400
                                    @endif">
                                    {{ ucfirst($invoice->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('organization.invoices.show', $invoice) }}" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 dark:hover:text-brand-300" title="View">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>
                                    @if($invoice->status === 'paid')
                                    <a href="{{ route('organization.invoices.download', $invoice) }}" class="text-gray-600 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300" title="Download">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                        </svg>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">No invoices found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($invoices->hasPages())
            <div class="border-t border-gray-200 px-6 py-4 dark:border-gray-800">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
