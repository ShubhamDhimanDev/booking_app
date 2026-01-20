@extends('layouts.app')

@section('title', 'Transactions - MeetFlow')

@section('content')
<div class="mb-10">
    <h1 class="text-3xl font-extrabold text-slate-900 dark:text-white tracking-tight flex items-center gap-3">
        <span class="material-icons-round text-primary text-4xl">receipt_long</span>
        My Transactions
    </h1>
    <p class="mt-2 text-slate-500 dark:text-slate-400 max-w-2xl">
        View and manage all your payment transactions and booking history.
    </p>
</div>

<div class="bg-white dark:bg-slate-800 rounded-3xl shadow-sm border border-slate-100 dark:border-slate-700 overflow-hidden">
    <!-- Desktop Table View -->
    <div class="hidden lg:block overflow-x-auto">
        <table class="w-full">
            <thead class="bg-slate-50 dark:bg-slate-900/50 border-b border-slate-200 dark:border-slate-700">
                <tr>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Transaction ID</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Booking</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Amount</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Provider</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-4 text-left text-xs font-bold text-slate-600 dark:text-slate-300 uppercase tracking-wider">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                @forelse($payments as $p)
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-slate-900 dark:text-white">{{ $p->transaction_id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-2">
                            <span class="material-icons-round text-primary text-sm">event</span>
                            <span class="text-sm font-medium text-slate-700 dark:text-slate-300">#BK-{{ optional($p->booking)->id ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="text-sm font-bold text-slate-900 dark:text-white">{{ $p->amount }} {{ $p->currency }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-full text-xs font-semibold">
                            <span class="material-icons-round text-xs">payment</span>
                            {{ ucfirst($p->provider) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($p->status === 'success' || $p->status === 'paid')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-xs font-bold uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                {{ ucfirst($p->status) }}
                            </span>
                        @elseif($p->status === 'pending')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-xs font-bold uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                Pending
                            </span>
                        @elseif($p->status === 'failed' || $p->status === 'cancelled')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-xs font-bold uppercase">
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                                {{ ucfirst($p->status) }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-full text-xs font-bold uppercase">
                                {{ ucfirst($p->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 dark:text-slate-400">
                        {{ $p->created_at->format('d M Y, H:i') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center">
                        <div class="flex flex-col items-center gap-4">
                            <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                                <span class="material-icons-round text-4xl">receipt</span>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-600 dark:text-slate-400 mb-1">No transactions found</h3>
                                <p class="text-sm text-slate-400">Your payment history will appear here once you make bookings.</p>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Mobile Card View -->
    <div class="lg:hidden">
        @forelse($payments as $p)
        <div class="border-b border-slate-100 dark:border-slate-700 last:border-b-0 p-4 hover:bg-slate-50 dark:hover:bg-slate-700/30 transition-colors">
            <!-- Header with Status -->
            <div class="flex items-start justify-between mb-3">
                <div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Transaction ID</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-white mt-0.5">{{ $p->transaction_id }}</p>
                </div>
                <div>
                    @if($p->status === 'success' || $p->status === 'paid')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-[10px] font-bold uppercase">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            {{ ucfirst($p->status) }}
                        </span>
                    @elseif($p->status === 'pending')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-[10px] font-bold uppercase">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                            Pending
                        </span>
                    @elseif($p->status === 'failed' || $p->status === 'cancelled')
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-[10px] font-bold uppercase">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                            {{ ucfirst($p->status) }}
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-400 rounded-full text-[10px] font-bold uppercase">
                            {{ ucfirst($p->status) }}
                        </span>
                    @endif
                </div>
            </div>

            <!-- Amount (Prominent) -->
            <div class="mb-3 p-3 bg-slate-50 dark:bg-slate-900/50 rounded-xl">
                <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Amount Paid</p>
                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $p->amount }} <span class="text-base text-slate-500">{{ $p->currency }}</span></p>
            </div>

            <!-- Details Grid -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Booking</p>
                    <div class="flex items-center gap-1.5">
                        <span class="material-icons-round text-primary text-sm">event</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">#BK-{{ optional($p->booking)->id ?? 'N/A' }}</span>
                    </div>
                </div>
                <div>
                    <p class="text-[10px] uppercase font-bold text-slate-400 tracking-wider mb-1">Provider</p>
                    <div class="flex items-center gap-1.5">
                        <span class="material-icons-round text-slate-500 dark:text-slate-400 text-sm">payment</span>
                        <span class="text-sm font-semibold text-slate-700 dark:text-slate-300">{{ ucfirst($p->provider) }}</span>
                    </div>
                </div>
            </div>

            <!-- Date -->
            <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                    <span class="material-icons-round text-sm">schedule</span>
                    <span class="font-medium">{{ $p->created_at->format('d M Y, H:i') }}</span>
                </div>
            </div>
        </div>
        @empty
        <div class="p-12 text-center">
            <div class="flex flex-col items-center gap-4">
                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                    <span class="material-icons-round text-4xl">receipt</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-600 dark:text-slate-400 mb-1">No transactions found</h3>
                    <p class="text-sm text-slate-400">Your payment history will appear here once you make bookings.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($payments->hasPages())
    <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
        {{ $payments->links('pagination::tailwind') }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
{!! \App\Services\TrackingService::getEventScript('ViewTransactions', [
    'user_id' => auth()->id(),
    'total_transactions' => $payments->total() ?? 0
]) !!}
@endpush
