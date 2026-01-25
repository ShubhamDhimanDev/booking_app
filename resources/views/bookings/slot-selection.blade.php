@extends('layouts.app')

@section('title', "{$event->user->name} | {$event->title} Meeting")

{{-- Header customization --}}
@section('header-icon', 'event_note')

{{-- Badge customization --}}
@section('badge-color', 'bg-blue-50 dark:bg-blue-900/30')
@section('badge-border', 'border-blue-200 dark:border-blue-700')
@section('badge-icon', 'verified_user')
@section('badge-text-color', 'text-blue-600 dark:text-blue-400')
@section('badge-text', 'Secure Booking')

{{-- Include loader --}}
@section('loader')
@section('loader-text', 'Please wait...')

{{-- Add tracking scripts --}}
{{-- Base script loaded from layout --}}

{{-- Custom styles --}}
@section('additional-styles')
<style>
    /* Reset and override style.css for this modern design */
    .schedule .date,
    .schedule .time-slot {
        all: unset;
        box-sizing: border-box;
        display: flex;
    }

    /* Progress step animation */
    .progress-step {
        transition: all 0.3s ease;
    }

    .progress-step.active {
        transform: scale(1.05);
    }

    /* Custom calendar styles with dark mode */
    .day-name {
        text-align: center;
        padding: 0.5rem 0;
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
    }

    .dark .day-name {
        color: #94a3b8;
    }

    /* Available dates - prominent and bright */
    #calendar .date {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        /* Light mode - available dates are dark and bold */
        color: #0f172a !important;
        background-color: #ffffff !important;
        border: 2px solid #cbd5e1 !important;
    }

    .dark #calendar .date {
        /* Dark mode - available dates are bright */
        color: #f1f5f9 !important;
        background-color: #475569 !important;
        border: 2px solid #64748b !important;
    }

    #calendar .date:hover {
        background-color: #6366f1 !important;
        color: #ffffff !important;
        border-color: #6366f1 !important;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.3);
    }

    #calendar .date.active {
        background: linear-gradient(to bottom right, #6366f1, #4f46e5) !important;
        color: #ffffff !important;
        border-color: #6366f1 !important;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        transform: scale(1.05);
    }

    /* Disabled dates - dim and faded */
    #calendar .date.disabled {
        opacity: 0.4;
        cursor: not-allowed !important;
        /* Light mode - disabled are very light */
        color: #cbd5e1 !important;
        background-color: #f8fafc !important;
        border: 1px solid #e2e8f0 !important;
    }

    .dark #calendar .date.disabled {
        /* Dark mode - disabled are very dark */
        color: #475569 !important;
        background-color: #1e293b !important;
        border: 1px solid #334155 !important;
    }

    #calendar .date.disabled:hover {
        background-color: #f8fafc !important;
        color: #cbd5e1 !important;
        border-color: #e2e8f0 !important;
        box-shadow: none !important;
        transform: none !important;
    }

    .dark #calendar .date.disabled:hover {
        background-color: #1e293b !important;
        color: #475569 !important;
        border-color: #334155 !important;
    }

    /* Time slots */
    #timeSlots .time-slot {
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        font-size: 0.875rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        /* Light mode */
        color: #1e293b !important;
        background-color: #ffffff !important;
        border: 2px solid #cbd5e1 !important;
    }

    .dark #timeSlots .time-slot {
        /* Dark mode */
        color: #f1f5f9 !important;
        background-color: #334155 !important;
        border: 2px solid #475569 !important;
    }

    #timeSlots .time-slot:hover {
        border-color: #6366f1 !important;
        color: #6366f1 !important;
        background-color: rgba(99, 102, 241, 0.05) !important;
        box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2);
    }

    .dark #timeSlots .time-slot:hover {
        background-color: rgba(99, 102, 241, 0.15) !important;
    }

    #timeSlots .time-slot.selected {
        background: linear-gradient(to bottom right, #6366f1, #4f46e5) !important;
        border-color: #6366f1 !important;
        color: #ffffff !important;
        box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
        transform: scale(1.05);
    }
</style>
@endsection

