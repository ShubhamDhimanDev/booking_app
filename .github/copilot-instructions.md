# Copilot Instructions for Natega

## Project Overview
**Natega** is a Laravel-based booking platform that syncs with Google Calendar. Users (event organizers) create events with time slots; bookers reserve those slots and process payments via pluggable gateways (Razorpay/PayU). Automated reminders notify both parties before events.

## Core Architecture

### Data Model
- **Users**: Event organizers and bookers (single user table)
- **Events**: Calendar-linked slots defined by organizer (Google sync via `BookingController`)
- **Bookings**: Reservations with status (`pending`, `confirmed`, `declined`, `rescheduled`)
- **Payments**: Linked 1:1 to bookings; supports dual payment gateways
- **EventReminders**: Configurable reminder offsets (minutes before event) per event
- **BookingReminderLog**: Tracks which reminders were sent (idempotency)
- **EventExclusion**: Blackout dates for events
- **Settings**: Stores active payment gateway (`payment_gateway` key)

### Frontend Routes (UnauthenticatedACCESS)
- `GET /e/{event:slug}` → Public event booking page (React/InertiaJS)
- `POST /e/{event:slug}/book` → Store booking via `BookingController@store`
- `GET|POST /payment/*` → Payment flow (Razorpay/PayU)
- `GET /payu/*` → PayU-specific callbacks

### Backend Routes (Auth + Middleware)
- Admin routes: Require `IsAdmin` + `LinkedWithGoogleMiddleware` (Google Calendar sync)
- User routes: Bookings list, reschedule form/action, transactions history
- Payment routes: Create order, verify payment (handler agnostic)

## Key Patterns & Conventions

### Payment Gateway Pattern
**Problem**: Support multiple payment processors with encrypted credential storage; easy switching via admin UI
- `app/Services/PaymentGatewayInterface` → Common contract (getName, getPublicConfig, initiatePayment, verifyPayment)
- `PaymentGatewayManager` → Loads active gateway from `Settings` table at runtime
- `RazorpayService`, `PayUService` → Implementations (read credentials from encrypted Settings or env)
- `Setting` model → Key-value store with encryption; `setSetting($key, $value, $encrypt=false)`, `getSetting($key, $default)`
- Admin panel: `/admin/payment-gateway` - Select gateway + store encrypted credentials
- Usage: Inject `PaymentGatewayManager` in controller, call `getActiveGateway()` to get active service instance
- **Adding new gateway**: 1) Implement `PaymentGatewayInterface`, 2) Register in `PaymentGatewayManager::$gateways`, 3) Update admin form + frontend handlers
- Example: `app/Http/Controllers/PaymentController.php` line ~45 (createOrder uses `getActiveGateway()`)
- **Frontend**: `book-event.blade.php` detects gateway and calls `handleRazorpayPayment()` or `handlePayUPayment()`

### Notification & Reminder Queuing
- All notifications extend `Notification` + `ShouldQueue` (queued by default)
- Notifications: `BookingCreatedNotification`, `BookingRescheduledNotification`, `BookingDeclinedNotification`
- Reminders: `BookingReminderJob` (Laravel queued job, not command)
  - Triggered by scheduler or manually; reads `EventReminder` offsets
  - Logs sent reminders to `BookingReminderLog` to prevent duplicates
  - Sends via `BookingReminderNotification`

### Authorization
- Model policies: `BookingPolicy`, `EventPolicy` (in `app/Policies/`)
- Middleware checks: `IsAdmin`, `LinkedWithGoogleMiddleware` (Google token validation)
- Environment: Google auth ONLY works on `127.0.0.1`, not `.test` domains (localhost redirect)

### Database Migrations
- Events: `price`, `available_week_days`, `custom_timeslots` (flexible time setup)
- Bookings: `status`, `user_id` (booker), `event_id`, timestamps
- Payments: `booking_id` (unique), `gateway`, `order_id`, `status`
- Reminders: `event_id`, `offset_minutes`, `notification_type`
- Settings: Key-value store for admin config

## Developer Workflows

### Setup
```bash
cp .env.example .env
# Set DB_*, GOOGLE_CLIENT_ID/SECRET, RAZORPAY/PAYU keys in .env
composer install
npm install
php artisan migrate
php artisan serve  # Use 127.0.0.1:8000 for Google OAuth
npm run build      # Or npm run dev for watch mode
```

### Key Commands
- Reminders: Trigger job manually: `php artisan tinker` → `dispatch(new BookingReminderJob())`
- Database: `php artisan migrate:fresh --seed` (if seeders exist)
- Testing: `php artisan test` or `vendor/bin/phpunit`
- Logs: `storage/logs/laravel.log`

### Adding Features
1. **New notification**: Extend `Notification`, implement `ShouldQueue`, add to controllers where needed
2. **New payment gateway**: Create service class, implement `PaymentGatewayInterface`, add to `PaymentGatewayManager::$gateways`
3. **New job**: Place in `app/Jobs/`, implement `ShouldQueue` if async, dispatch from event/controller
4. **Admin panel**: Routes in `routes/admin.php` (check if it exists; web.php has commented-out admin routes)

## Critical Integration Points
- **Google Calendar**: Requires `GOOGLE_CLIENT_ID/SECRET` in `.env`; linked events use OAuth flow
- **Payment Gateways**: Razor pay uses API key/secret; PayU uses credentials; verify callbacks match webhook signatures
- **Queue**: Notifications/jobs need queue worker: `php artisan queue:work` (or supervisor for production)
- **Frontend**: InertiaJS (React) in `resources/js/`; Blade templatesback up routes; TailwindCSS configured in `tailwind.config.js`

## File References
- Models: `app/Models/{Booking,Event,User,Payment,EventReminder}.php`
- Core logic: `app/Http/Controllers/{BookingController,PaymentController}.php`
- Jobs: `app/Jobs/BookingReminderJob.php`
- Services: `app/Services/{PaymentGatewayManager,RazorpayService,PayUService}.php`
- Routes: `routes/{web.php,admin.php,auth.php}`
- Frontend: `resources/js/Pages/`, `resources/views/`

---
See Laravel docs: https://laravel.com/docs/9.x
