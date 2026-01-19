# Multi-Page Booking Flow with Meta Pixel Tracking - Implementation Summary

## 🎯 Overview
Successfully split the single-page booking flow into a **multi-page funnel** with Meta Pixel tracking integration and admin configuration panel. This enables proper conversion tracking and funnel analytics.

---

## 📄 New Booking Flow (Multi-Page)

### **Page 1: Slot Selection** 
- **URL:** `/e/{event:slug}`
- **View:** `resources/views/bookings/slot-selection.blade.php`
- **Purpose:** User selects date and time slot
- **Action:** Form GET submission → redirects to Page 2
- **Tracking:** `PageView` (via Meta Pixel base script)

### **Page 2: Details Form**
- **URL:** `/e/{event:slug}/details?date=YYYY-MM-DD&time=HH:MM`
- **View:** `resources/views/bookings/details.blade.php`
- **Purpose:** User enters name, email, phone, DOB, notes
- **Action:** Form POST → creates booking → redirects to Page 3
- **Tracking:** `PageView` + `InitiateCheckout`

### **Page 3: Payment Page**
- **URL:** `/payment/{booking}`
- **View:** `resources/views/payments/show.blade.php` (updated)
- **Purpose:** Payment gateway interface (Razorpay/PayU)
- **Action:** Payment processing → gateway callback → redirects to Page 4
- **Tracking:** `PageView` + `AddPaymentInfo`

### **Page 4: Thank You Page**
- **URL:** `/payment/thankyou/{booking}`
- **View:** `resources/views/payments/thankyou.blade.php` (updated)
- **Purpose:** Booking confirmed message with calendar links
- **Tracking:** `PageView` + **`Purchase` (CONVERSION EVENT)**

---

## 🛠️ Technical Implementation

### **1. Migration & Database**
**File:** `database/migrations/2026_01_19_100922_add_tracking_settings_to_settings_table.php`

Seeds default tracking settings:
- `meta_pixel_enabled` (boolean) - Master enable/disable
- `meta_pixel_id` (string) - Meta Pixel ID
- `meta_event_page_view` (boolean) - Track PageView
- `meta_event_initiate_checkout` (boolean) - Track InitiateCheckout
- `meta_event_add_payment_info` (boolean) - Track AddPaymentInfo
- `meta_event_purchase` (boolean) - Track Purchase (conversion)

**Run migration:**
```bash
php artisan migrate
```

---

### **2. TrackingService (Core Tracking Logic)**
**File:** `app/Services/TrackingService.php`

Key methods:
- `isMetaPixelEnabled()` - Check if tracking is enabled
- `getMetaPixelId()` - Get Pixel ID from settings
- `isEventEnabled($eventName)` - Check if specific event is enabled
- `getBaseScript()` - Generate Meta Pixel base script with PageView
- `getEventScript($eventName, $params)` - Generate event tracking script

**Usage in Blade:**
```blade
{!! \App\Services\TrackingService::getBaseScript() !!}
{!! \App\Services\TrackingService::getEventScript('InitiateCheckout', ['value' => 500, 'currency' => 'INR']) !!}
```

---

### **3. Admin Panel - Tracking Settings**

**Controller:** `app/Http/Controllers/Admin/TrackingSettingsController.php`
**View:** `resources/views/admin/tracking/index.blade.php`
**Route:** `/tracking-settings` (admin only)

Features:
- ✅ Enable/disable Meta Pixel globally
- ✅ Set Meta Pixel ID
- ✅ Toggle individual events (PageView, InitiateCheckout, AddPaymentInfo, Purchase)
- ✅ Help section with setup instructions
- ✅ Accessible from sidebar: **Settings → Tracking Settings**

---

### **4. Updated Controllers**

#### **BookingController** (`app/Http/Controllers/BookingController.php`)
**New Methods:**
- `showDetailsForm(Event $event, Request $request)` - Display details form page
  - Validates date/time query params
  - Returns `bookings.details` view

**Updated Methods:**
- `store()` - Changed from JSON response to **redirect to payment page**
  - Creates pending booking
  - Returns `redirect()->route('payment.page', $booking->id)`

#### **PaymentController** (`app/Http/Controllers/PaymentController.php`)
**Updated Methods:**
- `createOrder()` - Added gateway name to response
  - Returns `['gateway' => 'razorpay'|'payu', ...]`
  - Frontend uses this to determine which payment handler to call

#### **EventController** (`app/Http/Controllers/Admin/EventController.php`)
**Updated Methods:**
- `showPublic()` - Changed view from `book-event` to `bookings.slot-selection`

---

### **5. Routes**

**File:** `routes/web.php`
```php
// Multi-page booking flow
Route::get('/e/{event:slug}', [EventController::class, 'showPublic'])->name('events.show.public');
Route::get('/e/{event:slug}/details', [BookingController::class, 'showDetailsForm'])->name('bookings.details');
Route::post('/e/{event:slug}/book', [BookingController::class, 'store'])->name('bookings.store');
```

**File:** `routes/admin.php`
```php
// Tracking Settings
Route::name('tracking.')->controller(TrackingSettingsController::class)->group(function(){
    Route::get('/tracking-settings', 'index')->name('index');
    Route::put('/tracking-settings', 'update')->name('update');
});
```

---

### **6. Updated Views**

#### **New Views:**
1. `resources/views/bookings/slot-selection.blade.php` - Calendar & time slot selection
2. `resources/views/bookings/details.blade.php` - Booking details form
3. `resources/views/admin/tracking/index.blade.php` - Tracking settings admin panel

