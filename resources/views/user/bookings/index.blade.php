@extends('admin.layout.app')

@section('title', 'My Bookings')

@section('content')

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">My Bookings</h4>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Event</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $b)
                        <tr>
                            <td>{{ $b->id }}</td>
                            <td>{{ optional($b->event)->title }}</td>
                            <td>{{ \Carbon\Carbon::parse($b->booked_at_date)->format('d M Y') }}</td>
                            <td>{{ $b->booked_at_time }}</td>
                            <td>{{ ucfirst($b->status) }}</td>
                            <td>
                                @if($b->status === 'confirmed')
                                    <a href="{{ route('payment.page', [$b->id]) }}" class="btn btn-sm btn-outline-light">View</a>
                                @endif
                                <button class="btn btn-sm btn-primary reschedule-btn"
                                    data-booking-id="{{ $b->id }}"
                                    data-event-slug="{{ optional($b->event)->slug }}"
                                    data-event-title="{{ optional($b->event)->title }}"
                                    data-event-price="{{ optional($b->event)->price ?? 0 }}"
                                    data-event-duration="{{ optional($b->event)->duration ?? 30 }}"
                                    data-event-description="{{ optional($b->event)->description ?? '' }}"
                                    data-event-owner="{{ optional($b->event->user)->name ?? '' }}"
                                    data-has-payment="{{ $b->payment ? 'true' : 'false' }}"
                                    data-current-date="{{ $b->booked_at_date }}"
                                    data-current-time="{{ $b->booked_at_time }}"
                                    data-booker-name="{{ $b->booker_name }}"
                                    data-booker-email="{{ $b->booker_email }}">
                                    Reschedule
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No bookings found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content bg-dark text-white">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">Reschedule: <span id="modalEventTitle"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="p-3">
                            <h6 id="eventOwnerName" class="text-warning mb-2"></h6>
                            <h5 id="eventTitleDetail" class="mb-3"></h5>
                            <p class="mb-2"><strong>⏱️ Duration:</strong> <span id="eventDuration"></span> minutes</p>
                            <div id="eventDescriptionDetail" class="small text-muted mb-3"></div>
                            <hr class="border-secondary">
                            <h6 class="text-info">Current Booking</h6>
                            <p id="currentBookingInfo" class="small"></p>
                            <p id="paymentStatusInfo" class="small"></p>
                        </div>
                    </div>
                    <div class="col-md-8 border-start border-secondary">
                        <div class="p-3">
                            <div class="calendar-header d-flex justify-content-between align-items-center mb-3">
                                <button id="prevMonthModal" class="btn btn-sm btn-outline-warning">←</button>
                                <h6 id="monthLabelModal" class="mb-0 text-warning"></h6>
                                <button id="nextMonthModal" class="btn btn-sm btn-outline-warning">→</button>
                            </div>
                            <div id="calendarModal" class="calendar-grid mb-3"></div>
                            <div id="timeSlotsModal" class="time-slots-grid mt-3"></div>
                            <div class="timezone small text-muted text-center my-2">🌍 India Standard Time (IST)</div>
                            <div id="confirmPanelModal" class="mt-3" style="display:none;">
                                <div class="alert alert-success">
                                    <p id="confirmTextModal" class="mb-2"></p>
                                </div>
                                <button class="btn btn-warning w-100 fw-bold" id="confirmReschedule">Confirm Reschedule</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<style>
