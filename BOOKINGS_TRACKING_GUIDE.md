# User Bookings & Reschedule Tracking Implementation Guide

## Overview
This document describes the standalone user bookings and reschedule pages with Meta Pixel tracking integration, completing the full booking lifecycle tracking system.

## Architecture

### Pages Converted to Standalone
1. **User Bookings Dashboard** (`resources/views/user/bookings/index.blade.php`)
   - Displays all user bookings with status badges
   - Includes reschedule modal with calendar interface
   - Tracks "ViewBookings" custom event

2. **Reschedule Page** (`resources/views/user/bookings/reschedule.blade.php`)
   - Dedicated reschedule form with date/time selectors
   - Shows current booking details
   - Tracks "BookingRescheduled" custom event

### Key Features

#### Standalone Architecture
- **No Layout Dependency**: Both pages use complete HTML structure without `@extends('layouts.app')`
- **Guest Friendly**: Can be accessed by authenticated users or guests with booking links
- **Bootstrap 5.1.3**: Consistent styling with other booking flow pages
- **Meta Pixel Integration**: Tracking scripts injected conditionally based on admin settings

#### Design System
- **Primary Color**: `#2563eb` (blue) for branding
- **Accent Color**: `#ffc107` (yellow/gold) for actions and highlights
- **Dark Theme**: Consistent with payment and thank you pages
- **Responsive Grid**: Calendar and time slot grids adapt to screen size

## Implementation Details

### 1. User Bookings Dashboard

#### Structure
```blade
<!DOCTYPE html>
<html>
<head>
    <!-- Meta Pixel Base Script -->
    {!! \App\Services\TrackingService::getBaseScript() !!}

    <!-- Custom CSS for booking table, badges, calendar, time slots -->
    <style>
        /* Status badges */
        .badge-confirmed { background: #10b981; }
        .badge-pending { background: #f59e0b; }
        .badge-cancelled { background: #ef4444; }

        /* Calendar grid */
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); }

        /* Time slots grid */
        .time-slots-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
    </style>
</head>
<body>
    <!-- Top Navigation -->
    <nav style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
        <div class="container">
            <a href="/">Home</a>
            <a href="{{ route('user.transactions') }}">Transactions</a>
            @auth
                <form method="POST" action="{{ route('logout') }}">...</form>
            @endauth
        </div>
    </nav>

    <!-- Bookings Table -->
    <table>
        @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->event->title }}</td>
                <td><span class="badge badge-{{ $booking->status }}">{{ $booking->status }}</span></td>
                <td>
                    <button class="reschedule-btn"
                        data-booking-id="{{ $booking->id }}"
                        data-event-slug="{{ $booking->event->slug }}"
                        data-event-title="{{ $booking->event->title }}"
                        data-has-payment="{{ $booking->payment ? 'true' : 'false' }}">
                        Reschedule
                    </button>
                </td>
            </tr>
        @endforeach
    </table>

    <!-- Reschedule Modal -->
    <div class="modal" id="rescheduleModal">
        <div class="modal-content">
            <div class="row">
                <div class="col-md-4">
                    <!-- Event details, current booking info -->
                </div>
                <div class="col-md-8">
                    <!-- Calendar grid -->
                    <div id="calendarModal" class="calendar-grid"></div>

                    <!-- Time slots grid -->
                    <div id="timeSlotsModal" class="time-slots-grid"></div>

                    <!-- Confirm panel -->
                    <div id="confirmPanelModal">
                        <button id="confirmReschedule">Confirm Reschedule</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script src="https://secure.payu.in/js/web/checkout.js"></script>
    <script>
        // Track ViewBookings event
        if (typeof fbq === 'function') {
            fbq('trackCustom', 'ViewBookings', {
                user_id: '{{ auth()->id() }}'
            });
        }

        // Calendar rendering logic
        function renderCalendar(date) { ... }

        // Time slot selection logic
        function renderTimeSlots() { ... }

        // Reschedule confirmation logic
        document.getElementById('confirmReschedule').addEventListener('click', async () => {
            if (hasPayment) {
                // Direct reschedule API call
                await fetch(`/user/bookings/${bookingId}/reschedule`, { ... });
            } else {
                // Create payment order first
                const orderData = await fetch('/create-order', { ... });

                // Store reschedule data in session
                sessionStorage.setItem('pendingReschedule', JSON.stringify(payload));

                // Route to payment gateway
                if (activeGateway === 'razorpay') {
                    handleRazorpayPayment(orderData, booking);
                } else if (activeGateway === 'payu') {
                    handlePayUPayment(orderData, booking);
                }
            }
        });
    </script>
</body>
</html>
```

#### Key Data Attributes
```html
<button class="reschedule-btn"
    data-booking-id="123"
    data-event-slug="consultation-call"
    data-event-title="Consultation Call"
    data-event-price="1500"
    data-event-duration="30"
    data-event-description="One-on-one consultation"
    data-event-owner="John Doe"
    data-has-payment="true"
    data-current-date="2026-01-25"
    data-current-time="14:00:00"
    data-booker-name="Jane Smith"
    data-booker-email="jane@example.com">
```

