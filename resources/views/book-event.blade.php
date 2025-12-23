<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $event->user->name }} | {{ $event->title }} Meeting</title>
    <link rel="stylesheet" href="{{ asset('style.css') }}">
</head>

<body>

    <div id="loader" style="display: none;">
        Please wait...
    </div>

    <div class="container">
        <div class="info">
            <h4>{{ $event->user->name }}</h4>
            <h2>{{ $event->title }}</h2>
            <div class="meta">⏱️ {{ $event->duration }} minutes</div>
            <p>{!! $event->description !!}</p>
        </div>

        <div class="schedule" id="step1">
            <div class="calendar-header">
                <button id="prevMonth">←</button>
                <h3 id="monthLabel"></h3>
                <button id="nextMonth">→</button>
            </div>

            <div class="calendar" id="calendar"></div>
            <div class="time-slots" id="timeSlots"></div>
            <div class="timezone">🌍 India Standard Time (IST)</div>

            <div class="confirm-panel" id="confirmPanel">
                <p id="confirmText"></p>
                <button class="btn-confirm" id="proceedDetails">Confirm Meeting</button>
            </div>
        </div>

        <div class="schedule details-form" id="step2">
            <h3>Enter Your Details</h3>
            <input type="text" id="name" value="{{ auth()->user() ? auth()->user()->name : '' }}" placeholder="Full Name" required>
            <input type="email" id="email" value="{{ auth()->user() ? auth()->user()->email : '' }}" placeholder="Email Address" required>
            <input type="tel" id="phone" value="{{ auth()->user() ? auth()->user()->phone : '' }}" placeholder="Phone Number" required>
            <textarea id="notes" rows="3" placeholder="Any specific agenda or notes?"></textarea>
            <button class="btn-submit" id="submitDetails">Schedule Meeting</button>
        </div>

        <div class="schedule confirmation" id="step3">
            <h3>🎉 Meeting Scheduled!</h3>
            <p id="summary"></p>
            <p>You'll receive your web conferencing details shortly via email.</p>
            <p>Or <a href="{{ route('admin.login') }}">login here</a></p>
        </div>
    </div>

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
            loader.style.display = 'flex';
        }

        function hideLoader() {
            loader.style.display = 'none';
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
                div.classList.add('day-name');
                div.textContent = d;
                calendar.appendChild(div);
            });

            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);
            const startDay = (firstDay.getDay() + 6) % 7;

            for (let i = 0; i < startDay; i++) calendar.appendChild(document.createElement('div'));

            for (let i = 1; i <= lastDay.getDate(); i++) {
                const dateObj = new Date(year, month, i);
                const dateStr = formatLocalDate(dateObj);
                const dateEl = document.createElement('div');
                dateEl.classList.add('date');
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
            confirmPanel.style.display = "none";
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
                timeSlotsDiv.textContent = "No slots available for this day.";
                return;
            }

            daySlots.forEach(slot => {
                const t = document.createElement('div');
                t.classList.add('time-slot');

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
            confirmPanel.style.display = "block";
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
            step1.style.display = 'none';
            step2.style.display = 'block';
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
                        step2.style.display = 'none';
                        step3.style.display = 'block';
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
                <input type="hidden" name="phone" value="">
                <input type="hidden" name="surl" value="{{ route('payu.success') }}">
                <input type="hidden" name="furl" value="{{ route('payu.failure') }}">
                <input type="hidden" name="hash" value="${orderData.hash}">
                <input type="hidden" name="booking_id" value="${bookingId}">
            `;
            document.body.appendChild(form);
            form.submit();
        }

        renderCalendar(currentMonth);
    </script>
</body>

</html>