#### **Updated Views:**
1. `resources/views/payments/show.blade.php` - Added Meta Pixel tracking + dual gateway support
2. `resources/views/payments/thankyou.blade.php` - Added Purchase event tracking
3. `resources/views/layouts/app.blade.php` - Added `@stack('head')` for tracking scripts
4. `resources/views/layouts/sidebar.blade.php` - Added "Tracking Settings" menu item

---

### **7. Payment Gateway Support**

Both **Razorpay** and **PayU** are fully supported:

**Frontend Payment Handler** (`resources/views/payments/show.blade.php`):
```javascript
// Detects active gateway from createOrder response
if (data.gateway === 'payu') {
    handlePayUPayment(data);
} else {
    handleRazorpayPayment(data);
}
```

**Razorpay Flow:**
- Opens Razorpay checkout modal
- On success: verifies payment via `/verify-payment`
- Redirects to thank you page

**PayU Flow:**
- Creates form with PayU parameters
- Submits to PayU gateway
- PayU redirects back to success/failure URL
- Verifies payment via `/verify-payment`
- Redirects to thank you page

---

## 🎨 Design Consistency

All pages use the **same CSS** (`public/style.css`) for consistent look:
- Slot selection page matches original design
- Details form uses same button styles, inputs, and colors
- Responsive for mobile/tablet
- Blue theme (`#2563eb`) throughout

---

## 📊 Meta Pixel Event Tracking

### **Event Parameters Sent:**

#### **InitiateCheckout** (Details Page)
```javascript
{
    content_name: 'Event Title',
    content_ids: [event_id],
    value: 500,
    currency: 'INR'
}
```

#### **AddPaymentInfo** (Payment Page)
```javascript
{
    content_name: 'Event Title',
    content_ids: [event_id],
    value: 500,
    currency: 'INR'
}
```

#### **Purchase** (Thank You Page - CONVERSION)
```javascript
{
    content_name: 'Event Title',
    content_ids: [event_id],
    value: 500,
    currency: 'INR',
    transaction_id: 'payment_transaction_id'
}
```

---

## ✅ Testing Checklist

### **1. Setup Meta Pixel**
- [ ] Login to admin panel: `/tracking-settings`
- [ ] Enable Meta Pixel tracking
- [ ] Enter your Meta Pixel ID (16-digit number)
- [ ] Enable all events (PageView, InitiateCheckout, AddPaymentInfo, Purchase)
- [ ] Save settings

### **2. Test Booking Flow**
- [ ] Visit event page: `/e/{event-slug}`
- [ ] Select date and time slot
- [ ] Click "Next: Enter Details"
- [ ] Fill in name, email, phone
- [ ] Click "Continue to Payment"
- [ ] Complete payment (Razorpay or PayU)
- [ ] Verify redirect to thank you page

### **3. Verify Meta Pixel Events** (Use Meta Pixel Helper Extension)
- [ ] `PageView` fires on slot selection page
- [ ] `InitiateCheckout` fires on details form page
- [ ] `AddPaymentInfo` fires on payment page
- [ ] `Purchase` fires on thank you page (with transaction_id)

### **4. Test Both Payment Gateways**
- [ ] Switch to Razorpay in admin → payment gateway settings
- [ ] Complete a booking with Razorpay
- [ ] Switch to PayU in admin → payment gateway settings
- [ ] Complete a booking with PayU
- [ ] Verify both redirect to thank you page correctly

### **5. Test Edge Cases**
- [ ] Navigate back button (should work properly)
- [ ] Try accessing details page without date/time (should redirect back)
- [ ] Try booking same slot twice (should show error)
- [ ] Disable Meta Pixel → verify scripts don't load
- [ ] Disable specific event → verify that event doesn't fire

---

## 🚀 Next Steps (Optional Enhancements)

1. **Google Analytics Integration** - Add GA4 tracking similar to Meta Pixel
2. **A/B Testing** - Test different checkout flows
3. **Abandoned Cart** - Track users who leave at payment page
4. **Email Remarketing** - Capture emails at details form for remarketing
5. **Conversion API** - Send server-side events to Meta for better tracking

---

## 📝 Important Notes

1. **Migration Required:** Run `php artisan migrate` to seed tracking settings
2. **Queue Workers:** Ensure queue workers are running for email notifications
3. **Google Calendar:** Requires Google OAuth setup for event owners
4. **Database Connection:** Ensure database is running before testing
5. **Meta Pixel Helper:** Install Chrome extension for debugging tracking events
6. **Testing Mode:** Use Meta Pixel in test mode initially to verify events

---

## 🐛 Troubleshooting

**Issue:** Meta Pixel not loading
- **Solution:** Check admin panel → enable Meta Pixel + verify Pixel ID

**Issue:** Events not firing
- **Solution:** Check admin panel → ensure specific events are enabled

**Issue:** Payment redirect fails
- **Solution:** Check payment gateway settings + verify callback URLs

**Issue:** Database connection error
- **Solution:** Start database server + verify `.env` DB credentials

---

## 📞 Support

If you encounter issues:
1. Check `storage/logs/laravel.log` for errors
2. Use Meta Pixel Helper to debug tracking
3. Verify all migrations ran successfully
4. Ensure `.env` variables are correct

---

**Implementation Date:** January 19, 2026  
**Developer:** GitHub Copilot  
**Framework:** Laravel 9.x + Blade Templates + Meta Pixel
