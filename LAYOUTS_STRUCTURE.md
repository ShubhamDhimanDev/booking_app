# MeetFlow - Layouts Structure Documentation

## Overview
The application uses **5 distinct layouts** to provide tailored experiences for different user contexts:
- **auth.blade.php** - Authentication pages (login, register, password reset)
- **user.blade.php** - Guest/public booking interface
- **app.blade.php** - General-purpose fallback layout
- **organization.blade.php** - Organization member dashboard (extends base.blade.php)
- **super-admin.blade.php** - Platform administrator dashboard (extends base.blade.php)

---

## Layout Architecture

### 1. **layouts/auth.blade.php**
**Purpose:** Authentication and password management pages

**Features:**
- Split-screen design (50/50)
- Left side: Hero section with branding, tagline, testimonial
- Right side: Form content area
- Floating theme toggle
- LocalStorage-based theme persistence (guests only)
- Material Icons Outlined + Playfair Display + Plus Jakarta Sans fonts

**Used By:**
- `/register` - User registration
- `/login` - User login
- `/forgot-password` - Password reset request
- `/reset-password/{token}` - Password reset form
- `/verify-email` - Email verification prompt
- `/confirm-password` - Password confirmation

**Routes:** `routes/auth.php`

**View Files:**
```
resources/views/auth/
├── register.blade.php
├── login.blade.php
├── forgot-password.blade.php
├── reset-password.blade.php
├── verify-email.blade.php
└── confirm-password.blade.php
```

---

### 2. **layouts/user.blade.php**
**Purpose:** Guest-facing public booking interface and authenticated user views

**Features:**
- Full header with navigation:
  - **Guest:** Login + Sign Up buttons with security badge
  - **Authenticated:** Dashboard, My Bookings links + profile dropdown with role detection
- Main content container (max-w-7xl)
- Flash messages (success/error)
- Floating theme toggle
- Footer with links
- Theme persistence (server-side for auth, localStorage for guests)
- Material Icons Round + Plus Jakarta Sans fonts

**Used By:**
- `/{organization:subdomain}/events` - Public event listing
- `/{organization:subdomain}/events/{event:slug}` - Event detail page
- `/bookings/create` - Booking form
- `/bookings/{booking}/confirmation` - Booking confirmation
- `/bookings/{booking}/manage` - Guest booking management
- `/my/dashboard` - Authenticated user dashboard
- `/my/bookings` - Authenticated user bookings list

**Routes:** `routes/user.php`

**View Files:**
```
resources/views/user/
├── dashboard.blade.php (TODO)
├── bookings/
│   ├── index.blade.php (TODO)
│   ├── create.blade.php (TODO)
│   ├── confirmation.blade.php (TODO)
│   └── manage.blade.php (TODO)
└── profile/ (TODO)
```

---

### 3. **layouts/app.blade.php**
**Purpose:** General-purpose flexible layout

**Features:**
- Minimal header (can be disabled with `@section('no-header')`)
- Flexible container class (customizable via `@yield('container-class')`)
- Flash messages (success/error)
- Floating theme toggle (can be disabled with `@section('no-theme-toggle')`)
- Footer (can be disabled with `@section('no-footer')`)
- Theme persistence (server-side for auth, localStorage for guests)
- Sections: `header-actions`, `content`, `additional-styles`

**Used By:**
- `/` - Landing page (currently using welcome.blade.php)
- Policy pages (privacy, terms, help)
- Profile pages
- Any page requiring flexible structure

**Routes:** `routes/web.php` (miscellaneous pages)

**View Files:**
```
resources/views/
├── welcome.blade.php (standalone, TODO: convert to use app layout)
├── profile/ (TODO)
└── pages/ (TODO: privacy, terms, help)
```

---

### 4. **layouts/organization.blade.php** ⭐
**Purpose:** Organization member dashboard (org-owner, org-admin, org-member roles)

