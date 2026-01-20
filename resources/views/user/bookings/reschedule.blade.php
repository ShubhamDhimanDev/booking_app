@extends('layouts.app')

@section('title', 'Reschedule Booking - MeetFlow')

@push('styles')
    <style>
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
            color: #0f172a !important;
            background-color: #ffffff !important;
            border: 2px solid #cbd5e1 !important;
        }

        .dark #calendar .date {
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
            color: #cbd5e1 !important;
            background-color: #f8fafc !important;
            border: 1px solid #e2e8f0 !important;
        }

        .dark #calendar .date.disabled {
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
            color: #1e293b !important;
            background-color: #ffffff !important;
            border: 2px solid #cbd5e1 !important;
        }

        .dark #timeSlots .time-slot {
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

        #timeSlots .time-slot.active {
            background: linear-gradient(to bottom right, #6366f1, #4f46e5) !important;
            border-color: #6366f1 !important;
            color: #ffffff !important;
            box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);
            transform: scale(1.05);
        }

        .no-slots {
            text-align: center;
            color: #64748b;
            padding: 20px;
            font-size: 14px;
        }

        .dark .no-slots {
            color: #94a3b8;
        }
    </style>
@endpush

@section('content')
    <div class="mb-6">
        <a href="{{ route('user.bookings.index') }}"
            class="inline-flex items-center gap-2 text-primary hover:text-primary/80 font-medium transition-colors">
            <span class="material-icons-round text-xl">arrow_back</span>
            Back to Bookings
        </a>
    </div>

    @if (session('alert_type'))
        <div class="p-4 rounded-lg mb-5 border-l-4 {{ session('alert_type') === 'success' ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20' : 'border-red-500 bg-red-50 dark:bg-red-900/20' }}">
            <strong class="{{ session('alert_type') === 'success' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                {{ session('alert_type') === 'success' ? '✓ Success' : '✗ Error' }}
            </strong>
            <p class="mt-1 {{ session('alert_type') === 'success' ? 'text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                {{ session('alert_message') }}
            </p>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-lg mb-5 border-l-4 border-red-500 bg-red-50 dark:bg-red-900/20">
            <strong class="text-red-700 dark:text-red-400">✗ Validation Error</strong>
            <ul class="mt-1 ml-5 list-disc text-red-700 dark:text-red-400">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8">

        <!-- Left Column - Event Info & Current Booking -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Event Info -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 hover:shadow-xl transition-all duration-300">
                <div class="flex flex-col space-y-4">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-indigo-700 text-white text-2xl font-bold flex items-center justify-center ring-4 ring-primary/10 shadow-lg shadow-primary/20">
                                {{ strtoupper(substr(optional($booking->event->user)->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                        <div>
                            <h4 class="text-xs font-semibold text-primary uppercase tracking-wider mb-1">{{ optional($booking->event->user)->name }}</h4>
                            <h2 class="text-xl font-extrabold text-slate-900 dark:text-white tracking-tight">Reschedule: {{ optional($booking->event)->title }}</h2>
                        </div>
                    </div>

                    <div class="inline-flex items-center gap-2 bg-slate-50 dark:bg-slate-700/50 px-4 py-2 rounded-full text-sm font-bold text-slate-700 dark:text-slate-300 w-fit">
                        <span class="material-icons-round text-primary text-lg">schedule</span>
                        <span>{{ optional($booking->event)->duration }} minutes</span>
                    </div>
                </div>
            </div>

            <!-- Current Booking Info -->
            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20 rounded-3xl shadow-lg border border-blue-100 dark:border-blue-800 p-6">
                <div class="flex items-start space-x-3 mb-4">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 dark:bg-primary/20 flex items-center justify-center flex-shrink-0">
                        <span class="material-icons-round text-primary text-xl">event_note</span>
                    </div>
                    <div class="flex-1">
                        <h6 class="text-primary dark:text-indigo-400 font-extrabold text-sm mb-3">Current Booking</h6>
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                <span class="material-icons-round text-base text-slate-500">calendar_today</span>
                                <span class="font-semibold">{{ \Carbon\Carbon::parse($booking->booked_at_date)->format('F j, Y') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                <span class="material-icons-round text-base text-slate-500">schedule</span>
                                <span class="font-semibold">{{ \Carbon\Carbon::parse($booking->booked_at_time, 'UTC')->format('g:i A') }}</span>
                            </div>
                            <div class="flex items-center gap-2 text-slate-700 dark:text-slate-300">
                                <span class="material-icons-round text-base text-slate-500">payments</span>
                                <span class="font-semibold">₹{{ $booking->payment ? $booking->payment->amount : optional($booking->event)->price }}</span>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-blue-200 dark:border-blue-800">
                            @if($booking->payment)
                                <div class="inline-flex items-center gap-2 bg-emerald-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold">
                                    <span class="material-icons-round text-sm">check_circle</span>
                                    Paid
                                </div>
                                <p class="text-emerald-600 dark:text-emerald-400 text-xs font-semibold mt-2">No additional payment required</p>
                            @else
                                <div class="inline-flex items-center gap-2 bg-amber-500 text-white px-3 py-1.5 rounded-lg text-xs font-bold">
                                    <span class="material-icons-round text-sm">warning</span>
                                    Unpaid
                                </div>
                                <p class="text-amber-600 dark:text-amber-400 text-xs font-semibold mt-2">Payment required{{ !$booking->payment && optional($booking->event)->price > 0 ? ' (payment will be required after rescheduling)' : '' }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Reschedule Instructions -->
            <div class="bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 rounded-3xl shadow-lg border border-amber-100 dark:border-amber-800 p-6">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                        <span class="material-icons-round text-amber-600 dark:text-amber-400 text-xl">info</span>
                    </div>
                    <div class="flex-1">
                        <h6 class="text-amber-700 dark:text-amber-400 font-extrabold text-sm mb-2">Want to Change Date/Time?</h6>
                        <p class="text-amber-700 dark:text-amber-300 text-sm leading-relaxed">Choose a new time slot from the calendar to reschedule your booking</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Date & Time Selection -->
        <div class="lg:col-span-3 bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 hover:shadow-xl transition-all duration-300">
            <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <span class="material-icons-round text-primary text-3xl">event</span>
                Select New Date & Time
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

            <div class="grid grid-cols-7 gap-2 mb-6" id="calendar"></div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6" id="timeSlots"></div>

            <div class="flex justify-center mb-6">
                <div class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 font-medium bg-slate-50 dark:bg-slate-700/50 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600">
                    <span class="material-icons-round text-lg">public</span>
                    <span>India Standard Time (IST)</span>
                </div>
            </div>

            <div class="hidden mt-8 bg-gradient-to-br from-primary/5 via-indigo-50 to-purple-50 dark:from-primary/20 dark:via-slate-700 dark:to-purple-900/20 rounded-2xl p-8 text-center border-2 border-primary/20 dark:border-primary/30 shadow-inner" id="confirmPanel">
                <div class="flex items-start space-x-4 mb-6">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-primary to-indigo-700 flex items-center justify-center flex-shrink-0 shadow-lg shadow-primary/30">
                        <span class="material-icons-round text-white text-2xl">check_circle</span>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-bold text-primary uppercase tracking-wider mb-2">New Time Slot Selected</p>
                        <p id="confirmText" class="text-lg font-extrabold text-slate-900 dark:text-white tracking-tight"></p>
                    </div>
                </div>
                <form method="POST" action="{{ route('user.bookings.reschedule', $booking->id) }}" id="rescheduleForm">
                    @csrf
                    <input type="hidden" name="booked_at_date" id="selectedDate">
                    <input type="hidden" name="booked_at_time" id="selectedTime">
                    @if (!$booking->payment && optional($booking->event)->price > 0)
                        <input type="hidden" name="requires_payment" value="1">
                    @endif
                    <button type="submit" class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-indigo-700 hover:opacity-95 text-white font-extrabold py-4 rounded-2xl transition-all duration-300 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40 hover:scale-[1.02]">
                        <span class="material-icons-round">
                            @if (!$booking->payment && optional($booking->event)->price > 0)
                                payment
                            @else
                                event_repeat
                            @endif
                        </span>
                        @if (!$booking->payment && optional($booking->event)->price > 0)
                            Proceed to Payment
                        @else
                            Confirm Reschedule
                        @endif
                    </button>
                </form>
            </div>
        </div>

    </div>
    <!-- End Two Column Layout -->
@endsection

@push('scripts')
    <script>
        const monthLabel = document.getElementById('monthLabel');
        const calendar = document.getElementById('calendar');
        const timeSlotsDiv = document.getElementById('timeSlots');
        const confirmPanel = document.getElementById('confirmPanel');
        const confirmText = document.getElementById('confirmText');
        const rescheduleForm = document.getElementById('rescheduleForm');
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
        const availableDates = @json($availableSlots);
        // Weekday restrictions
        const allowedWeekDays = @json(optional($booking->event)->available_week_days ?? []);
        const allowedWeekDaysLower = (Array.isArray(allowedWeekDays) ? allowedWeekDays.map(d => d.toString().toLowerCase()) : []);
        // Exclusions per date
        const eventExclusions = @json(optional($booking->event)->exclusions->map(function($e){ return ['date' => $e->date->toDateString(), 'exclude_all' => (bool)$e->exclude_all, 'times' => $e->times ?? []]; }) ?? []);

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
                const weekdayNames = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
                const weekday = weekdayNames[dateObj.getDay()];
                const weekdayAllowed = allowedWeekDaysLower.length === 0 || allowedWeekDaysLower.includes(weekday);

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
                            const [eh, em] = (s.end || s.start).split(':').map(Number);
                            const slotEnd = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(),
                                eh, em);
                            return slotEnd.getTime() > now.getTime();
                        });
                    }

                    daySlots.sort((a, b) => timeToMinutes(a.start) - timeToMinutes(b.start));
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
                    const [eh, em] = (s.end || s.start).split(':').map(Number);
                    const slotEnd = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate
                        .getDate(), eh, em);
                    return slotEnd.getTime() > now.getTime();
                });
            }

            daySlots.sort((a, b) => timeToMinutes(a.start) - timeToMinutes(b.start));

            if (daySlots.length === 0) {
                timeSlotsDiv.innerHTML = '<div class="col-span-full text-center text-slate-500 dark:text-slate-400 py-4 font-semibold">No slots available for this day.</div>';
                return;
            }

            daySlots.forEach(slot => {
                const t = document.createElement('div');
                t.className = 'time-slot';
                let [hours, minutes] = slot.start.split(':').map(Number);
                const ampm = hours >= 12 ? 'pm' : 'am';
                const displayHours = hours % 12 || 12;
                t.textContent = `${displayHours}:${minutes.toString().padStart(2, '0')}${ampm}`;
                t.dataset.backendTime = slot.start;
                t.addEventListener('click', () => selectTime(t.dataset.backendTime, t));
                timeSlotsDiv.appendChild(t);
            });
        }

        function selectTime(time, el) {
            document.querySelectorAll('.time-slot').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            selectedTime = time;
            confirmPanel.classList.remove('hidden');

            const [hours, minutes] = time.split(':').map(Number);
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            const displayTime = `${displayHours}:${minutes.toString().padStart(2, '0')}${ampm}`;

            confirmText.textContent = `You've selected ${selectedDate.toDateString()} at ${displayTime}`;

            selectedDateInput.value = formatLocalDate(selectedDate);
            selectedTimeInput.value = time;

            setTimeout(() => {
                confirmPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }, 100);
        }

        document.getElementById('prevMonth').addEventListener('click', () => {
            currentMonth.setMonth(currentMonth.getMonth() - 1);
            renderCalendar(currentMonth);
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentMonth.setMonth(currentMonth.getMonth() + 1);
            renderCalendar(currentMonth);
        });

        // Track reschedule attempt
        rescheduleForm.addEventListener('submit', function() {
            {!! \App\Services\TrackingService::getInlineTrackingCode('BookingRescheduled', [
                'event_name' => optional($booking->event)->title,
                'booking_id' => $booking->id,
                'event_id' => optional($booking->event)->id
            ]) !!}
            {!! \App\Services\TrackingService::getGoogleInlineTrackingCode('booking_rescheduled', [
                'event_name' => optional($booking->event)->title,
                'booking_id' => $booking->id,
                'event_id' => optional($booking->event)->id
            ]) !!}
        });

        // Initial render
        renderCalendar(currentMonth);
    </script>
@endpush

