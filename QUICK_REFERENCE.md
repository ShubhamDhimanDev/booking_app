# Quick Reference: User Bookings & Reschedule Tracking

## URLs

### User Pages
- **Bookings Dashboard**: `/user/bookings`
- **Reschedule Page**: `/user/bookings/{id}/reschedule`
- **Transactions**: `/user/transactions`

### Admin Pages
- **Tracking Settings**: `/admin/tracking`
- **Events Management**: `/admin/events`
- **Payment Gateway Config**: `/admin/payment-gateway`

### API Endpoints
- **Reschedule Data**: `/api/events/{slug}/reschedule-data`
- **Create Order**: `/create-order`
- **Verify Payment**: `/verify-payment`

## Meta Pixel Events

| Event | Type | Fires When | Parameters |
|-------|------|-----------|------------|
| PageView | Standard | Every page load | (none) |
| InitiateCheckout | Standard | Details form page | `event_name`, `event_price` |
| AddPaymentInfo | Standard | Payment page | `booking_id`, `amount` |
| Purchase | Standard | Thank you page | `booking_id`, `amount`, `currency` |
| ViewBookings | Custom | Bookings dashboard | `user_id` |
| BookingRescheduled | Custom | Reschedule submit | `event_name`, `booking_id` |

## Status Badge Colors

```css
.badge-confirmed { background: #10b981; }  /* Green */
.badge-pending { background: #f59e0b; }    /* Yellow */
.badge-cancelled { background: #ef4444; }  /* Red */
.badge-declined { background: #ef4444; }   /* Red */
.badge-rescheduled { background: #3b82f6; } /* Blue */
```

## JavaScript Functions

### Bookings Dashboard (index.blade.php)
```javascript
renderCalendar(date)           // Renders calendar grid
selectDate(date, element)      // Handles date selection
renderTimeSlots()              // Populates time slot grid
selectTime(time, element)      // Handles time selection
handleRazorpayPayment(order)   // Processes Razorpay payment
handlePayUPayment(order)       // Processes PayU payment
```

### Reschedule Page (reschedule.blade.php)
```javascript
// Date selector change event
dateSel.addEventListener('change', function() {
    // Populates time slots based on selected date
});

// Form submit tracking
form.addEventListener('submit', function() {
    fbq('trackCustom', 'BookingRescheduled', {...});
});
```

## Data Attributes

### Reschedule Button
```html
data-booking-id       // Booking ID (integer)
data-event-slug       // Event slug (string)
data-event-title      // Event title (string)
data-event-price      // Event price (decimal)
data-event-duration   // Duration in minutes (integer)
data-event-description // Event description (HTML)
data-event-owner      // Owner name (string)
data-has-payment      // "true" or "false" (string)
data-current-date     // Current booking date (YYYY-MM-DD)
data-current-time     // Current booking time (HH:MM:SS)
data-booker-name      // Booker name (string)
data-booker-email     // Booker email (string)
```

## CSS Classes

### Calendar Grid
```css
.calendar-grid          // Main calendar container
.calendar-grid .date    // Individual date cells
.date.active           // Selected date
.date.disabled         // Unavailable date
.day-name              // Weekday headers
```

### Time Slots
```css
.time-slots-grid       // Time slots container
.time-slot             // Individual time slot
.time-slot.selected    // Selected time slot
```

### Modal
```css
#rescheduleModal       // Modal container
#calendarModal         // Calendar grid inside modal
#timeSlotsModal        // Time slots grid inside modal
#confirmPanelModal     // Confirmation panel
```

## Controller Methods

### BookingController
```php
index()                     // List all bookings
showRescheduleForm($id)     // Show reschedule page
reschedule($id)             // Process reschedule
```

### PaymentController
```php
showPaymentPage($id)        // Display payment page
createOrder()               // Create payment order
verifyPayment()             // Verify payment success
thankYouPage($id)           // Display thank you page
```

## Authorization

### Policies
```php
BookingPolicy::update()     // Check if user can update booking
EventPolicy::view()         // Check if user can view event
```