**Features:**
- Extends `layouts/base.blade.php`
- Includes `partials/sidebar/organization.blade.php`
- Full admin interface with Alpine.js
- Dark mode with preloader animation
- Responsive sidebar with collapse
- Header with user dropdown
- Flash messages

**Sidebar Menu (9 items):**
1. **Dashboard** - Overview with stats
2. **Events** - Event management (CRUD)
3. **Bookings** - Booking management
4. **Payments** - Payment processing
5. **Team** - Team member management
6. **Subscription** - Subscription management
7. **Invoices** - Invoice viewing/download
8. **Settings** - Organization settings

**Used By:**
- `/organization/dashboard`
- `/organization/events/*`
- `/organization/bookings/*`
- `/organization/payments/*`
- `/organization/team/*`
- `/organization/subscription/*`
- `/organization/invoices/*`
- `/organization/settings/*`

**Routes:** `routes/organization.php` (40+ endpoints)

**Middleware:** `auth`, `verified`, `has.organization`

**View Files:**
```
resources/views/organization/
├── dashboard.blade.php ✅
├── events/
│   ├── index.blade.php ✅
│   ├── create.blade.php (TODO)
│   ├── edit.blade.php (TODO)
│   └── show.blade.php (TODO)
├── bookings/
│   ├── index.blade.php ✅
│   ├── show.blade.php (TODO)
│   └── reschedule.blade.php (TODO)
├── billing/
│   ├── subscription.blade.php ✅
│   └── invoices.blade.php ✅
├── members/
│   ├── index.blade.php ✅
│   └── invite.blade.php (TODO)
└── settings/
    ├── general.blade.php (TODO)
    ├── branding.blade.php (TODO)
    └── notifications.blade.php (TODO)
```

---

### 5. **layouts/super-admin.blade.php** ⭐
**Purpose:** Platform administrator dashboard (super-admin role only)

**Features:**
- Extends `layouts/base.blade.php`
- Includes `partials/sidebar/super-admin.blade.php`
- Full admin interface with Alpine.js
- Dark mode with preloader animation
- Responsive sidebar with collapse
- Header with user dropdown
- Flash messages

**Sidebar Menu (10 items):**
1. **Dashboard** - Platform metrics
2. **Organizations** - Organization management
3. **Users** - User management with impersonation
4. **All Events** - Platform-wide event viewing
5. **All Bookings** - Platform-wide booking viewing
6. **Plans** - Subscription plan management
7. **Subscriptions** - Active subscription management
8. **Invoices** - Platform-wide invoice viewing
9. **Reports** - Revenue and analytics
10. **Settings** - Platform settings

**Used By:**
- `/super-admin/dashboard`
- `/super-admin/organizations/*`
- `/super-admin/users/*`
- `/super-admin/events/*`
- `/super-admin/bookings/*`
- `/super-admin/plans/*`
- `/super-admin/subscriptions/*`
- `/super-admin/invoices/*`
- `/super-admin/reports/*`
- `/super-admin/settings/*`

**Routes:** `routes/super-admin.php` (60+ endpoints)

**Middleware:** `auth`, `verified`, `role:super-admin`

**View Files:**
```
resources/views/super-admin/
├── dashboard.blade.php ✅
├── organizations/
│   ├── index.blade.php ✅
│   ├── create.blade.php (TODO)
│   ├── edit.blade.php (TODO)
│   └── show.blade.php (TODO)
├── plans/
│   ├── index.blade.php ✅
│   ├── create.blade.php (TODO)
│   └── edit.blade.php (TODO)
├── users/
│   ├── index.blade.php (TODO)
│   ├── create.blade.php (TODO)
│   └── edit.blade.php (TODO)
├── events/
│   └── index.blade.php (TODO)
├── bookings/
│   └── index.blade.php (TODO)
├── subscriptions/
│   └── index.blade.php (TODO)
├── invoices/
│   └── index.blade.php (TODO)
├── reports/
│   ├── revenue.blade.php (TODO)
│   ├── subscriptions.blade.php (TODO)
│   └── organizations.blade.php (TODO)
└── settings/
    ├── general.blade.php (TODO)
    ├── email.blade.php (TODO)
    └── payment.blade.php (TODO)
```

