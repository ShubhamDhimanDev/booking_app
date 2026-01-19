<!DOCTYPE html>
<html lang="en" class="scroll-smooth">

<head>
    <script>
        // Detect system theme preference
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        // Listen for system theme changes
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            if (e.matches) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        });
    </script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->user->name }} | {{ $event->title }} Meeting</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#6366f1',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900 min-h-screen antialiased transition-colors duration-300" style="font-family: 'Plus Jakarta Sans', sans-serif;">

    <div id="loader" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-white/80 dark:bg-slate-900/80 backdrop-blur-md">
        <div class="bg-white dark:bg-slate-800 rounded-3xl p-8 shadow-2xl text-center border border-slate-200 dark:border-slate-700">
            <div class="w-16 h-16 border-4 border-slate-200 dark:border-slate-700 border-t-primary rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-lg font-bold text-slate-900 dark:text-white">Please wait...</p>
        </div>
    </div>

    <div class="container max-w-6xl mx-auto px-4 py-8 sm:py-12">
        <div class="info bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 sm:p-8 mb-8 transition-all duration-300">
            <h4 class="text-sm font-semibold text-primary dark:text-primary uppercase tracking-wider mb-2">{{ $event->user->name }}</h4>
            <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 dark:text-white tracking-tight mb-4">{{ $event->title }}</h2>
            <div class="meta inline-flex items-center gap-2 bg-slate-50 dark:bg-slate-700/50 px-4 py-2 rounded-full text-sm font-bold text-slate-700 dark:text-slate-300 mb-4">
                <span>⏱️</span>
                <span>{{ $event->duration }} minutes</span>
            </div>
            <p class="text-slate-600 dark:text-slate-400 leading-relaxed">{!! $event->description !!}</p>
        </div>

        <div class="schedule bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 sm:p-8 transition-all duration-300" id="step1">
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

            <div class="confirm-panel hidden mt-8 bg-gradient-to-br from-primary/5 via-indigo-50 to-purple-50 dark:from-primary/20 dark:via-slate-700 dark:to-purple-900/20 rounded-2xl p-6 border-2 border-primary/20 dark:border-primary/30 shadow-lg" id="confirmPanel">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <span class="material-icons-round text-primary text-2xl">check_circle</span>
                    <p id="confirmText" class="text-slate-900 dark:text-white font-bold text-center text-lg"></p>
                </div>
                <button class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-indigo-700 hover:opacity-95 text-white font-extrabold py-4 rounded-2xl transition-all duration-300 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40" id="proceedDetails">
                    <span class="material-icons-round">arrow_forward</span>
                    Confirm & Continue
                </button>
            </div>
        </div>

        <div class="schedule details-form hidden bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 sm:p-8 transition-all duration-300" id="step2">
            <h3 class="text-2xl font-extrabold text-slate-900 dark:text-white mb-6 flex items-center gap-2">
                <span class="material-icons-round text-primary text-3xl">badge</span>
                Enter Your Details
            </h3>
            <div class="space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round text-primary text-sm">person</span>
                            Full Name *
                        </span>
                    </label>
                    <input type="text" id="name" value="{{ auth()->user() ? auth()->user()->name : '' }}" placeholder="Enter your full name" required class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round text-primary text-sm">email</span>
                            Email Address *
                        </span>
                    </label>
                    <input type="email" id="email" value="{{ auth()->user() ? auth()->user()->email : '' }}" placeholder="your@email.com" required class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round text-primary text-sm">phone</span>
                            Phone Number
                        </span>
                    </label>
                    <input type="tel" id="phone" value="{{ auth()->user() ? auth()->user()->phone : '' }}" placeholder="+91 XXXXXXXXXX" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">
                        <span class="flex items-center gap-2">
                            <span class="material-icons-round text-primary text-sm">notes</span>
                            Additional Notes
                        </span>
                    </label>
                    <textarea id="notes" rows="3" placeholder="Any specific agenda or requirements?" class="w-full px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent transition-all resize-none"></textarea>
                </div>
                <button class="w-full inline-flex items-center justify-center gap-2 bg-gradient-to-r from-primary to-indigo-700 hover:opacity-95 text-white font-extrabold py-4 rounded-2xl transition-all duration-300 shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40" id="submitDetails">
                    <span class="material-icons-round">payment</span>
                    Continue to Payment
                </button>
            </div>
        </div>

        <div class="schedule confirmation hidden bg-white dark:bg-slate-800 rounded-3xl shadow-lg border border-slate-100 dark:border-slate-700 p-6 sm:p-8 text-center transition-all duration-300" id="step3">
            <div class="w-24 h-24 bg-gradient-to-br from-emerald-500 to-green-600 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg shadow-emerald-500/30">
                <span class="material-icons-round text-white" style="font-size: 64px;">check_circle</span>
            </div>
            <h3 class="text-3xl font-extrabold text-slate-900 dark:text-white mb-4">Meeting Scheduled!</h3>
            <p id="summary" class="text-lg text-slate-700 dark:text-slate-300 font-medium mb-4"></p>
            <p class="text-slate-600 dark:text-slate-400 mb-4">You'll receive your web conferencing details shortly via email.</p>
            <p class="text-sm text-slate-500 dark:text-slate-400">
                Or <a href="{{ route('login') }}" class="text-primary hover:underline font-semibold">login here</a>
            </p>
        </div>
    </div>

    <style>
        /* Custom calendar styles with dark mode */
        .day-name {
            @apply text-xs font-bold text-slate-500 dark:text-slate-400 text-center py-2;
        }

        .date {
            @apply aspect-square flex items-center justify-center rounded-xl text-sm font-bold cursor-pointer transition-all;
            @apply text-slate-700 dark:text-slate-300 bg-white dark:bg-slate-700 border border-slate-200 dark:border-slate-600;
            @apply hover:bg-primary hover:text-white hover:border-primary hover:shadow-md;
        }

        .date.active {
            @apply bg-gradient-to-br from-primary to-indigo-700 text-white border-primary shadow-lg shadow-primary/30 scale-105;
        }

        .date.disabled {
            @apply text-slate-300 dark:text-slate-600 bg-slate-50 dark:bg-slate-800 border-slate-100 dark:border-slate-700;
            @apply cursor-not-allowed hover:bg-slate-50 dark:hover:bg-slate-800 hover:text-slate-300 dark:hover:text-slate-600 hover:border-slate-100 dark:hover:border-slate-700 hover:shadow-none;
        }

        .time-slot {
            @apply px-4 py-3 rounded-xl border-2 border-slate-200 dark:border-slate-600 text-sm font-bold text-slate-700 dark:text-slate-300;
            @apply bg-white dark:bg-slate-700 cursor-pointer transition-all;
            @apply hover:border-primary hover:text-primary hover:bg-primary/5 dark:hover:bg-primary/10 hover:shadow-md;
        }

        .time-slot.selected {
            @apply bg-gradient-to-br from-primary to-indigo-700 border-primary text-white shadow-lg shadow-primary/30 scale-105;
        }
    </style>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <!-- PayU Script -->
    <script src="https://secure.payu.in/js/web/checkout.js"></script>
    <script>
        const activeGateway = @json($activeGateway ?? 'razorpay');
        const gatewayConfig = @json($gatewayConfig ?? []);
        const monthLabel = document.getElementById('monthLabel');
        const timeSlotsDiv = document.getElementById('timeSlots');
        const confirmPanel = document.getElementById('confirmPanel');
        const confirmText = document.getElementById('confirmText');
        const step1 = document.getElementById('step1');
        const step2 = document.getElementById('step2');
        const step3 = document.getElementById('step3');
        const summary = document.getElementById('summary');

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
        // Weekday restrictions set on the event (e.g. ["monday","wednesday"]) - empty means all days
        const allowedWeekDays = @json($event->available_week_days ?? []);
        const allowedWeekDaysLower = (Array.isArray(allowedWeekDays) ? allowedWeekDays.map(d => d.toString().toLowerCase()) : []);
        // Exclusions per date (from server): [{date: 'YYYY-MM-DD', exclude_all: bool, times: ['HH:MM']}]
        const eventExclusions = @json($event->exclusions->map(function($e){
            return ['date' => $e->date->toDateString(), 'exclude_all' => (bool)$e->exclude_all, 'times' => $e->times ?? []];
        }));

        const loader = document.getElementById('loader');

        function showLoader() {
            loader.classList.remove('hidden');
        }

        function hideLoader() {
            loader.classList.add('hidden');
        }

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
                // determine weekday name for this date (sunday..saturday)
                const weekdayNames = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
                const weekday = weekdayNames[dateObj.getDay()];
                const weekdayAllowed = allowedWeekDaysLower.length === 0 ? true : allowedWeekDaysLower.includes(weekday);
                // If there is an availableDate, apply exclusions and skip past times to see if any timeslots remain
                let hasSlots = false;
                if (availableDate && Array.isArray(availableDate.timeslots)) {
                    const daySlotsRaw = availableDate.timeslots;
                    const exclusion = (Array.isArray(eventExclusions) ? eventExclusions.find(x => x.date === dateStr) : null);
                    let daySlots = daySlotsRaw;
                    if (exclusion) {
                        if (exclusion.exclude_all) {
                            daySlots = [];
                        } else if (Array.isArray(exclusion.times) && exclusion.times.length) {
                            daySlots = daySlotsRaw.filter(s => !exclusion.times.includes(s.start));
                        }
                    }

                    // remove times that are in the past for the current day by checking slot end
                    const todayStrInner = formatLocalDate(new Date());
                    if (dateStr === todayStrInner) {
                        const now = new Date();
                        daySlots = daySlots.filter(s => {
                            const [eh, em] = (s.end || s.start).split(':').map(Number);
                            const slotEnd = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), eh, em);
                            return slotEnd.getTime() > now.getTime(); // keep slots whose end is in the future
                        });
                    }

                    // ensure availability check uses sorted slots (early -> late)
                    daySlots.sort((a,b) => timeToMinutes(a.start) - timeToMinutes(b.start));
                    hasSlots = daySlots.length > 0;
                }

                // disable past calendar days as well
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
            // Apply exclusions for this date (exclude_all or specific times)
            const exclusion = (Array.isArray(eventExclusions) ? eventExclusions.find(x => x.date === dateStr) : null);
            let daySlots = daySlotsRaw;
            if (exclusion) {
                if (exclusion.exclude_all) {
                    daySlots = [];
                } else if (Array.isArray(exclusion.times) && exclusion.times.length) {
                    daySlots = daySlotsRaw.filter(s => !exclusion.times.includes(s.start));
                }
            }

            // additionally filter past slots for today's selected date
            const todayStr = formatLocalDate(new Date());
            if (dateStr === todayStr) {
                const now = new Date();
                daySlots = daySlots.filter(s => {
                    const [eh, em] = (s.end || s.start).split(':').map(Number);
                    const slotEnd = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(), eh, em);
                    return slotEnd.getTime() > now.getTime();
                });
            }
            // sort early -> late
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

                t.dataset.backendTime = slot.start; // Keep HH:MM for backend
                t.addEventListener('click', () => selectTime(t.dataset.backendTime, t));
                timeSlotsDiv.appendChild(t);
            });
        }

        function selectTime(time, el) {
            document.querySelectorAll('.time-slot').forEach(t => t.classList.remove('selected'));
            el.classList.add('selected');
            selectedTime = time;
            confirmPanel.classList.remove('hidden');
            confirmText.textContent = `You’ve selected ${selectedDate.toDateString()} at ${el.textContent}`;
        }

        document.getElementById('prevMonth').addEventListener('click', () => {
            currentMonth.setMonth(currentMonth.getMonth() - 1);
            renderCalendar(currentMonth);
        });

        document.getElementById('nextMonth').addEventListener('click', () => {
            currentMonth.setMonth(currentMonth.getMonth() + 1);
            renderCalendar(currentMonth);
        });

        document.getElementById('proceedDetails').addEventListener('click', () => {
            step1.classList.add('hidden');
            step2.classList.remove('hidden');
        });

        document.getElementById('submitDetails').addEventListener('click', async () => {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const phone = document.getElementById('phone').value.trim();
            const notes = document.getElementById('notes').value.trim();

            if (!name || !email) return alert("Please fill in your name and email!");

                if (!selectedDate || !selectedTime) {
                    hideLoader();
                    return alert('Please select a date and time before submitting.');
                }

                const bookingData = {
                    booker_name: name,
                    booker_email: email,
                    booked_at_date: formatLocalDate(selectedDate),
                    booked_at_time: selectedTime,
                    phone,
                    notes
                };

            try {
                showLoader();
                // 1. Create booking first
                const bookingResponse = await fetch(`/e/{{ $event->slug }}/book`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(bookingData)
                });

                if (!bookingResponse.ok) {
                    let err = null;
                    try { err = await bookingResponse.json(); } catch(e) { /* ignore parse errors */ }
                    hideLoader();
                    return alert((err && err.error) ? err.error : "Failed to create booking. Try another timeslot.");
                }

                const bookingResult = await bookingResponse.json();
                if (!bookingResult.id) {
                    hideLoader();
                    return alert("Failed to create booking. Try another timeslot.");
                }

                const bookingId = bookingResult.id;

                // 2. Create payment order with active gateway
                const orderResponse = await fetch('/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        amount: {{ $event->price }},
                        booking_id: bookingId,
                        first_name: name,
                        email: email,
                        product_info: '{{ $event->title }}'
                    })
                });

                const orderData = await orderResponse.json();
                if (!orderData.success) {
                    hideLoader();
                    return alert("Unable to create payment order. Try again.");
                }

                hideLoader();

                // 3. Route to appropriate payment gateway
                if (activeGateway === 'razorpay') {
                    handleRazorpayPayment(orderData, bookingId, name, email, phone);
                } else if (activeGateway === 'payu') {
                    handlePayUPayment(orderData, bookingId, name, email);
                }

            } catch (error) {
                hideLoader();
                console.error(error);
                alert('Something went wrong. Please try again.');
            }
        });

        // Razorpay Payment Handler
        async function handleRazorpayPayment(orderData, bookingId, name, email, phone) {
            const options = {
                key: orderData.key,
                amount: orderData.amount * 100,
                currency: 'INR',
                name: '{{ config('app.name') }}',
                description: '{{ $event->title }}',
                order_id: orderData.order_id,
                handler: async function(response) {
                    showLoader();
                    const verifyResponse = await fetch('/verify-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            razorpay_order_id: response.razorpay_order_id,
                            razorpay_payment_id: response.razorpay_payment_id,
                            razorpay_signature: response.razorpay_signature,
                            booking_id: bookingId,
                            amount: orderData.amount
                        })
                    });

                    const result = await verifyResponse.json();
                    hideLoader();
                    if (result.success) {
                        step2.classList.add('hidden');
                        step3.classList.remove('hidden');
                        summary.textContent = `${name}, your meeting is scheduled for ${selectedDate.toDateString()} at ${selectedTime}. Payment successful!`;
                    } else alert('Payment verification failed. Please contact support.');
                },
                prefill: { name, email, contact: phone },
                theme: { color: '#3399cc' }
            };

            new Razorpay(options).open();
        }

        // PayU Payment Handler
        async function handlePayUPayment(orderData, bookingId, name, email) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = orderData.payu_url;
            form.innerHTML = `
                <input type="hidden" name="key" value="${orderData.merchant_key}">
                <input type="hidden" name="txnid" value="${orderData.txn_id}">
                <input type="hidden" name="amount" value="${orderData.amount}">
                <input type="hidden" name="productinfo" value="${orderData.product_info}">
                <input type="hidden" name="firstname" value="${orderData.first_name}">
                <input type="hidden" name="email" value="${orderData.email}">
                <input type="hidden" name="phone" value="${orderData.phone || ''}">
                <input type="hidden" name="surl" value="{{ route('payu.success') }}">
                <input type="hidden" name="furl" value="{{ route('payu.failure') }}">
                <input type="hidden" name="hash" value="${orderData.hash}">
                <input type="hidden" name="service_provider" value="payu_paisa">
                <input type="hidden" name="mid" value="${orderData.merchant_id}">
                <input type="hidden" name="booking_id" value="${bookingId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        renderCalendar(currentMonth);
    </script>
</body>

</html>
