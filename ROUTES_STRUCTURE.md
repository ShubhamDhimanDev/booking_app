# Routes & Controllers Structure

## 📁 File Organization

### Routes Structure
```
routes/
├── web.php              # Public routes & dashboard redirect
├── auth.php             # Authentication routes (Laravel Breeze)
├── organization.php     # Organization member routes
├── super-admin.php      # Super admin platform management
└── user.php             # Guest/user-facing booking routes
```

### Controllers Structure
```
app/Http/Controllers/
├── Controller.php       # Base controller
│
├── Organization/        # Organization-scoped controllers
│   ├── DashboardController.php
│   ├── EventController.php
│   ├── BookingController.php
│   ├── TeamController.php
│   ├── SubscriptionController.php
│   ├── InvoiceController.php
│   ├── PaymentController.php
│   └── SettingsController.php
│
├── SuperAdmin/          # Super admin controllers
│   ├── DashboardController.php
│   ├── OrganizationController.php
│   ├── UserController.php
│   ├── EventController.php
│   ├── BookingController.php
│   ├── SubscriptionPlanController.php
│   ├── SubscriptionController.php
│   ├── InvoiceController.php
│   ├── ReportController.php
│   └── SettingsController.php
│
└── User/                # Guest/user-facing controllers
    ├── EventController.php
    └── BookingController.php
```

## 🛣️ Route Groups & Middleware

### 1. **Web Routes** (`routes/web.php`)
- **Prefix:** None
- **Middleware:** `web`
- **Purpose:** Public landing page, dashboard redirect logic

**Key Routes:**
- `GET /` - Landing page
- `GET /dashboard` - Smart redirect based on user role

---

### 2. **Organization Routes** (`routes/organization.php`)
- **Prefix:** `/organization`
- **Middleware:** `auth`, `verified`, `has.organization`
- **Name Prefix:** `organization.`

**Route Groups:**

#### Dashboard
- `GET /organization/dashboard` → `DashboardController@index`

#### Events Management
- `GET /organization/events` → `EventController@index`
- `GET /organization/events/create` → `EventController@create`
- `POST /organization/events` → `EventController@store`
- `GET /organization/events/{event}` → `EventController@show`
- `GET /organization/events/{event}/edit` → `EventController@edit`
- `PATCH /organization/events/{event}` → `EventController@update`
- `DELETE /organization/events/{event}` → `EventController@destroy`
- `POST /organization/events/{event}/duplicate` → `EventController@duplicate`
- `PATCH /organization/events/{event}/toggle-status` → `EventController@toggleStatus`

#### Bookings Management
- `GET /organization/bookings` → `BookingController@index`
- `GET /organization/bookings/{booking}` → `BookingController@show`
- `POST /organization/bookings/{booking}/confirm` → `BookingController@confirm`
- `POST /organization/bookings/{booking}/cancel` → `BookingController@cancel`
- `POST /organization/bookings/{booking}/reschedule` → `BookingController@reschedule`

#### Payments
- `GET /organization/payments` → `PaymentController@index`
- `GET /organization/payments/{payment}` → `PaymentController@show`
- `POST /organization/payments/{payment}/refund` → `PaymentController@refund`

#### Team Management
- `GET /organization/team` → `TeamController@index`
- `GET /organization/team/invite` → `TeamController@showInviteForm`
- `POST /organization/team/invite` → `TeamController@invite`
- `GET /organization/team/{user}` → `TeamController@show`
- `GET /organization/team/{user}/edit` → `TeamController@edit`
- `PATCH /organization/team/{user}` → `TeamController@update`
- `DELETE /organization/team/{user}` → `TeamController@remove`

#### Subscription & Billing
- `GET /organization/subscription` → `SubscriptionController@show`
- `GET /organization/subscription/change-plan` → `SubscriptionController@changePlan`
- `POST /organization/subscription/subscribe` → `SubscriptionController@subscribe`
- `POST /organization/subscription/change` → `SubscriptionController@change`
- `POST /organization/subscription/cancel` → `SubscriptionController@cancel`
- `POST /organization/subscription/resume` → `SubscriptionController@resume`

