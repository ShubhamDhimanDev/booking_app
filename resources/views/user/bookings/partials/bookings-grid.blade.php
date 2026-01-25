<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @forelse($bookings as $b)
        <div data-status="{{ $b->status }}"
            class="booking-card group bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm hover:shadow-2xl transition-all duration-300 border border-slate-100 dark:border-slate-700 relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4">
                @if ($b->status === 'confirmed')
                    <span
                        class="flex items-center gap-1.5 px-3 py-1 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Confirmed
                    </span>
                @elseif($b->status === 'pending')
                    <span
                        class="flex items-center gap-1.5 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                        Pending @if (!$b->payment && optional($b->event)->price > 0)
                            Payment
                        @endif
                    </span>
                @elseif($b->status === 'cancelled')
                    <span
                        class="flex items-center gap-1.5 px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>
                        Cancelled
                    </span>
                @else
                    <span
                        class="flex items-center gap-1.5 px-3 py-1 bg-slate-100 dark:bg-slate-900/30 text-slate-700 dark:text-slate-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        {{ ucfirst($b->status) }}
                    </span>
                @endif
            </div>
            <div class="mb-6">
                <span
                    class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">#BK-{{ $b->id }}</span>
                <h3
                    class="text-xl font-bold text-slate-800 dark:text-white mt-1 group-hover:text-primary transition-colors">
                    {{ optional($b->event)->title ?? 'Untitled Event' }}
                </h3>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">with
                    {{ optional($b->event->user)->name ?? 'Unknown' }}</p>
            </div>
            <div class="space-y-4 mb-8">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                        <span class="material-icons-round text-xl">calendar_today</span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Date</p>
                        <p class="text-sm font-semibold dark:text-slate-200">
                            {{ \Carbon\Carbon::parse($b->booked_at_date)->format('D, d M Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-2xl bg-slate-50 dark:bg-slate-700/50 flex items-center justify-center text-primary">
                        <span class="material-icons-round text-xl">schedule</span>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-slate-400">Time</p>
                        <p class="text-sm font-semibold dark:text-slate-200">
                            {{ \Carbon\Carbon::parse($b->booked_at_time, 'UTC')->format('g:i A') }}
                            ({{ optional($b->event)->duration ?? 60 }} min)</p>
                    </div>
                </div>
            </div>
            <div class="space-y-3 pt-4 border-t border-slate-100 dark:border-slate-700">
                {{-- Primary Action Button --}}
                @php
                    $bookingDateTime = \Carbon\Carbon::parse($b->booked_at_date . ' ' . $b->booked_at_time);
                    $isExpired = $bookingDateTime->isPast();
                @endphp
                
                @if ($b->status === 'confirmed' && $b->meet_link)
                    @if ($isExpired)
                        <div class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-slate-100 dark:bg-slate-700 text-slate-500 dark:text-slate-400 rounded-2xl text-sm font-bold">
                            <span class="material-icons-round text-lg">event_busy</span>
                            Expired
                        </div>
                    @else
                        <a href="{{ $b->meet_link }}" target="_blank"
                            class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-primary text-white rounded-2xl text-sm font-bold transition-all shadow-lg shadow-primary/20 hover:bg-indigo-700">
                            <span class="material-icons-round text-lg">videocam</span>
                            Join Meeting
                        </a>
                    @endif
                @elseif(!$b->payment && $b->status === 'pending' && optional($b->event)->price > 0)
                    <a href="{{ route('payment.page', $b->id) }}"
                        class="flex items-center justify-center gap-2 w-full px-4 py-2.5 bg-cyan-500 hover:bg-cyan-600 text-white rounded-2xl text-sm font-bold transition-all shadow-lg shadow-cyan-500/20">
                        <span class="material-icons-round text-lg">payments</span>
                        Pay Now
                    </a>
                @endif

                {{-- Secondary Actions (Reschedule & Cancel) --}}
                @if (($b->status === 'confirmed' || $b->status === 'pending') && !$isExpired)
                    <div class="grid grid-cols-2 gap-2">
                        <a href="{{ route('user.bookings.reschedule.form', $b->id) }}"
                            class="flex items-center justify-center gap-1.5 px-3 py-2 bg-slate-100 dark:bg-slate-700 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-600 dark:text-slate-300 rounded-xl text-xs font-bold transition-all">
                            <span class="material-icons-round text-base">sync</span>
                            Reschedule
                        </a>
                        @if($b->canCancel())
                            <button onclick="openCancelModal({{ $b->id }}, '{{ addslashes($b->event->title ?? 'Event') }}')"
                                class="flex items-center justify-center gap-1.5 px-3 py-2 bg-red-50 dark:bg-red-900/20 hover:bg-red-500 hover:text-white text-red-600 dark:text-red-400 rounded-xl text-xs font-bold transition-all">
                                <span class="material-icons-round text-base">cancel</span>
                                Cancel
                            </button>
                        @else
                            <div class="flex items-center justify-center px-3 py-2 bg-slate-50 dark:bg-slate-700/30 text-slate-400 dark:text-slate-500 rounded-xl text-xs font-bold cursor-not-allowed">
                                <span class="material-icons-round text-base">block</span>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full">
            <div
                class="group bg-slate-50/50 dark:bg-slate-800/30 rounded-3xl p-12 border-2 border-dashed border-slate-200 dark:border-slate-700 flex flex-col items-center justify-center text-center gap-4">
                <div
                    class="w-20 h-20 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center text-slate-400">
                    <span class="material-icons-round text-5xl">event_busy</span>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-slate-600 dark:text-slate-400 mb-2">No bookings yet</h3>
                    <p class="text-sm text-slate-400 max-w-md">Your bookings will appear here once you make a
                        reservation. Start by booking a session with our experts.</p>
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($bookings->hasPages())
<div class="mt-8">
    {{ $bookings->links('pagination::tailwind') }}
</div>
@endif
