@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div class="mb-10">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
        <span class="material-icons-round text-primary text-4xl">inventory_2</span>
        My Orders
    </h1>
    <p class="mt-2 text-slate-500 dark:text-slate-400 max-w-2xl">Track your store purchases and open the free session that comes with them.</p>
</div>

<div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Order</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Items</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Total</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                        <td class="px-6 py-4 text-sm font-semibold">
                            <a class="text-primary hover:underline" href="{{ route('user.orders.show', $order) }}">{{ $order->order_number }}</a>
                        </td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="px-6 py-4 text-sm text-slate-600 dark:text-slate-300">{{ $order->items_count }}</td>
                        <td class="px-6 py-4 text-sm font-bold text-slate-900 dark:text-white">{{ $order->currency_symbol }}{{ number_format($order->total, 2) }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-xs font-bold uppercase">{{ $order->status_label }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center text-slate-400">You haven't placed any orders yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($orders->hasPages())
    <div class="mt-6">{{ $orders->links() }}</div>
@endif
@endsection