#### Invoices
- `GET /organization/invoices` → `InvoiceController@index`
- `GET /organization/invoices/{invoice}` → `InvoiceController@show`
- `GET /organization/invoices/{invoice}/download` → `InvoiceController@download`

#### Settings
- `GET /organization/settings` → `SettingsController@index`
- `PATCH /organization/settings/general` → `SettingsController@updateGeneral`
- `PATCH /organization/settings/branding` → `SettingsController@updateBranding`
- `PATCH /organization/settings/notifications` → `SettingsController@updateNotifications`
- `DELETE /organization/settings/delete-organization` → `SettingsController@deleteOrganization`

---

### 3. **Super Admin Routes** (`routes/super-admin.php`)
- **Prefix:** `/super-admin`
- **Middleware:** `auth`, `verified`, `role:super-admin`
- **Name Prefix:** `super-admin.`

**Route Groups:**

#### Dashboard
- `GET /super-admin/dashboard` → `DashboardController@index`

#### Organizations Management
- `GET /super-admin/organizations` → `OrganizationController@index`
- `GET /super-admin/organizations/create` → `OrganizationController@create`
- `POST /super-admin/organizations` → `OrganizationController@store`
- `GET /super-admin/organizations/{organization}` → `OrganizationController@show`
- `GET /super-admin/organizations/{organization}/edit` → `OrganizationController@edit`
- `PATCH /super-admin/organizations/{organization}` → `OrganizationController@update`
- `DELETE /super-admin/organizations/{organization}` → `OrganizationController@destroy`
- `POST /super-admin/organizations/{organization}/suspend` → `OrganizationController@suspend`
- `POST /super-admin/organizations/{organization}/activate` → `OrganizationController@activate`

#### Users Management
- `GET /super-admin/users` → `UserController@index`
- Full CRUD operations + impersonate

#### Platform-wide Views
- `GET /super-admin/events` → `EventController@index`
- `GET /super-admin/bookings` → `BookingController@index`

#### Subscription Plans
- `GET /super-admin/plans` → `SubscriptionPlanController@index`
- Full CRUD + activate/deactivate

#### Subscriptions
- `GET /super-admin/subscriptions` → `SubscriptionController@index`
- Cancel/resume operations

#### Invoices
- `GET /super-admin/invoices` → `InvoiceController@index`
- View/download operations

#### Reports
- `GET /super-admin/reports` → `ReportController@index`
- Revenue, subscriptions, organizations reports
- Export functionality

#### Settings
- `GET /super-admin/settings` → `SettingsController@index`
- General, email, payment gateway settings
- Cache clear functionality

---

### 4. **User/Guest Routes** (`routes/user.php`)
- **Prefix:** None (subdomain-based)
- **Middleware:** `web`
- **Name Prefix:** `user.`

**Route Groups:**

#### Public Event Viewing
- `GET /{organization:subdomain}/events` → `EventController@index`
- `GET /{organization:subdomain}/events/{event:slug}` → `EventController@show`

#### Booking Flow
- `GET /{organization:subdomain}/events/{event:slug}/book` → `BookingController@create`
- `POST /{organization:subdomain}/events/{event:slug}/book` → `BookingController@store`
- `GET /bookings/{booking}/confirmation` → `BookingController@confirmation`

#### Booking Management (Guest)
- `GET /bookings/{booking}/manage` → `BookingController@manage`
- `POST /bookings/{booking}/reschedule` → `BookingController@reschedule`
- `POST /bookings/{booking}/cancel` → `BookingController@cancel`

#### Payment Callbacks
- `GET /payments/callback/{gateway}` → `BookingController@paymentCallback`
- `POST /payments/webhook/{gateway}` → `BookingController@paymentWebhook`

#### Authenticated User (Optional)
- `GET /my/dashboard` → User dashboard
- `GET /my/bookings` → `BookingController@userBookings`

