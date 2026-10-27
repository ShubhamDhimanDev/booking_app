<!-- Follow-up Session Notice -->
<div class="max-w-5xl mx-auto mb-6">
    <div class="bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-emerald-900/20 dark:to-blue-900/20 border-2 border-emerald-200 dark:border-emerald-700 rounded-2xl p-6 shadow-lg">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-full bg-emerald-500 dark:bg-emerald-600 flex items-center justify-center">
                    <span class="material-icons-round text-white text-2xl">verified</span>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">{{ ($invite->source ?? null) === 'order' ? 'Your Free Session' : 'Follow-up Session' }}</h3>
                <p class="text-slate-700 dark:text-slate-300 mb-3">{{ ($invite->source ?? null) === 'order' ? 'Thank you for your order. Choose a slot for the free session that comes with it.' : "You've been invited for a follow-up session based on your previous booking." }}</p>
                <div class="inline-flex items-center gap-2 bg-white dark:bg-slate-800 px-4 py-2 rounded-full border border-emerald-200 dark:border-emerald-700">
                    <span class="material-icons-round text-emerald-600 dark:text-emerald-400 text-lg">local_offer</span>
                    <span class="font-bold text-slate-900 dark:text-white">
                        @if($customPrice == 0)
                            <span class="text-emerald-600 dark:text-emerald-400">FREE Session!</span>
                        @else
                            Special Price: {{ $event->currency_symbol ?? '₹' }}{{ number_format($customPrice, 2) }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
