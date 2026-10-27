<div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 hover:shadow-xl transition-all duration-300">
    <div class="flex flex-col space-y-4">
        <div class="flex items-center space-x-4">
            <div class="flex-shrink-0">
                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-indigo-700 text-white text-2xl font-bold flex items-center justify-center ring-4 ring-primary/10 shadow-lg shadow-primary/20">
                    {{ strtoupper(substr($event->user->name, 0, 1)) }}
                </div>
            </div>
            <div>
                <h4 class="text-xs font-semibold text-primary uppercase tracking-wider mb-1">{{ $event->user->name }}</h4>
                <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">{{ $event->title }}</h2>
            </div>
        </div>

        <div class="inline-flex items-center gap-2 bg-slate-50 dark:bg-slate-700/50 px-4 py-2 rounded-full text-sm font-bold text-slate-700 dark:text-slate-300 w-fit">
            <span class="material-icons-round text-primary text-lg">schedule</span>
            <span>{{ $event->duration }} minutes</span>
        </div>

        <div class="pt-2">
            <h3 class="text-sm font-bold text-slate-900 dark:text-white mb-2">Description</h3>
            <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{!! nl2br($event->description) !!}</div>
        </div>
    </div>
</div>