---

## Base Layout Details

### **layouts/base.blade.php**
Parent layout for organization and super-admin interfaces

**Alpine.js Data:**
```javascript
{
    darkMode: {{ auth()->user()?->dark_mode ? 'true' : 'false' }},
    sidebarToggle: false,
    page: {
        title: '@yield('title', 'Dashboard')'
    },
    loaded: false
}
```

**Structure:**
- Preloader with fade-out animation
- Flex layout: Sidebar (fixed) + Content (flex-1)
- Mobile overlay for sidebar
- Sticky header with user dropdown
- Flash messages
- Vite assets (resources/css/app.css, resources/js/app.js)

**Yields:**
- `@yield('title')` - Page title
- `@yield('sidebar')` - Sidebar partial
- `@yield('content')` - Main content
- `@stack('styles')` - Additional styles
- `@stack('scripts')` - Additional scripts

---

## Partials

### **partials/header.blade.php**
Shared header for admin layouts

**Features:**
- Hamburger menu toggle for mobile
- Logo with link to dashboard
- Dark mode toggle
- User dropdown:
  - Avatar with initials fallback
  - Role detection (super-admin, org-owner, org-admin, org-member)
  - Profile + Settings links
  - Logout form

### **partials/sidebar/organization.blade.php**
Organization member sidebar navigation

**Groups:**
- **MENU:** Dashboard, Events, Bookings, Payments, Team
- **BILLING:** Subscription, Invoices
- **SETTINGS:** Settings

**Features:**
- Active state detection
- Permission checks (`@can` directives)
- Responsive collapse on mobile
- Material Icons Round

### **partials/sidebar/super-admin.blade.php**
Super admin sidebar navigation

**Groups:**
- **PLATFORM:** Dashboard, Organizations, Users, All Events, All Bookings
- **SUBSCRIPTION:** Plans, Subscriptions, Invoices
- **SYSTEM:** Reports, Settings

**Features:**
- Active state detection
- Responsive collapse on mobile
- Material Icons Round

---

## Routing Logic

### **Smart Dashboard Redirect (routes/web.php)**
```php
Route::get('/dashboard', function () {
    if (auth()->user()->hasRole('super-admin')) {
        return redirect()->route('super-admin.dashboard');
    }
    
    if (auth()->user()->organization_id) {
        return redirect()->route('organization.dashboard');
    }
    
    return redirect()->route('user.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');
```

### **Route File Organization**
1. **routes/web.php** - Public pages, landing, smart redirect
2. **routes/auth.php** - Authentication flows
3. **routes/user.php** - Guest booking + authenticated user
4. **routes/organization.php** - Organization member dashboard
5. **routes/super-admin.php** - Platform administration

---

## Theme System

### **Server-Side (Authenticated Users)**
- Theme stored in `users.dark_mode` column
- Service: `App\Services\ThemeService`
- API endpoint: `/api/user/theme` (POST)
- Persisted across sessions

### **Client-Side (Guests)**
- Theme stored in `localStorage`
- Key: `theme`
- Values: `'dark'` or `'light'`
- Falls back to system preference

### **Implementation**
All layouts include theme toggle JavaScript:
```javascript
// For authenticated users
fetch('/api/user/theme', {
    method: 'POST',
    body: JSON.stringify({ dark_mode: !isDark })
});

// For guests
localStorage.setItem('theme', newTheme);
```

---

## Creating New Views

### **For Auth Pages**
```blade
@extends('layouts.auth')

@section('title', 'Login to MeetFlow')

@section('content')
    <!-- Form content here -->
@endsection
```