### Middleware
```php
auth                        // Requires authentication
IsAdmin                     // Requires admin role
LinkedWithGoogleMiddleware  // Requires Google Calendar link
```

## Validation Rules

### Reschedule Form
```php
'booked_at_date' => 'required|date|after_or_equal:today'
'booked_at_time' => 'required'
```

### Payment Order
```php
'amount' => 'required|numeric|min:1'
'booking_id' => 'required|exists:bookings,id'
'first_name' => 'required|string'
'email' => 'required|email'
```

## Notifications

```php
BookingCreatedNotification      // New booking confirmation
BookingRescheduledNotification  // Reschedule confirmation
BookingDeclinedNotification     // Booking declined
BookingReminderNotification     // Upcoming booking reminder
```

## Session Storage

### Reschedule Flow
```javascript
// Store reschedule data before payment
sessionStorage.setItem('pendingReschedule', JSON.stringify({
    booked_at_date: '2026-01-25',
    booked_at_time: '14:00:00'
}));

// Retrieve after payment
const rescheduleData = JSON.parse(sessionStorage.getItem('pendingReschedule'));
sessionStorage.removeItem('pendingReschedule');
```

## Common Issues & Fixes

### Issue: Modal doesn't open
**Fix**: Check Bootstrap JS loaded and modal ID matches
```html
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
```

### Issue: Time slots not loading
**Fix**: Verify API endpoint returns data
```javascript
console.log(await fetch('/api/events/event-slug/reschedule-data').then(r => r.json()));
```

### Issue: Meta Pixel not firing
**Fix**: Check admin settings and Pixel ID
```javascript
// Browser console
console.log(typeof fbq); // Should be "function"
```

### Issue: Payment not triggering
**Fix**: Verify payment gateway scripts loaded
```html
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script src="https://secure.payu.in/js/web/checkout.js"></script>
```

## Testing Commands

### Check Routes
```bash
php artisan route:list | grep booking
```

### Clear Cache
```bash
php artisan optimize:clear
php artisan view:clear
php artisan config:clear
```

### Test Meta Pixel
```javascript
// Browser console
fbq('trackCustom', 'ViewBookings', {user_id: '123'});
```

### Check Logs
```bash
tail -f storage/logs/laravel.log
```

## Database Queries

### Get User Bookings
```php
Booking::with(['event', 'payment'])
    ->where('user_id', auth()->id())
    ->latest()
    ->paginate(10);
```

### Get Available Slots
```php
Event::where('slug', $slug)
    ->with(['exclusions', 'bookings'])
    ->firstOrFail()
    ->generateAvailableSlots();
```

## Environment Variables

### Required
```env
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_DATABASE=meetflow

RAZORPAY_KEY=
RAZORPAY_SECRET=

PAYU_MERCHANT_KEY=
PAYU_MERCHANT_SALT=

GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

### Optional
```env
MAIL_MAILER=smtp
QUEUE_CONNECTION=database
```

## File Locations

### Views
- `resources/views/user/bookings/index.blade.php`
- `resources/views/user/bookings/reschedule.blade.php`
- `resources/views/admin/tracking/index.blade.php`

### Controllers
- `app/Http/Controllers/BookingController.php`
- `app/Http/Controllers/PaymentController.php`
- `app/Http/Controllers/Admin/TrackingSettingsController.php`

### Services
- `app/Services/TrackingService.php`
- `app/Services/PaymentGatewayManager.php`
- `app/Services/RazorpayService.php`
- `app/Services/PayUService.php`

### Models
- `app/Models/Booking.php`
- `app/Models/Event.php`
- `app/Models/Payment.php`
- `app/Models/Setting.php`

### Routes
- `routes/web.php` (user routes)
- `routes/admin.php` (admin routes)
- `routes/api.php` (API routes)

## Git Commands

### Commit Changes
```bash
git add .
git commit -m "Add standalone user bookings and reschedule pages with Meta Pixel tracking"
git push origin main
```

### View Changes
```bash
git diff HEAD~1
git log --oneline -5
```

---

**Last Updated**: January 2026
**Version**: 1.0
**Author**: MeetFlow Development Team