.calendar-grid {
    display: grid !important;
    grid-template-columns: repeat(7, 1fr) !important;
    gap: 5px !important;
    text-align: center;
    width: 100%;
}
.calendar-grid > div {
    padding: 10px 5px;
    border-radius: 4px;
    cursor: pointer;
    min-height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.calendar-grid .day-name {
    font-weight: bold;
    font-size: 12px;
    color: #aaa;
    cursor: default;
    background: transparent !important;
}
.calendar-grid .date {
    background: #2a2a2a;
    color: #fff;
    transition: all 0.2s;
    font-weight: 500;
}
.calendar-grid .date:hover:not(.disabled) {
    background: #3a3a3a;
    transform: scale(1.05);
}
.calendar-grid .date.active {
    background: #ffc107 !important;
    color: #000 !important;
    font-weight: bold;
}
.calendar-grid .date.disabled {
    background: #1a1a1a;
    color: #555;
    cursor: not-allowed;
    opacity: 0.5;
}
.time-slots-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)) !important;
    gap: 10px !important;
    max-height: 300px;
    overflow-y: auto;
    width: 100%;
}
.time-slot {
    padding: 10px;
    border: 1px solid #444;
    border-radius: 4px;
    text-align: center;
    cursor: pointer;
    background: #2a2a2a;
    transition: all 0.2s;
    min-height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.time-slot:hover {
    background: #3a3a3a;
    border-color: #ffc107;
}
.time-slot.selected {
    background: #ffc107 !important;
    color: #000 !important;
    font-weight: bold;
    border-color: #ffc107;
}
</style>
@endpush

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://secure.payu.in/js/web/checkout.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = new bootstrap.Modal(document.getElementById('rescheduleModal'));
    let currentBooking = null;
    let currentMonth = new Date();
    let selectedDate = null;
    let selectedTime = null;
    let availableSlots = [];
    let eventExclusions = [];
    let allowedWeekDays = [];
    let bookedSlots = {};

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

    document.querySelectorAll('.reschedule-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const bookingId = this.dataset.bookingId;
            const eventSlug = this.dataset.eventSlug;
            const eventTitle = this.dataset.eventTitle;
            const eventPrice = this.dataset.eventPrice;
            const eventDuration = this.dataset.eventDuration;
            const eventDescription = this.dataset.eventDescription;
            const eventOwner = this.dataset.eventOwner;
            const hasPayment = this.dataset.hasPayment === 'true';
            const currentDate = this.dataset.currentDate;
            const currentTime = this.dataset.currentTime;
            const bookerName = this.dataset.bookerName;
            const bookerEmail = this.dataset.bookerEmail;

            currentBooking = {
                id: bookingId,
                eventSlug,
                eventTitle,
                eventPrice: parseFloat(eventPrice),
                eventDuration,
                hasPayment,
                currentDate,
                currentTime,
                bookerName,
                bookerEmail
            };

            // Load event data
            try {
                const response = await fetch(`/api/events/${eventSlug}/reschedule-data`);
                const data = await response.json();

                availableSlots = data.availableSlots || [];
                eventExclusions = data.exclusions || [];
                allowedWeekDays = (data.allowedWeekDays || []).map(d => d.toLowerCase());
                bookedSlots = data.bookedSlots || {};

                // Update modal content
                document.getElementById('modalEventTitle').textContent = eventTitle;
                document.getElementById('eventOwnerName').textContent = eventOwner;
                document.getElementById('eventTitleDetail').textContent = eventTitle;
                document.getElementById('eventDuration').textContent = eventDuration;
                document.getElementById('eventDescriptionDetail').innerHTML = eventDescription || 'No description';

                const formattedDate = new Date(currentDate).toLocaleDateString('en-US', {
                    year: 'numeric', month: 'short', day: 'numeric'
                });
                document.getElementById('currentBookingInfo').innerHTML =
                    `<strong>Date:</strong> ${formattedDate}<br><strong>Time:</strong> ${currentTime}`;

                document.getElementById('paymentStatusInfo').innerHTML = hasPayment
                    ? '<span class="badge bg-success">✓ Paid</span> No additional payment required'
                    : '<span class="badge bg-warning text-dark">⚠ Unpaid</span> Payment required after rescheduling';

                currentMonth = new Date();
                selectedDate = null;
                selectedTime = null;
                renderCalendar(currentMonth);

                modal.show();
            } catch (error) {
                console.error(error);
                alert('Failed to load booking data');
            }
        });
    });

    function renderCalendar(date) {
        const calendar = document.getElementById('calendarModal');
        calendar.innerHTML = "";
        const year = date.getFullYear();
        const month = date.getMonth();
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        document.getElementById('monthLabelModal').textContent = `${monthNames[month]} ${year}`;

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

            const availableDate = availableSlots.find(d => d.date === dateStr);
            const weekdayNames = ['sunday','monday','tuesday','wednesday','thursday','friday','saturday'];
            const weekday = weekdayNames[dateObj.getDay()];
            const weekdayAllowed = allowedWeekDays.length === 0 ? true : allowedWeekDays.includes(weekday);

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
                        const slotEnd = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate(), eh, em);
                        return slotEnd.getTime() > now.getTime();
                    });
                }

                daySlots.sort((a,b) => timeToMinutes(a.start) - timeToMinutes(b.start));
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
        document.querySelectorAll('#calendarModal .date').forEach(d => d.classList.remove('active'));
        el.classList.add('active');
        selectedDate = date;
        renderTimeSlots();
    }

    function renderTimeSlots() {
        const container = document.getElementById('timeSlotsModal');
        container.innerHTML = "";
        document.getElementById('confirmPanelModal').style.display = "none";
        if (!selectedDate) return;

        const dateStr = formatLocalDate(selectedDate);
        const daySlotsRaw = availableSlots.find(d => d.date === dateStr)?.timeslots || [];
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
                const slotEnd = new Date(selectedDate.getFullYear(), selectedDate.getMonth(), selectedDate.getDate(), eh, em);
                return slotEnd.getTime() > now.getTime();
            });
        }

        daySlots.sort((a,b) => timeToMinutes(a.start) - timeToMinutes(b.start));

        if (daySlots.length === 0) {
            container.innerHTML = '<p class="text-muted text-center">No slots available for this day.</p>';
            return;
        }

        daySlots.forEach(slot => {
            const t = document.createElement('div');
            t.classList.add('time-slot');
            let [hours, minutes] = slot.start.split(':').map(Number);
            const ampm = hours >= 12 ? 'pm' : 'am';
            const displayHours = hours % 12 || 12;
            t.textContent = `${displayHours}:${minutes.toString().padStart(2,'0')}${ampm}`;
            t.dataset.backendTime = slot.start;
            t.addEventListener('click', () => selectTime(t.dataset.backendTime, t));
            container.appendChild(t);
        });
    }

    function selectTime(time, el) {
        document.querySelectorAll('.time-slot').forEach(t => t.classList.remove('selected'));
        el.classList.add('selected');
        selectedTime = time;
        document.getElementById('confirmPanelModal').style.display = "block";

        const [hours, minutes] = time.split(':').map(Number);
        const ampm = hours >= 12 ? 'PM' : 'AM';
        const displayHours = hours % 12 || 12;
        const displayTime = `${displayHours}:${minutes.toString().padStart(2,'0')}${ampm}`;

        document.getElementById('confirmTextModal').textContent =
            `📅 ${selectedDate.toDateString()} at ${displayTime}`;
    }

    document.getElementById('prevMonthModal').addEventListener('click', () => {
        currentMonth.setMonth(currentMonth.getMonth() - 1);
        renderCalendar(currentMonth);
    });

    document.getElementById('nextMonthModal').addEventListener('click', () => {
        currentMonth.setMonth(currentMonth.getMonth() + 1);
        renderCalendar(currentMonth);
    });

    document.getElementById('confirmReschedule').addEventListener('click', async () => {
        if (!selectedDate || !selectedTime || !currentBooking) {
            return alert('Please select a date and time');
        }

        const payload = {
            booked_at_date: formatLocalDate(selectedDate),
            booked_at_time: selectedTime
        };

        try {
            // If already paid, just reschedule
            if (currentBooking.hasPayment) {
                const response = await fetch(`/user/bookings/${currentBooking.id}/reschedule`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                if (response.ok) {
                    alert('Booking rescheduled successfully!');
                    modal.hide();
                    location.reload();
                } else {
                    const err = await response.json();
                    alert(err.message || 'Failed to reschedule');
                }
            } else {
                // Need payment - create payment order
                const orderResponse = await fetch('/create-order', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        amount: currentBooking.eventPrice,
                        booking_id: currentBooking.id,
                        first_name: currentBooking.bookerName,
                        email: currentBooking.bookerEmail,
                        product_info: currentBooking.eventTitle
                    })
                });

                const orderData = await orderResponse.json();
                if (!orderData.success) {
                    return alert("Unable to create payment order. Try again.");
                }

                // Store reschedule data and proceed to payment
                sessionStorage.setItem('pendingReschedule', JSON.stringify(payload));

                // Route to appropriate gateway
                const activeGateway = orderData.gateway || 'razorpay';

                if (activeGateway === 'razorpay') {
                    handleRazorpayPayment(orderData, currentBooking);
                } else if (activeGateway === 'payu') {
                    handlePayUPayment(orderData, currentBooking);
                }
            }
        } catch (error) {
            console.error(error);
            alert('Something went wrong. Please try again.');
        }
    });

    async function handleRazorpayPayment(orderData, booking) {
        const options = {
            key: orderData.key,
            amount: orderData.amount * 100,
            currency: 'INR',
            name: '{{ config("app.name") }}',
            description: booking.eventTitle,
            order_id: orderData.order_id,
            handler: async function(response) {
                const verifyResponse = await fetch('/verify-payment', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        razorpay_order_id: response.razorpay_order_id,
                        razorpay_payment_id: response.razorpay_payment_id,
                        razorpay_signature: response.razorpay_signature,
                        booking_id: booking.id,
                        amount: orderData.amount
                    })
                });

                const result = await verifyResponse.json();
                if (result.success) {
                    // Now reschedule
                    const rescheduleData = JSON.parse(sessionStorage.getItem('pendingReschedule'));
                    await fetch(`/user/bookings/${booking.id}/reschedule`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify(rescheduleData)
                    });
                    sessionStorage.removeItem('pendingReschedule');
                    alert('Payment successful and booking rescheduled!');
                    location.reload();
                } else {
                    alert('Payment verification failed. Please contact support.');
                }
            },
            prefill: { name: booking.bookerName, email: booking.bookerEmail },
            theme: { color: '#3399cc' }
        };
        new Razorpay(options).open();
    }

    async function handlePayUPayment(orderData, booking) {
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
            <input type="hidden" name="booking_id" value="${booking.id}">
        `;
        document.body.appendChild(form);
        form.submit();
    }
});
</script>
@endpush