### **For Guest/Public Pages**
```blade
@extends('layouts.user')

@section('title', 'Browse Events')
@section('badge-icon', 'event')
@section('badge-text', 'Find Events')

@section('content')
    <!-- Page content here -->
@endsection
```

### **For Organization Pages**
```blade
@extends('layouts.organization')

@section('title', 'Events')

@section('content')
    <!-- Admin content here -->
@endsection
```

### **For Super Admin Pages**
```blade
@extends('layouts.super-admin')

@section('title', 'Organizations')

@section('content')
    <!-- Admin content here -->
@endsection
```

### **For General Pages (app layout)**
```blade
@extends('layouts.app')

@section('title', 'Privacy Policy')
@section('no-header') {{-- Optional: disable header --}}
@section('container-class', 'container mx-auto px-4 py-20')

@section('content')
    <!-- Page content here -->
@endsection
```

---

## Design System

### **Colors (Tailwind)**
- `primary` - Main brand color (indigo-600)
- `accent` - Accent color (pink-500)
- `background-light` - Light mode bg (slate-50)
- `background-dark` - Dark mode bg (slate-900)

### **Typography**
- **Admin Layouts:** Plus Jakarta Sans (300-800 weights)
- **Auth Layout:** Plus Jakarta Sans + Playfair Display (serif for headings)
- Line heights: Relaxed (leading-relaxed)

### **Icons**
- **Admin:** Material Icons Round
- **Auth:** Material Icons Outlined
- **User:** Material Icons Round

### **Spacing**
- Content containers: `max-w-7xl`
- Section padding: `py-10`
- Card padding: `p-6`
- Gaps: `gap-6` (24px)

### **Components**
- Cards: Rounded corners (`rounded-xl`), shadows (`shadow-sm`)
- Buttons: Primary (`bg-primary`), Ghost (transparent with border)
- Badges: Rounded full (`rounded-full`), uppercase text
- Tables: Striped rows, hover effects
- Forms: Focus rings, validation states

---

## Next Steps

### **Immediate Priorities**
1. ✅ Populate auth.blade.php
2. ✅ Create user.blade.php
3. ✅ Update app.blade.php
4. ✅ Verify route compatibility
5. ⏳ Create user dashboard and booking views
6. ⏳ Create organization CRUD views (events, team, settings)
7. ⏳ Create super-admin CRUD views (organizations, users, plans)
8. ⏳ Create policy pages (privacy, terms, help)
9. ⏳ Convert welcome.blade.php to use app layout

### **View Creation Workflow**
1. Identify which layout to extend
2. Create view file in appropriate directory
3. Add `@extends()` directive
4. Define `@section('title')` and `@section('content')`
5. Use design system components (cards, buttons, tables)
6. Add Alpine.js for interactivity
7. Test with sample data

### **Testing Checklist**
- [ ] Auth flows (register, login, password reset)
- [ ] User booking flow (browse, create, manage)
- [ ] Organization dashboard navigation
- [ ] Super-admin dashboard navigation
- [ ] Theme toggle (auth and guest)
- [ ] Mobile responsive design
- [ ] Dark mode appearance
- [ ] Flash messages display
- [ ] Permission gates (`@can`)
- [ ] Role redirects

---

## Best Practices

### **Performance**
- Use `@vite()` for asset compilation
- Lazy load heavy components
- Optimize images (convert to WebP)
- Cache static resources

### **Security**
- Always include CSRF token forms
- Use `@can` for permission checks
- Validate user input in controllers
- Sanitize HTML output

### **Accessibility**
- Use semantic HTML tags
- Add ARIA labels where needed
- Ensure keyboard navigation works
- Maintain color contrast ratios

### **Maintainability**
- Follow Blade naming conventions
- Extract reusable components to partials
- Document complex Alpine.js logic
- Keep views focused on presentation

---

**Last Updated:** {{ date('Y-m-d') }}
**Version:** 1.0.0