---

## 🔐 Middleware Reference

### Custom Middleware
- **`has.organization`** - Ensures user belongs to an organization
  - Location: `app/Http/Middleware/EnsureUserHasOrganization.php`
  - Used in: Organization routes

### Existing Middleware
- **`auth`** - Requires authentication
- **`verified`** - Requires email verification
- **`role:super-admin`** - Requires super-admin role (Spatie)
- **`permission:*`** - Requires specific permission (Spatie)
- **`tenant`** - Multi-tenancy scoping
- **`subscription`** - Subscription status check
- **`usage.limit`** - Usage limit enforcement

---

## 🎯 Key Features

### 1. **Namespace Organization**
All controllers are properly namespaced:
- `App\Http\Controllers\Organization\*`
- `App\Http\Controllers\SuperAdmin\*`
- `App\Http\Controllers\User\*`

### 2. **Route Model Binding**
Uses implicit route model binding for:
- `{organization}` - Organization model
- `{event}` - Event model
- `{booking}` - Booking model
- `{user}` - User model
- `{subscription}` - Subscription model
- `{invoice}` - Invoice model
- `{plan}` - SubscriptionPlan model

### 3. **Authorization**
Controllers use Laravel's policy-based authorization:
```php
$this->authorize('view', $event);
$this->authorize('update', $booking);
```

### 4. **Route Names**
All routes have descriptive names following convention:
```
{prefix}.{resource}.{action}

Examples:
- organization.events.index
- super-admin.organizations.show
- user.bookings.create
```

---

## 📝 Usage Examples

### Organization Dashboard
```php
route('organization.dashboard')
// /organization/dashboard
```

### Create Event
```php
route('organization.events.create')
// /organization/events/create
```

### View Super Admin Organizations
```php
route('super-admin.organizations.index')
// /super-admin/organizations
```

### Guest Booking
```php
route('user.bookings.create', [
    'organization' => 'acme',
    'event' => 'consultation'
])
// /acme/events/consultation/book
```

---

## 🚀 Next Steps

1. **Create Policies** for authorization
   ```bash
   php artisan make:policy EventPolicy --model=Event
   php artisan make:policy BookingPolicy --model=Booking
   ```

2. **Create Form Requests** for complex validation
   ```bash
   php artisan make:request StoreEventRequest
   php artisan make:request UpdateSubscriptionRequest
   ```

3. **Implement Views** for all controller actions

4. **Add API Routes** if needed (SPA/mobile app)

5. **Create Seeders** for testing
   ```bash
   php artisan make:seeder OrganizationSeeder
   php artisan make:seeder SubscriptionPlanSeeder
   ```

---

## 🔧 Configuration

### Register Routes in RouteServiceProvider
Already configured in `app/Providers/RouteServiceProvider.php`:
```php
Route::middleware('web')->group(base_path('routes/web.php'));
Route::middleware('web')->group(base_path('routes/organization.php'));
Route::middleware('web')->group(base_path('routes/super-admin.php'));
Route::middleware('web')->group(base_path('routes/user.php'));
```

### Register Middleware Alias
Already configured in `app/Http/Kernel.php`:
```php
'has.organization' => \App\Http\Middleware\EnsureUserHasOrganization::class,
```

---

## ✅ Best Practices Implemented

1. **Separation of Concerns** - Controllers organized by domain
2. **RESTful Design** - Follows REST conventions
3. **Middleware Protection** - Proper authentication/authorization
4. **Route Model Binding** - Clean controller methods
5. **Named Routes** - Easy URL generation
6. **Namespace Organization** - Scalable structure
7. **Policy-based Authorization** - Centralized permissions
8. **Service Layer** - Business logic in services (PaymentGatewayFactory)
9. **Job Queues** - Async processing (commented placeholders)
10. **Validation** - Request validation in controllers

---

This structure provides a solid foundation for a scalable multi-tenant SaaS booking platform!
