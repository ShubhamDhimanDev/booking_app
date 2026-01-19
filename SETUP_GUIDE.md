# 🚀 Setup & Testing Guide

## Prerequisites

- Laravel 9.x application running
- Database server running (MySQL/PostgreSQL)
- Composer installed
- NPM installed (for frontend assets)

---

## 📦 Installation Steps

### 1. Run Database Migration

```bash
php artisan migrate
```

This will create the tracking settings in your database with default values:
- Meta Pixel: Disabled
- All events: Enabled (but won't fire until Pixel is enabled)

### 2. Clear Cache (Optional but Recommended)

```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### 3. Verify Routes

```bash
php artisan route:list | grep -E "events.show.public|bookings.details|bookings.store|tracking"
```

You should see:
- `GET /e/{event:slug}` → Slot selection page
- `GET /e/{event:slug}/details` → Details form
- `POST /e/{event:slug}/book` → Create booking
- `GET /tracking-settings` → Admin tracking settings
- `PUT /tracking-settings` → Update tracking settings

---

## 🎯 Configure Meta Pixel

### Step 1: Get Your Meta Pixel ID

1. Go to [Meta Events Manager](https://business.facebook.com/events_manager)
2. Select your Pixel (or create a new one)
3. Copy the Pixel ID (16-digit number like `1234567890123456`)

### Step 2: Configure in Admin Panel

1. Login to your Laravel application as admin
2. Navigate to **Tracking Settings** in the sidebar
3. Enable "Meta Pixel Tracking"
4. Paste your Pixel ID
5. Ensure all events are checked:
   - ☑️ PageView
   - ☑️ InitiateCheckout
   - ☑️ AddPaymentInfo
   - ☑️ Purchase
6. Click "Save Settings"

---

## 🧪 Testing the Flow

### Test 1: Basic Booking Flow

1. **Visit Event Page**
   ```
   http://your-domain.com/e/{event-slug}
   ```
   - Should show calendar and time slots
   - Click on a date → time slots should appear
   - Select a time slot
   - Click "Next: Enter Details"

2. **Fill Details Form**
   - Should redirect to `/e/{event-slug}/details?date=...&time=...`
   - Fill in name, email, phone
   - Click "Continue to Payment"

3. **Complete Payment**
   - Should redirect to `/payment/{booking-id}`
   - Click "Pay Now"
   - Complete payment (use test cards for Razorpay/PayU)
   - **Razorpay Test Card:** 4111 1111 1111 1111, CVV: 123, Any future date
   - **PayU Test Credentials:** Check PayU documentation

4. **Thank You Page**
   - Should redirect to `/payment/thankyou/{booking-id}`
   - Should show "Booking Confirmed" message
   - Should display Google Calendar link and Meet link

### Test 2: Verify Meta Pixel Events

**Install Meta Pixel Helper:**
- Chrome: [Meta Pixel Helper Extension](https://chrome.google.com/webstore/detail/meta-pixel-helper/fdgfkebogiimcoedlicjlajpkdmockpc)
- Firefox: Meta Pixel Helper Add-on

**Check Each Page:**

1. **Slot Selection Page** (`/e/{event-slug}`)
   - Open Meta Pixel Helper
   - Should see: `PageView` event

2. **Details Form** (`/e/{event-slug}/details`)
   - Should see: `PageView` + `InitiateCheckout`
   - `InitiateCheckout` should have parameters:
     - `value`: event price
     - `currency`: INR
     - `content_name`: event title

3. **Payment Page** (`/payment/{booking}`)
   - Should see: `PageView` + `AddPaymentInfo`
   - `AddPaymentInfo` should have same parameters as above

4. **Thank You Page** (`/payment/thankyou/{booking}`)
   - Should see: `PageView` + `Purchase` ✅
   - `Purchase` should have:
     - `value`: payment amount
     - `currency`: INR
     - `transaction_id`: payment transaction ID

### Test 3: Admin Panel Controls

**Test Disable Pixel:**
1. Go to `/tracking-settings`
2. Uncheck "Enable Meta Pixel Tracking"
3. Save
4. Visit any event page
5. Open page source → Meta Pixel script should NOT be present

**Test Disable Specific Event:**
1. Go to `/tracking-settings`
2. Enable Meta Pixel
3. Uncheck "InitiateCheckout"
4. Save
5. Visit details form page
6. Open Meta Pixel Helper → `InitiateCheckout` should NOT fire

### Test 4: Both Payment Gateways

**Test Razorpay:**
1. Go to `/payment-gateway` (admin)
2. Select "Razorpay"
3. Enter test credentials
4. Complete a booking
5. Verify redirect to thank you page

**Test PayU:**
1. Go to `/payment-gateway` (admin)
2. Select "PayU"
3. Enter test credentials
4. Complete a booking
5. Verify redirect to thank you page

---

## 🐛 Troubleshooting

### Issue: "No connection could be made" Error

**Problem:** Database server is not running

**Solution:**
```bash
# Start your database server
# For XAMPP: Start MySQL from control panel
# For Laravel Valet: valet restart
# For Docker: docker-compose up -d

# Verify connection
php artisan migrate:status
```

### Issue: Meta Pixel Not Loading

**Problem:** Pixel is disabled or ID is incorrect

**Solution:**
1. Check admin panel: `/tracking-settings`
2. Verify "Enable Meta Pixel Tracking" is checked
3. Verify Pixel ID is correct (16 digits)
4. Clear browser cache: Ctrl+Shift+R (hard refresh)

### Issue: Events Not Firing

**Problem:** Specific events are disabled

**Solution:**
1. Go to `/tracking-settings`
2. Check all event checkboxes
3. Save settings
4. Clear cache: `php artisan cache:clear`

### Issue: Payment Page Shows 404

**Problem:** Booking not created or invalid ID

**Solution:**
1. Check Laravel logs: `storage/logs/laravel.log`
2. Verify booking was created in database
3. Check payment gateway settings

### Issue: Redirect Loop or Errors

**Problem:** Route conflicts or middleware issues

**Solution:**
```bash
# Clear all caches
php artisan optimize:clear

# Re-cache routes
php artisan route:cache

# Check routes
php artisan route:list
```

### Issue: Google Calendar Event Not Created

**Problem:** Google OAuth not configured or token expired

**Solution:**
1. Check `.env` file for Google credentials:
   ```
   GOOGLE_CLIENT_ID=your-client-id
   GOOGLE_CLIENT_SECRET=your-client-secret
   ```
2. Re-authenticate with Google Calendar
3. Check user's Google token expiry in database

---

## 📊 Verify Meta Conversions

### In Meta Events Manager:

1. Go to [Meta Events Manager](https://business.facebook.com/events_manager)
2. Select your Pixel
3. Click "Test Events" tab
4. Complete a test booking
5. You should see events appear in real-time:
   - PageView (4 times)
   - InitiateCheckout (1 time)
   - AddPaymentInfo (1 time)
   - Purchase (1 time) ← **This is your conversion!**

### Create a Custom Conversion:

1. In Events Manager, go to "Custom Conversions"
2. Click "Create Custom Conversion"
3. Name: "Booking Completed"
4. Event Source: Select your Pixel
5. Conversion Event: `Purchase`
6. Save

Now you can track this conversion in Facebook Ads Manager!

---

## 📝 Important Notes

1. **Test Mode:** Always test in a staging environment first
2. **Real Pixel ID:** Don't use production Pixel ID in development
3. **Database Backups:** Backup database before running migrations
4. **Queue Workers:** Ensure queue workers are running for email notifications:
   ```bash
   php artisan queue:work
   ```
5. **SSL Certificate:** Meta Pixel works best with HTTPS

---

## 🎓 Next Steps

### 1. Create Facebook Conversion Campaign
- Go to Facebook Ads Manager
- Create new campaign → Conversions
- Select your custom conversion
- Target your audience
- Launch campaign

### 2. Monitor Performance
- Check Events Manager daily
- Review conversion funnel
- Identify drop-off points
- Optimize accordingly

### 3. Add More Tracking (Optional)
- Google Analytics 4
- Google Tag Manager
- LinkedIn Insight Tag
- Twitter Pixel

---

## 📞 Support Resources

- **Laravel Documentation:** https://laravel.com/docs/9.x
- **Meta Pixel Documentation:** https://developers.facebook.com/docs/meta-pixel
- **Razorpay Documentation:** https://razorpay.com/docs
- **PayU Documentation:** https://devguide.payu.in

---

## ✅ Final Checklist

Before going live:

- [ ] Database migration completed
- [ ] Meta Pixel ID configured
- [ ] All events enabled
- [ ] Tested complete booking flow
- [ ] Verified all 4 Meta events fire correctly
- [ ] Tested Razorpay payment
- [ ] Tested PayU payment (if using)
- [ ] Email notifications working
- [ ] Google Calendar integration working
- [ ] SSL certificate installed (HTTPS)
- [ ] Queue workers running
- [ ] Backup created

**Congratulations! Your multi-page booking flow with Meta Pixel tracking is ready! 🎉**