### 2. Reschedule Page

#### Structure
```blade
<!DOCTYPE html>
<html>
<head>
    <!-- Meta Pixel Base Script -->
    {!! \App\Services\TrackingService::getBaseScript() !!}

    <style>
        /* Form styling */
        .form-section { background: #1a1a1a; padding: 30px; border-radius: 12px; }

        /* Info panels */
        .info-panel { background: #2a2a2a; padding: 20px; border-left: 4px solid #2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Current Booking Info Panel -->
        <div class="info-panel">
            <h6 style="color: #60a5fa;">Current Booking Details</h6>
            <p>📅 {{ $booking->booked_at->format('F j, Y') }}</p>
            <p>🕒 {{ $booking->booked_at->format('g:i A') }}</p>
            <p>💰 {{ $booking->event->price > 0 ? '₹' . $booking->event->price : 'Free' }}</p>
        </div>

        <!-- Reschedule Form -->
        <form method="POST" action="{{ route('user.bookings.reschedule', $booking->id) }}">
            @csrf

            <div class="form-group">
                <label>New Date</label>
                <select name="booked_at_date">
                    @foreach($availableSlots as $slot)
                        <option value="{{ $slot['date'] }}">{{ Carbon\Carbon::parse($slot['date'])->format('F j, Y') }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>New Time</label>
                <select name="booked_at_time">
                    <option value="">Select a time...</option>
                    <!-- Dynamically populated via JavaScript -->
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Confirm Reschedule</button>
        </form>
    </div>

    <script>
        // Dynamic time slot loading based on selected date
        const dateSel = document.querySelector('select[name="booked_at_date"]');
        const timeSel = document.querySelector('select[name="booked_at_time"]');

        dateSel.addEventListener('change', function() {
            const selectedDate = this.value;
            const slot = slots.find(s => s.date === selectedDate);

            timeSel.innerHTML = '<option value="">Select a time...</option>';

            if (slot && slot.timeslots) {
                slot.timeslots.forEach(t => {
                    const option = document.createElement('option');
                    option.value = t.start;
                    option.text = formatTime(t.start);
                    timeSel.appendChild(option);
                });
            }
        });

        // Track reschedule attempt
        document.querySelector('form').addEventListener('submit', function() {
            if (typeof fbq === 'function') {
                fbq('trackCustom', 'BookingRescheduled', {
                    event_name: '{{ $booking->event->title }}',
                    booking_id: '{{ $booking->id }}'
                });
            }
        });
    </script>
</body>
</html>
```

## Meta Pixel Events

### Standard Events
1. **PageView** - Fires on every page load (all booking pages)
2. **InitiateCheckout** - User starts booking details form
3. **AddPaymentInfo** - User reaches payment page
4. **Purchase** - Payment successful (CONVERSION)

### Custom Events (New)
5. **ViewBookings** - User accesses bookings dashboard
   ```javascript
   fbq('trackCustom', 'ViewBookings', {
       user_id: '123'
   });
   ```

6. **BookingRescheduled** - User submits reschedule form
   ```javascript
   fbq('trackCustom', 'BookingRescheduled', {
       event_name: 'Consultation Call',
       booking_id: '456'
   });
   ```

## Controller Integration

### BookingController

#### Reschedule Method
```php
public function reschedule(Request $request, Booking $booking)
{
    // Validate user owns booking
    $this->authorize('update', $booking);

    // Validate new date/time
    $validated = $request->validate([
        'booked_at_date' => 'required|date|after_or_equal:today',
        'booked_at_time' => 'required',
    ]);

    // Update booking
    $booking->update([
        'booked_at' => $validated['booked_at_date'] . ' ' . $validated['booked_at_time'],
        'status' => 'confirmed',
    ]);

    // Send notifications
    $booking->user->notify(new BookingRescheduledNotification($booking));
    $booking->event->user->notify(new BookingRescheduledNotification($booking));

    // Redirect with success message
    return redirect()->route('user.bookings.index')
        ->with('alert_type', 'success')
        ->with('alert_message', 'Booking rescheduled successfully!');
}
```

## Admin Configuration

### Tracking Settings Panel
Location: `/admin/tracking`

Configuration includes:
- Enable/disable Meta Pixel globally
- Set Meta Pixel ID
- Toggle individual events:
  - ✅ PageView
  - ✅ InitiateCheckout
  - ✅ AddPaymentInfo
  - ✅ Purchase
  - ✅ ViewBookings (Custom)
  - ✅ BookingRescheduled (Custom)

## Payment Flow Integration

### Paid Reschedule
If booking already has payment:
1. User selects new date/time
2. Clicks "Confirm Reschedule"
3. Direct API call to `/user/bookings/{id}/reschedule`
4. Success message, page reload