@section('content')

        <!-- Progress Indicator -->
        <div class="mb-10">
            <div class="flex items-center justify-center space-x-2 sm:space-x-4">
                <div class="flex items-center progress-step active">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary to-indigo-700 flex items-center justify-center ring-4 ring-primary/20 shadow-lg shadow-primary/40 animate-pulse">
                        <span class="material-icons-round text-white text-lg">event</span>
                    </div>
                    <span class="ml-2 text-sm font-bold text-slate-900 dark:text-white">Details</span>
                </div>
                <div class="w-16 sm:w-32 h-1 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
                <div class="flex items-center progress-step">
                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                        <span class="material-icons-round text-slate-400 dark:text-slate-500 text-lg">payment</span>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-slate-400 dark:text-slate-500">Payment</span>
                </div>
                <div class="w-16 sm:w-32 h-1 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
                <div class="flex items-center progress-step">
                    <div class="w-10 h-10 rounded-full bg-slate-200 dark:bg-slate-700 flex items-center justify-center">
                        <span class="material-icons-round text-slate-400 dark:text-slate-500 text-lg">done_all</span>
                    </div>
                    <span class="ml-2 text-sm font-semibold text-slate-400 dark:text-slate-500 hidden sm:inline">Confmration</span>
                </div>
            </div>
        </div>

        <!-- Follow-up Session Notice -->
        @if(isset($isFollowUp) && $isFollowUp && isset($customPrice))
        <div class="max-w-5xl mx-auto mb-6">
            <div class="bg-gradient-to-r from-emerald-50 to-blue-50 dark:from-emerald-900/20 dark:to-blue-900/20 border-2 border-emerald-200 dark:border-emerald-700 rounded-2xl p-6 shadow-lg">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 rounded-full bg-emerald-500 dark:bg-emerald-600 flex items-center justify-center">
                            <span class="material-icons-round text-white text-2xl">verified</span>
                        </div>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-2">Follow-up Session</h3>
                        <p class="text-slate-700 dark:text-slate-300 mb-3">You've been invited for a follow-up session based on your previous booking.</p>
                        <div class="inline-flex items-center gap-2 bg-white dark:bg-slate-800 px-4 py-2 rounded-full border border-emerald-200 dark:border-emerald-700">
                            <span class="material-icons-round text-emerald-600 dark:text-emerald-400 text-lg">local_offer</span>
                            <span class="font-bold text-slate-900 dark:text-white">
                                @if($customPrice == 0)
                                    <span class="text-emerald-600 dark:text-emerald-400">FREE Session!</span>
                                @else
                                    Special Price: ₹{{ number_format($customPrice, 2) }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8">

            <!-- Left Column - Event Info -->
            <div class="lg:col-span-2 bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 hover:shadow-xl transition-all duration-300">
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
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed">{!! $event->description !!}</div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Date & Time Selection -->
            <div class="lg:col-span-3 schedule bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 hover:shadow-xl transition-all duration-300">
            <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <span class="material-icons-round text-primary text-3xl">event</span>
                Select Date & Time
            </h3>

            <div class="calendar-header flex items-center justify-between mb-6 bg-slate-50 dark:bg-slate-700/50 p-4 rounded-2xl">
                <button id="prevMonth" class="w-11 h-11 rounded-xl bg-white dark:bg-slate-600 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-700 dark:text-slate-300 font-bold transition-all flex items-center justify-center shadow-sm hover:shadow-md">
                    <span class="material-icons-round">chevron_left</span>
                </button>
                <h3 id="monthLabel" class="text-xl font-extrabold text-slate-900 dark:text-white"></h3>
                <button id="nextMonth" class="w-11 h-11 rounded-xl bg-white dark:bg-slate-600 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-700 dark:text-slate-300 font-bold transition-all flex items-center justify-center shadow-sm hover:shadow-md">
                    <span class="material-icons-round">chevron_right</span>
                </button>
            </div>

            <div class="calendar grid grid-cols-7 gap-2 mb-6" id="calendar"></div>
            <div class="time-slots grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6" id="timeSlots"></div>
            <div class="flex justify-center">
                <div class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 font-medium bg-slate-50 dark:bg-slate-700/50 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600">
                    <span class="material-icons-round text-lg">public</span>
                    <span>India Standard Time (IST)</span>
                </div>
            </div>

            <div class="confirm-panel hidden mt-8 bg-gradient-to-br from-primary/5 via-indigo-50 to-purple-50 dark:from-primary/20 dark:via-slate-700 dark:to-purple-900/20 rounded-2xl p-8 mb-6 text-center border-2 border-primary/20 dark:border-primary/30 shadow-inner" id="confirmPanel">
                <div class="flex items-start space-x-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-indigo-700 flex items-center justify-center flex-shrink-0 shadow-lg shadow-primary/30">
                        <span class="material-icons-round text-white text-2xl">check_circle</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-primary uppercase tracking-wider mb-2">Selected Time Slot</p>
                        <p id="confirmText" class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight"></p>
                    </div>
                </div>
                <form method="GET" action="{{ route('bookings.details', $event->slug) }}" id="slotForm">
                    <input type="hidden" name="date" id="selectedDate">
                    <input type="hidden" name="time" id="selectedTime">
                    @if(isset($isFollowUp) && $isFollowUp && isset($invite))
                        <input type="hidden" name="followup_token" value="{{ $invite->token }}">
                    @endif
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-indigo-700 hover:opacity-95 text-white font-extrabold py-4 rounded-2xl transition-all duration-300 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40 hover:scale-[1.02]">
                        <span class="material-icons-round">arrow_forward</span>
                        Next: Enter Details
                    </button>
                </form>
            </div>
        </div>

        </div>
        <!-- End Two Column Layout -->
@endsection

@push('scripts')
    <script>
        // Get DOM elements
        const calendar = document.getElementById('calendar');
        const monthLabel = document.getElementById('monthLabel');
        const timeSlotsDiv = document.getElementById('timeSlots');
        const confirmPanel = document.getElementById('confirmPanel');
        const confirmText = document.getElementById('confirmText');
        const slotForm = document.getElementById('slotForm');
        const selectedDateInput = document.getElementById('selectedDate');
        const selectedTimeInput = document.getElementById('selectedTime');

        let currentMonth = new Date();
        let selectedDate = null;
        let selectedTime = null;

        function formatLocalDate(d) {
            const y = d.getFullYear();
            const m = String(d.getMonth() + 1).padStart(2, '0');
            const day = String(d.getDate()).padStart(2, '0');
            return `${y}-${m}-${day}`;
        }

        function timeToMinutes(t) {
            const [hh, mm] = (t || '').split(':').map(Number);
            if (isNaN(hh) || isNaN(mm)) return 0;
            return hh * 60 + mm;
        }

        // Available slots from backend
        const availableDates = {!! json_encode($availableSlots) !!};

        // Weekday restrictions
        const allowedWeekDays = {!! json_encode($event->available_week_days ?? []) !!};
        const allowedWeekDaysLower = (Array.isArray(allowedWeekDays) ? allowedWeekDays.map(d => d.toString().toLowerCase()) : []);

        // Exclusions per date
        const eventExclusionsRaw = {!! json_encode($event->exclusions->map(function($e){
            return [
                'date' => $e->date->format('Y-m-d'),
                'exclude_all' => (bool)$e->exclude_all,
                'times' => $e->times ?? []
            ];
        })->toArray()) !!};
        const eventExclusions = eventExclusionsRaw;

        function renderCalendar(date) {
            calendar.innerHTML = "";
            const year = date.getFullYear();
            const month = date.getMonth();
            const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September",
                "October", "November", "December"
            ];
            monthLabel.textContent = `${monthNames[month]} ${year}`;

            const dayNames = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
            dayNames.forEach(d => {
                const div = document.createElement('div');
                div.className = 'day-name';
                div.textContent = d;
                calendar.appendChild(div);
            });

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDay = (firstDay.getDay() + 6) % 7;

            for (let i = 0; i < startDay; i++) {
                const emptyDiv = document.createElement('div');
                calendar.appendChild(emptyDiv);
            }

            for (let i = 1; i <= lastDay.getDate(); i++) {
                const dateObj = new Date(year, month, i);
                const dateStr = formatLocalDate(dateObj);
                const dateEl = document.createElement('div');
                dateEl.className = 'date';
                dateEl.textContent = i;

                const availableDate = availableDates.find(d => d.date === dateStr);
                const weekdayNames = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                const weekday = weekdayNames[dateObj.getDay()];
                const weekdayAllowed = allowedWeekDaysLower.length === 0 ? true : allowedWeekDaysLower.includes(weekday);

                let hasSlots = false;
                if (availableDate && Array.isArray(availableDate.timeslots)) {
                    let daySlots = availableDate.timeslots;
                    const exclusion = eventExclusions.find(x => x.date === dateStr);
                    if (exclusion) {
                        if (exclusion.exclude_all) {
                            daySlots = [];
                        } else if (Array.isArray(exclusion.times) && exclusion.times.length) {
                            daySlots = daySlots.filter(s => !exclusion.times.includes(s.start));
                        }
                    }

                    const todayStr = formatLocalDate(new Date());
                    if (dateStr === todayStr) {
                        const now = new Date();
                        daySlots = daySlots.filter(s => {
                            const slotEnd = new Date(dateStr + 'T' + s.end);
                            return slotEnd.getTime() > now.getTime();
                        });
                    }
                    hasSlots = daySlots.length > 0;
                }

                const todayStr = formatLocalDate(new Date());
                if (dateStr < todayStr) {
                    dateEl.classList.add('disabled');
                } else if (availableDate && weekdayAllowed && hasSlots) {
                    dateEl.addEventListener('click', () => selectDate(dateObj, dateEl));
                } else {
                    dateEl.classList.add('disabled');
                }

                calendar.appendChild(dateEl);
            }
        }

        function selectDate(date, el) {
            document.querySelectorAll('.date').forEach(d => d.classList.remove('active'));
            el.classList.add('active');
            selectedDate = date;
            renderTimeSlots();
        }

        function renderTimeSlots() {
            timeSlotsDiv.innerHTML = "";
            confirmPanel.classList.add('hidden');
            if (!selectedDate) return;

            const dateStr = formatLocalDate(selectedDate);
            const daySlotsRaw = availableDates.find(d => d.date === dateStr)?.timeslots || [];

            const exclusion = eventExclusions.find(x => x.date === dateStr);
            let daySlots = daySlotsRaw;
            if (exclusion) {
                if (exclusion.exclude_all) {
                    daySlots = [];
                } else if (Array.isArray(exclusion.times) && exclusion.times.length) {
                    daySlots = daySlots.filter(s => !exclusion.times.includes(s.start));
                }
            }

            const todayStr = formatLocalDate(new Date());
            if (dateStr === todayStr) {
                const now = new Date();
                daySlots = daySlots.filter(s => {
                    const slotEnd = new Date(dateStr + 'T' + s.end);
                    return slotEnd.getTime() > now.getTime();
                });
            }

            daySlots.sort((a,b) => timeToMinutes(a.start) - timeToMinutes(b.start));

            if (daySlots.length === 0) {
                timeSlotsDiv.innerHTML = '<div class="col-span-full text-center text-slate-500 dark:text-slate-400 py-4">No slots available for this day.</div>';
                return;
            }

            daySlots.forEach(slot => {
                const t = document.createElement('div');
                t.className = 'time-slot';

                let [hours, minutes] = slot.start.split(':').map(Number);
                const ampm = hours >= 12 ? 'pm' : 'am';
                const displayHours = hours % 12 || 12;
                t.textContent = `${displayHours}:${minutes.toString().padStart(2,'0')}${ampm}`;

                t.dataset.backendTime = slot.start;
                t.addEventListener('click', () => selectTime(slot.start, t));
                timeSlotsDiv.appendChild(t);
            });
        }

        function selectTime(time, el) {
            console.log('Time selected:', time);

            document.querySelectorAll('.time-slot').forEach(t => t.classList.remove('selected'));
            el.classList.add('selected');
            selectedTime = time;

            selectedDateInput.value = formatLocalDate(selectedDate);
            selectedTimeInput.value = time;

            confirmPanel.classList.remove('hidden');

            const [hours, minutes] = time.split(':').map(Number);
            const ampm = hours >= 12 ? 'pm' : 'am';
            const displayHours = hours % 12 || 12;
            const displayTime = `${displayHours}:${minutes.toString().padStart(2,'0')}${ampm}`;

            confirmText.textContent = `You've selected ${selectedDate.toDateString()} at ${displayTime}`;

            setTimeout(() => {
                confirmPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);

            console.log('Confirm panel displayed');
        }

        document.getElementById('prevMonth').addEventListener('click', () => {
            currentMonth.setMonth(currentMonth.getMonth() - 1);
            renderCalendar(currentMonth);
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentMonth.setMonth(currentMonth.getMonth() + 1);
            renderCalendar(currentMonth);
        });

        renderCalendar(currentMonth);
    </script>
@endpush
