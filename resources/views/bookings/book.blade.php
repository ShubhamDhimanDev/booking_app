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

{{-- layouts.app renders the loader overlay when this section exists --}}
@section('loader', '1')
@section('loader-text', 'Processing your booking...')

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

    /* Leave room for the sticky header when scrolling to the details form */
    #detailsBlock {
        scroll-margin-top: 6rem;
    }

    #submitBtn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        box-shadow: none;
        transform: none;
    }
</style>
@endsection

@section('content')

        @include('bookings.partials.progress')

        @if(isset($isFollowUp) && $isFollowUp && isset($customPrice))
            @include('bookings.partials.followup-banner')
        @endif

        @php
            $bookingPrice = (isset($isFollowUp) && $isFollowUp && isset($customPrice)) ? $customPrice : $event->price;
        @endphp

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-8">

            <!-- Left Column - Event Info -->
            <div class="lg:col-span-2">
                @include('bookings.partials.event-card')
            </div>

            <!-- Right Column - Date, Time & Details -->
            <div class="lg:col-span-3 schedule bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 hover:shadow-xl transition-all duration-300">
            <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <span class="material-icons-round text-primary text-3xl">event</span>
                Select Date & Time
            </h3>

            <div class="calendar-header flex items-center justify-between mb-6 bg-slate-50 dark:bg-slate-700/50 p-4 rounded-2xl">
                <button type="button" id="prevMonth" class="w-11 h-11 rounded-xl bg-white dark:bg-slate-600 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-700 dark:text-slate-300 font-bold transition-all flex items-center justify-center shadow-sm hover:shadow-md">
                    <span class="material-icons-round">chevron_left</span>
                </button>
                <h3 id="monthLabel" class="text-xl font-extrabold text-slate-900 dark:text-white"></h3>
                <button type="button" id="nextMonth" class="w-11 h-11 rounded-xl bg-white dark:bg-slate-600 hover:bg-primary hover:text-white dark:hover:bg-primary text-slate-700 dark:text-slate-300 font-bold transition-all flex items-center justify-center shadow-sm hover:shadow-md">
                    <span class="material-icons-round">chevron_right</span>
                </button>
            </div>

            <div class="calendar grid grid-cols-7 gap-2 mb-6" id="calendar"></div>
            <div class="time-slots grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mb-6" id="timeSlots"></div>
            <div class="flex justify-center">
                <div class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 font-medium bg-slate-50 dark:bg-slate-700/50 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600">
                    <span class="material-icons-round text-lg">public</span>
                    <span id="tzLabel">Times shown in your local timezone</span>
                </div>
            </div>

            <!-- Details form (always visible; submit unlocks once a slot is chosen) -->
            <div id="detailsBlock" class="mt-8 pt-8 border-t border-slate-200 dark:border-slate-700">
                <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="material-icons-round text-primary text-3xl">badge</span>
                    Enter Your Details
                </h3>

                @if($errors->any())
                    <div id="formErrors" class="bg-red-50 dark:bg-red-900/30 border-l-4 border-red-500 text-red-800 dark:text-red-300 px-6 py-4 rounded-xl mb-6 shadow-sm">
                        <div class="flex items-start space-x-3">
                            <span class="material-icons-round text-red-500 dark:text-red-400">error</span>
                            <div class="flex-1">
                                <strong class="font-bold block mb-2">Error:</strong>
                                <ul class="list-disc list-inside space-y-1 text-sm">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mb-6 flex items-start gap-3 bg-gradient-to-br from-primary/5 via-indigo-50 to-purple-50 dark:from-primary/20 dark:via-slate-700 dark:to-purple-900/20 rounded-2xl p-4 border-2 border-primary/20 dark:border-primary/30">
                    <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-primary to-indigo-700 flex items-center justify-center flex-shrink-0 shadow-lg shadow-primary/30">
                        <span class="material-icons-round text-white">check_circle</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-primary uppercase tracking-wider mb-1">Selected Time Slot</p>
                        <p id="confirmText" class="text-base font-extrabold text-slate-900 dark:text-white tracking-tight">No time selected yet</p>
                    </div>
                    <a href="#calendar" id="changeSlot" class="hidden text-sm font-bold text-primary hover:underline whitespace-nowrap">Change</a>
                </div>

                <form method="POST" action="{{ route('bookings.store', $event->slug) }}" id="bookingForm" class="space-y-5">
                    @csrf
                    <input type="hidden" name="booked_at_date" id="selectedDate" value="{{ old('booked_at_date') }}">
                    <input type="hidden" name="booked_at_time" id="selectedTime" value="{{ old('booked_at_time') }}">
                    <input type="hidden" name="timezone" id="selectedTimezone">
                    @if(isset($isFollowUp) && $isFollowUp && isset($invite))
                        <input type="hidden" name="followup_token" value="{{ $invite->token }}">
                    @endif

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                            <span class="flex items-center gap-2">
                                <span class="material-icons-round text-primary text-sm">person</span>
                                Full Name *
                            </span>
                        </label>
                        <input type="text" name="booker_name" value="{{ old('booker_name', auth()->user()->name ?? '') }}" placeholder="Enter your full name" required
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                            <span class="flex items-center gap-2">
                                <span class="material-icons-round text-primary text-sm">email</span>
                                Email Address *
                            </span>
                        </label>
                        <input type="email" name="booker_email" value="{{ old('booker_email', auth()->user()->email ?? '') }}" placeholder="your@email.com" required
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                            <span class="flex items-center gap-2">
                                <span class="material-icons-round text-primary text-sm">phone</span>
                                Phone Number *
                            </span>
                        </label>
                        <input type="tel" name="phone" value="{{ old('phone', auth()->user()->phone ?? '') }}" placeholder="+91 XXXXXXXXXX" required
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                            <span class="flex items-center gap-2">
                                <span class="material-icons-round text-primary text-sm">notes</span>
                                Additional Notes
                            </span>
                        </label>
                        <textarea name="additional_notes" rows="3" placeholder="Any specific agenda or requirements?"
                            class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none">{{ old('additional_notes') }}</textarea>
                    </div>

                    <div class="pt-2">
                        <p id="submitHint" class="text-sm text-center text-slate-500 dark:text-slate-400 mb-3">Choose a date and time above to continue.</p>
                        <button type="submit" id="submitBtn" disabled class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-indigo-700 hover:opacity-95 text-white font-extrabold py-4 rounded-2xl transition-all duration-300 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40">
                            <span class="material-icons-round">arrow_forward</span>
                            {{ (float) $bookingPrice == 0 ? 'Confirm Booking' : 'Continue to Payment' }}
                        </button>
                    </div>
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
        const detailsBlock = document.getElementById('detailsBlock');
        const confirmText = document.getElementById('confirmText');
        const changeSlot = document.getElementById('changeSlot');
        const bookingForm = document.getElementById('bookingForm');
        const submitBtn = document.getElementById('submitBtn');
        const submitHint = document.getElementById('submitHint');
        const selectedDateInput = document.getElementById('selectedDate');
        const selectedTimeInput = document.getElementById('selectedTime');

        document.getElementById('selectedTimezone').value = window.__visitorTz || 'Asia/Kolkata';

        // Fires the checkout-start events once per page view, the first time a slot is chosen.
        const trackCheckoutStart = (function () {
            let fired = false;
            return function () {
                if (fired) return;
                fired = true;
                {!! \App\Services\TrackingService::getInlineTrackingCode('InitiateCheckout', [
                    'content_name' => $event->title,
                    'content_ids' => [$event->id],
                    'value' => $event->price,
                    'currency' => $event->currency ?? 'INR',
                ], $event) !!}
                {!! \App\Services\TrackingService::getGoogleInlineTrackingCode('begin_checkout', [
                    'event_name' => $event->title,
                    'event_id' => $event->id,
                    'value' => $event->price,
                    'currency' => $event->currency ?? 'INR',
                ], $event) !!}
            };
        })();

        let currentMonth = new Date();
        currentMonth.setDate(1); // avoid month-overflow when stepping from the 29th-31st
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
                dateEl.dataset.date = dateStr;
                if (selectedDate && dateStr === formatLocalDate(selectedDate)) dateEl.classList.add('active');

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
            clearSelectedSlot();
            renderTimeSlots();
            // On narrow screens the slot list can sit below the fold.
            timeSlotsDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }

        // Drops the chosen slot (a new date was picked) and locks the submit button again.
        function clearSelectedSlot() {
            selectedTime = null;
            selectedDateInput.value = '';
            selectedTimeInput.value = '';
            confirmText.textContent = 'No time selected yet';
            changeSlot.classList.add('hidden');
            submitBtn.disabled = true;
            submitHint.classList.remove('hidden');
        }

        function renderTimeSlots() {
            timeSlotsDiv.innerHTML = "";
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

                const conv = window.convertIstToVisitorTz(dateStr, slot.start);
                t.textContent = conv.local;
                t.title = `${conv.ist} (India time)`;

                t.dataset.backendTime = slot.start;
                t.addEventListener('click', () => selectTime(slot.start, t, true));
                timeSlotsDiv.appendChild(t);
            });
        }

        // `scroll` is false when restoring a slot after a failed submit.
        function selectTime(time, el, scroll) {
            document.querySelectorAll('.time-slot').forEach(t => t.classList.remove('selected'));
            el.classList.add('selected');
            selectedTime = time;

            selectedDateInput.value = formatLocalDate(selectedDate);
            selectedTimeInput.value = time;

            const conv = window.convertIstToVisitorTz(formatLocalDate(selectedDate), time);
            const prettyDate = new Date(`${formatLocalDate(selectedDate)}T${time}:00+05:30`).toLocaleDateString('en-US', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: window.__visitorTz || 'Asia/Kolkata',
            });
            confirmText.textContent = `${prettyDate} at ${conv.local}`;
            changeSlot.classList.remove('hidden');
            submitBtn.disabled = false;
            submitHint.classList.add('hidden');

            trackCheckoutStart();

            if (scroll) {
                detailsBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }

        changeSlot.addEventListener('click', (e) => {
            e.preventDefault();
            calendar.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        // Never post without a slot, and stop double submits (each submit creates a pending booking).
        bookingForm.addEventListener('submit', (e) => {
            if (!selectedDateInput.value || !selectedTimeInput.value) {
                e.preventDefault();
                return;
            }
            submitBtn.disabled = true;
            document.getElementById('loader')?.classList.remove('hidden');
        });

        document.getElementById('prevMonth').addEventListener('click', () => {
            currentMonth.setMonth(currentMonth.getMonth() - 1);
            renderCalendar(currentMonth);
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentMonth.setMonth(currentMonth.getMonth() + 1);
            renderCalendar(currentMonth);
        });

        renderCalendar(currentMonth);

        // Coming back via the browser's back button (page restored from cache): unlock submit again.
        window.addEventListener('pageshow', (e) => {
            if (e.persisted && selectedDateInput.value && selectedTimeInput.value) {
                submitBtn.disabled = false;
            }
        });

        // After a failed submit, put the visitor's slot back (if it is still selectable) and show the errors.
        (function restoreSlot() {
            const oldDate = @json(old('booked_at_date'));
            const oldTime = @json(old('booked_at_time'));
            const hasErrors = {{ $errors->any() ? 'true' : 'false' }};

            // The hidden inputs are pre-filled from old(); clear them until a real slot is re-selected.
            clearSelectedSlot();

            if (oldDate && oldTime && /^\d{4}-\d{2}-\d{2}$/.test(oldDate)) {
                const [y, m, d] = oldDate.split('-').map(Number);
                currentMonth = new Date(y, m - 1, 1);
                renderCalendar(currentMonth);
                const dateEl = calendar.querySelector(`.date[data-date="${oldDate}"]:not(.disabled)`);
                if (dateEl) {
                    selectDate(new Date(y, m - 1, d), dateEl);
                    const slotEl = Array.from(timeSlotsDiv.children).find(t => t.dataset.backendTime === oldTime);
                    if (slotEl) selectTime(oldTime, slotEl, false);
                }
            }

            if (hasErrors) {
                const target = document.getElementById('formErrors') || detailsBlock;
                setTimeout(() => target.scrollIntoView({ behavior: 'smooth', block: 'start' }), 100);
            }
        })();
    </script>
@endpush