### Unpaid Reschedule
If booking doesn't have payment:
1. User selects new date/time
2. Clicks "Confirm Reschedule"
3. Create payment order via `/create-order`
4. Store reschedule data in `sessionStorage`
5. Redirect to payment gateway (Razorpay/PayU)
6. After successful payment verification
7. Execute reschedule with stored data
8. Clear `sessionStorage`

## API Routes

### Reschedule Data Endpoint
```php
// routes/api.php
Route::get('/events/{event:slug}/reschedule-data', function($slug) {
    $event = Event::where('slug', $slug)->firstOrFail();

    return response()->json([
        'availableSlots' => $event->generateAvailableSlots(),
        'exclusions' => $event->exclusions,
        'allowedWeekDays' => $event->available_week_days,
        'bookedSlots' => $event->bookedSlots(),
    ]);
});
```

## Testing Checklist

### User Bookings Page
- [ ] Page loads without authentication errors
- [ ] All bookings display with correct status badges
- [ ] Status colors match design system (green/yellow/red)
- [ ] ViewBookings Meta Pixel event fires on page load
- [ ] Reschedule button shows correct booking data attributes
- [ ] Modal opens with correct event details
- [ ] Calendar renders available dates correctly
- [ ] Time slots populate based on selected date
- [ ] Paid bookings reschedule without payment
- [ ] Unpaid bookings redirect to payment gateway

### Reschedule Page
- [ ] Page loads with correct booking details
- [ ] Current booking info displays correctly
- [ ] Date dropdown shows available slots
- [ ] Time dropdown updates when date changes
- [ ] Form validation works (required fields)
- [ ] BookingRescheduled event fires on submit
- [ ] Success redirect after reschedule
- [ ] Notifications sent to user and organizer

### Meta Pixel Tracking
- [ ] Install Meta Pixel Helper extension
- [ ] Verify PageView fires on all pages
- [ ] Verify ViewBookings fires on bookings dashboard
- [ ] Verify BookingRescheduled fires on reschedule submit
- [ ] Check Events Manager for custom events
- [ ] Verify event parameters are captured

## Troubleshooting

### Modal Not Opening
**Issue**: Reschedule modal doesn't open when button clicked

**Solution**: Check JavaScript console for errors. Ensure:
- Bootstrap JS loaded: `bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js`
- Modal ID matches: `id="rescheduleModal"`
- Button has correct data attributes
- `new bootstrap.Modal(document.getElementById('rescheduleModal'))` executes

### Time Slots Not Loading
**Issue**: Time dropdown stays empty after selecting date

**Solution**: Check:
- API endpoint returns data: `/api/events/{slug}/reschedule-data`
- `availableSlots` array structure matches expected format
- Date format matches: `YYYY-MM-DD`
- JavaScript event listener attached to date selector

### Payment Gateway Not Triggering
**Issue**: Unpaid reschedule doesn't redirect to payment

**Solution**: Verify:
- `data-has-payment="false"` on reschedule button
- `/create-order` endpoint returns valid order data
- Razorpay/PayU scripts loaded
- `handleRazorpayPayment()` or `handlePayUPayment()` called

### Meta Pixel Not Firing
**Issue**: Events not showing in Facebook Events Manager

**Solution**: Check:
- Meta Pixel enabled in admin settings: `/admin/tracking`
- Pixel ID correct (16-digit number)
- Individual events enabled in settings
- `fbq` function defined (check browser console)
- Ad blockers disabled during testing

## File Structure

```
app/
├── Services/
│   └── TrackingService.php (updated with custom events)
├── Http/Controllers/
│   ├── BookingController.php (reschedule method)
│   └── Admin/TrackingSettingsController.php
resources/views/
├── user/bookings/
│   ├── index.blade.php (standalone)
│   └── reschedule.blade.php (standalone)
└── admin/tracking/
    └── index.blade.php (updated with custom events)
routes/
├── web.php (reschedule routes)
└── api.php (reschedule-data endpoint)
```

## Next Steps

1. **Test Complete Flow**:
   - Create a booking
   - Access bookings dashboard
   - Click reschedule
   - Select new date/time
   - Complete payment (if needed)
   - Verify reschedule success

2. **Verify Meta Pixel**:
   - Install Meta Pixel Helper
   - Go through complete booking flow
   - Check all events fire correctly
   - Verify custom events in Events Manager

3. **Monitor Production**:
   - Check Laravel logs for errors
   - Monitor payment gateway webhooks
   - Track conversion rates in Meta Ads Manager
   - Analyze funnel drop-off points

## Related Documentation
- [IMPLEMENTATION_SUMMARY.md](./IMPLEMENTATION_SUMMARY.md) - Original booking flow implementation
- [FLOW_DIAGRAM.md](./FLOW_DIAGRAM.md) - Visual flow diagrams
- [SETUP_GUIDE.md](./SETUP_GUIDE.md) - Initial setup instructions
- [Laravel Notifications Docs](https://laravel.com/docs/9.x/notifications)
- [Meta Pixel Standard Events](https://developers.facebook.com/docs/meta-pixel/reference)
