# MeetFlow SaaS Transformation Roadmap v2.0
**Date:** February 8, 2026  
**Status:** Phase 1 Complete ✅ | In Progress: Phase 2

---

## 🎉 Phase 1 Complete! 

**Completion Date:** February 8, 2026  
**Phase:** Multi-Tenancy Foundation  
**Status:** ✅ Complete

### What Was Delivered:
- ✅ Organizations table with subscription management
- ✅ Organization_id added to all relevant tables
- ✅ Team invitation system
- ✅ Activity logging for compliance
- ✅ 3 new models (Organization, OrganizationInvitation, OrganizationActivityLog)
- ✅ 9 models updated with multi-tenancy support
- ✅ 4 middleware classes (Tenant, CheckSubscription, CheckUsageLimits, SuperAdmin)
- ✅ New role structure: super-admin, org-owner, org-admin, org-member, guest
- ✅ 40+ granular permissions
- ✅ Global scopes for automatic tenant isolation

**📁 Detailed Documentation:** See [phase-1-complete.md](phase-1-complete.md)

---

## 📊 Current State Analysis

### ✅ Already Implemented
- **Events System**: Advanced event management with custom timeslots, pricing, refund policies
- **Bookings**: Full lifecycle management (create, confirm, cancel, reschedule)
- **Payment Processing**: Razorpay (primary) and PayU integration with refund support
- **Promo Codes**: Discount system with percentage/fixed amounts
- **Refunds**: Automated refund processing with gateway integration
- **Follow-up Invites**: Re-engagement system with custom pricing
- **Google Calendar Integration**: Automatic event sync
- **Email System**: Booking confirmations, reminders, cancellation notifications
- **Analytics**: UTM tracking for marketing attribution
- **Help Desk**: Basic support request system
- **Tracking Service**: Facebook Pixel, Google Analytics integration
- **Theme System**: Dark mode and layout preferences

### 🔴 Missing for SaaS
- **Multi-Tenancy**: No organization/workspace model
- **Subscription Billing**: No recurring revenue system
- **Usage Limits**: No plan-based restrictions
- **Organization Settings**: All settings are global
- **Subdomain/Custom Domains**: No tenant isolation by domain
- **API**: No public API for integrations
- **White-labeling**: No per-organization branding
- **Team Management**: Limited role hierarchy

---

## 🎯 SaaS Transformation Strategy

### Core Principle: **Non-Disruptive Migration**
Transform existing single-tenant system into multi-tenant SaaS while maintaining backward compatibility and data integrity.

### Payment Architecture
- **B2C Payments** (Existing): Users → Organization owners (via Razorpay)
- **B2B Payments** (New): Organizations → Platform (subscription via Razorpay)

---

## 📋 Phase 1: Multi-Tenancy Foundation ✅ COMPLETED

**Duration:** Week 1-2  
**Status:** ✅ Complete  
**Completion Date:** February 8, 2026

### ✅ Delivered Features

#### Database Migrations
- ✅ Organizations table with subscription management
- ✅ Organization_id added to 9 tables (users, events, bookings, payments, promo_codes, refunds, settings, follow_up_invites, help_requests)
- ✅ Organization invitations table
- ✅ Organization activity logs table

#### Models
- ✅ Organization model with relationships
- ✅ OrganizationInvitation model
- ✅ OrganizationActivityLog model
- ✅ Updated 9 existing models with multi-tenancy support (global scopes, auto-assignment)

#### Middleware
- ✅ TenantMiddleware (subdomain/domain identification)
- ✅ CheckSubscription (subscription validation)
- ✅ CheckUsageLimits (plan-based limits)
- ✅ SuperAdminMiddleware (platform admin access)

#### Roles & Permissions
- ✅ New role structure: super-admin, org-owner, org-admin, org-member, guest
- ✅ 40+ granular permissions
- ✅ RoleSeeder with complete permission mapping

**📁 Full Implementation Details:** [phase-1-complete.md](phase-1-complete.md)

---

### Original Plan Documentation (For Reference)

Below is the original planned implementation. The actual implementation follows the same structure with improvements.

### Objective
Introduce organization model and scope all existing data to organizations without breaking current functionality.

### Database Schema Changes

#### Migration 1.1: Create Organizations Table
```sql
Schema::create('organizations', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique(); // for subdomain: {slug}.meetflow.app
    $table->string('domain')->nullable()->unique(); // optional custom domain
    $table->text('description')->nullable();
    
    // Owner & Contact
    $table->foreignId('owner_id')->constrained('users')->onDelete('restrict');
    $table->string('contact_email');
    $table->string('contact_phone')->nullable();
    
    // Subscription Status
    $table->enum('status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial');
    $table->foreignId('current_plan_id')->nullable()->constrained('subscription_plans')->onDelete('set null');
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamp('subscribed_at')->nullable();
    
    // Preferences
    $table->string('timezone')->default('Asia/Kolkata');
    $table->string('currency')->default('INR');
    $table->string('locale')->default('en');
    
    // Branding (JSON)
    $table->json('branding')->nullable(); // logo_url, primary_color, secondary_color
    $table->json('settings')->nullable(); // org-specific configurations
    
    // Billing
    $table->string('razorpay_customer_id')->nullable();
    $table->string('billing_email')->nullable();
    $table->text('billing_address')->nullable();
    $table->string('gstin')->nullable(); // GST number for Indian businesses
    
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['status', 'trial_ends_at']);
    $table->index('slug');
});
```

#### Migration 1.2: Add organization_id to All Tables
```sql
// Apply to: users, events, bookings, payments, promo_codes, refunds, settings

Schema::table('users', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->index('organization_id');
});

Schema::table('events', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->index(['organization_id', 'user_id', 'created_at']);
});

Schema::table('bookings', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->index(['organization_id', 'status', 'booked_at_date']);
});

Schema::table('payments', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->index(['organization_id', 'status', 'created_at']);
});

Schema::table('promo_codes', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->index('organization_id');
});

Schema::table('refunds', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->index(['organization_id', 'status']);
});

Schema::table('settings', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->unique(['organization_id', 'key']); // Remove old unique key index first
    $table->index('organization_id');
});

Schema::table('follow_up_invites', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->index('organization_id');
});

Schema::table('help_requests', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->index('organization_id');
});
```

#### Migration 1.3: Update Roles System
```sql
// Existing roles: owner, admin, team-member, user
// New structure:
// - super-admin (platform admin - already exists)
// - org-owner (organization owner, billing contact)
// - org-admin (organization admin, can manage everything except billing)
// - org-member (team member, limited permissions)
// - guest (external user who made a booking)

// Rename existing roles via seeder update
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_super_admin')->default(false)->after('email_verified_at');
    $table->index('is_super_admin');
});
```

#### Migration 1.4: Create Organization Invitations
```sql
Schema::create('organization_invitations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
    $table->string('email');
    $table->string('token', 64)->unique();
    $table->enum('role', ['org-admin', 'org-member'])->default('org-member');
    $table->enum('status', ['pending', 'accepted', 'expired', 'cancelled'])->default('pending');
    $table->timestamp('expires_at');
    $table->timestamp('accepted_at')->nullable();
    $table->timestamps();
    
    $table->index(['organization_id', 'email', 'status']);
    $table->index(['token', 'status']);
});
```

#### Migration 1.5: Create Organization Activity Log
```sql
Schema::create('organization_activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->string('action', 100); // event.created, booking.cancelled, user.invited, etc.
    $table->string('entity_type', 50)->nullable(); // Event, Booking, User
    $table->unsignedBigInteger('entity_id')->nullable();
    $table->json('metadata')->nullable(); // additional context
    $table->ipAddress('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    $table->timestamps();
    
    $table->index(['organization_id', 'created_at']);
    $table->index(['entity_type', 'entity_id']);
    $table->index('action');
});
```

### Data Migration Script
```php
// artisan command: php artisan migrate:to-multi-tenant

class MigrateToMultiTenant extends Command
{
    public function handle()
    {
        // 1. Find the first owner user (from existing seeder)
        $owner = User::role('owner')->first();
        
        if (!$owner) {
            $this->error('No owner user found. Run RoleAndAdminSeeder first.');
            return 1;
        }
        
        // 2. Create default organization for existing data
        $org = Organization::create([
            'name' => config('app.name', 'MeetFlow'),
            'slug' => Str::slug(config('app.name', 'meetflow')),
            'owner_id' => $owner->id,
            'contact_email' => $owner->email,
            'status' => 'active', // grandfather existing installation
            'trial_ends_at' => null,
            'subscribed_at' => now(), // treat as legacy customer
            'timezone' => 'Asia/Kolkata',
            'currency' => 'INR',
        ]);
        
        $this->info("Created organization: {$org->name} (ID: {$org->id})");
        
        // 3. Assign all users to this organization
        User::whereNull('organization_id')->update(['organization_id' => $org->id]);
        
        // 4. Assign all events to this organization
        Event::whereNull('organization_id')->update(['organization_id' => $org->id]);
        
        // 5. Assign all bookings to this organization
        Booking::whereNull('organization_id')->update(['organization_id' => $org->id]);
        
        // 6. Assign all payments to this organization
        Payment::whereNull('organization_id')->update(['organization_id' => $org->id]);
        
        // 7. Assign all promo codes to this organization
        PromoCode::whereNull('organization_id')->update(['organization_id' => $org->id]);
        
        // 8. Assign all refunds to this organization
        Refund::whereNull('organization_id')->update(['organization_id' => $org->id]);
        
        // 9. Assign all settings to this organization
        Setting::whereNull('organization_id')->update(['organization_id' => $org->id]);
        
        // 10. Assign other tables
        FollowUpInvite::whereNull('organization_id')->update(['organization_id' => $org->id]);
        HelpRequest::whereNull('organization_id')->update(['organization_id' => $org->id]);
        
        $this->info('✅ Migration complete!');
        
        return 0;
    }
}
```

### Models Update

#### Organization Model
```php
class Organization extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'name', 'slug', 'domain', 'description',
        'owner_id', 'contact_email', 'contact_phone',
        'status', 'current_plan_id', 'trial_ends_at', 'subscribed_at',
        'timezone', 'currency', 'locale',
        'branding', 'settings',
        'razorpay_customer_id', 'billing_email', 'billing_address', 'gstin',
    ];
    
    protected $casts = [
        'branding' => 'array',
        'settings' => 'array',
        'trial_ends_at' => 'datetime',
        'subscribed_at' => 'datetime',
    ];
    
    // Relationships
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function users() { return $this->hasMany(User::class); }
    public function events() { return $this->hasMany(Event::class); }
    public function bookings() { return $this->hasMany(Booking::class); }
    public function payments() { return $this->hasMany(Payment::class); }
    public function subscription() { return $this->hasOne(Subscription::class); }
    public function currentPlan() { return $this->belongsTo(SubscriptionPlan::class, 'current_plan_id'); }
    
    // Scopes
    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeTrial($query) { return $query->where('status', 'trial'); }
    
    // Methods
    public function isActive() { return $this->status === 'active'; }
    public function isOnTrial() { return $this->status === 'trial' && $this->trial_ends_at > now(); }
    public function hasExpiredTrial() { return $this->status === 'trial' && $this->trial_ends_at <= now(); }
}
```

#### Update Existing Models to Include Organization Scope
```php
// Add to User, Event, Booking, Payment, etc.

protected static function booted()
{
    // Auto-scope queries to current organization
    static::addGlobalScope('organization', function (Builder $builder) {
        if (auth()->check() && auth()->user()->organization_id) {
            $builder->where('organization_id', auth()->user()->organization_id);
        }
    });
    
    // Auto-assign organization_id on create
    static::creating(function ($model) {
        if (auth()->check() && !$model->organization_id) {
            $model->organization_id = auth()->user()->organization_id;
        }
    });
}
```

### Middleware

#### TenantMiddleware
```php
class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Extract organization from subdomain or domain
        $host = $request->getHost();
        
        // Check if custom domain
        $org = Organization::where('domain', $host)->first();
        
        // If not, check subdomain
        if (!$org) {
            $subdomain = explode('.', $host)[0];
            if ($subdomain !== 'www' && $subdomain !== config('app.domain_root')) {
                $org = Organization::where('slug', $subdomain)->first();
            }
        }
        
        if ($org) {
            // Set organization context
            app()->instance('currentOrganization', $org);
            $request->merge(['organization_id' => $org->id]);
            
            // Check if organization is active
            if (!$org->isActive() && !$org->isOnTrial()) {
                return redirect()->route('subscription.suspended');
            }
        }
        
        return $next($request);
    }
}
```

#### CheckSubscription
```php
class CheckSubscription
{
    public function handle(Request $request, Closure $next)
    {
        $org = app('currentOrganization');
        
        if (!$org) {
            abort(403, 'No organization context');
        }
        
        // Check trial expiration
        if ($org->hasExpiredTrial()) {
            return redirect()->route('subscription.expired');
        }
        
        // Check subscription status
        if ($org->status === 'suspended') {
            return redirect()->route('subscription.suspended');
        }
        
        return $next($request);
    }
}
```

#### CheckUsageLimits
```php
class CheckUsageLimits
{
    public function handle(Request $request, Closure $next)
    {
        $org = app('currentOrganization');
        $plan = $org->currentPlan;
        
        if (!$plan) {
            return $next($request); // No limits without plan
        }
        
        // Check based on action
        $action = $request->route()->getActionMethod();
        
        if (in_array($action, ['store', 'create'])) {
            $resource = $this->getResourceFromRoute($request->route());
            
            switch ($resource) {
                case 'events':
                    $count = $org->events()->count();
                    if ($count >= $plan->max_events) {
                        return back()->with('error', 'Event limit reached. Please upgrade your plan.');
                    }
                    break;
                    
                case 'users':
                    $count = $org->users()->count();
                    if ($count >= $plan->max_team_members) {
                        return back()->with('error', 'Team member limit reached. Please upgrade your plan.');
                    }
                    break;
            }
        }
        
        return $next($request);
    }
}
```

### Updated Seeder

#### RoleSeeder (Updated)
```php
class RoleSeeder extends Seeder
{
    public function run()
    {
        // Platform-level role
        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);
        
        // Organization-level roles
        Role::firstOrCreate(['name' => 'org-owner', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'org-admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'org-member', 'guard_name' => 'web']);
        
        // Guest users (made bookings but not part of org)
        Role::firstOrCreate(['name' => 'guest', 'guard_name' => 'web']);
        
        // Migrate old roles
        DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'owner')
            ->update(['role_id' => Role::where('name', 'org-owner')->first()->id]);
            
        DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'admin')
            ->update(['role_id' => Role::where('name', 'org-admin')->first()->id]);
            
        DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'team-member')
            ->update(['role_id' => Role::where('name', 'org-member')->first()->id]);
            
        DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->where('roles.name', 'user')
            ->update(['role_id' => Role::where('name', 'guest')->first()->id]);
    }
}
```

---

## 📋 Phase 2: Subscription & Billing System ✅ COMPLETED

**Status:** ✅ Completed - February 8, 2024  
**Documentation:** See [phase-2-complete.md](phase-2-complete.md)

**Deliverables:**
- ✅ 5 database migrations (subscription_plans, subscriptions, invoices, payment_attempts, usage_records)
- ✅ 5 models (SubscriptionPlan, Subscription, Invoice, PaymentAttempt, UsageRecord)
- ✅ 3 services (SubscriptionService, UsageTrackingService, InvoiceService)
- ✅ 1 webhook controller (RazorpayWebhookController with 8 event handlers)
- ✅ 3 observers (EventObserver, BookingObserver, UserObserver)
- ✅ 1 seeder (SubscriptionPlanSeeder with 4 tiers)
- ✅ Razorpay integration with webhook signature verification
- ✅ Automated usage tracking via model observers
- ✅ 4 subscription plans (Starter, Growth, Business, Enterprise)
- ✅ Indian market pricing (₹999-₹19,999/month)
- ✅ Trial period management (14 days)
- ✅ Grace period handling (7 days)
- ✅ Invoice generation with PDF support
- ✅ Organization model relationships updated

### Objective
Implement Razorpay-based subscription billing for organization-to-platform payments.

### Database Schema

#### Migration 2.1: Subscription Plans
```sql
Schema::create('subscription_plans', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Starter, Growth, Business, Enterprise
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->text('features_list')->nullable(); // bullet points for marketing
    
    // Pricing
    $table->decimal('price_monthly', 10, 2);
    $table->decimal('price_yearly', 10, 2);
    $table->integer('discount_yearly_percent')->default(0); // e.g., 20% off yearly
    
    // Razorpay Plan IDs
    $table->string('razorpay_plan_id_monthly')->nullable();
    $table->string('razorpay_plan_id_yearly')->nullable();
    
    // Usage Limits
    $table->integer('max_events')->default(10);
    $table->integer('max_bookings_per_month')->default(100);
    $table->integer('max_team_members')->default(5);
    $table->integer('max_promo_codes')->default(10);
    
    // Feature Flags (boolean flags)
    $table->boolean('custom_domain')->default(false);
    $table->boolean('white_label')->default(false);
    $table->boolean('api_access')->default(false);
    $table->boolean('priority_support')->default(false);
    $table->boolean('advanced_analytics')->default(false);
    $table->boolean('google_calendar')->default(true);
    $table->boolean('email_reminders')->default(true);
    $table->boolean('remove_branding')->default(false);
    
    // Meta
    $table->boolean('is_active')->default(true);
    $table->boolean('is_featured')->default(false);
    $table->integer('sort_order')->default(0);
    $table->json('metadata')->nullable(); // additional config
    
    $table->timestamps();
    
    $table->index(['is_active', 'sort_order']);
});
```

#### Migration 2.2: Subscriptions
```sql
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->unique()->constrained()->onDelete('cascade');
    $table->foreignId('subscription_plan_id')->constrained()->onDelete('restrict');
    
    // Billing Cycle & Status
    $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
    $table->enum('status', ['trialing', 'active', 'past_due', 'cancelled', 'expired'])->default('trialing');
    
    // Razorpay Subscription Details
    $table->string('razorpay_subscription_id')->unique()->nullable();
    $table->string('razorpay_customer_id')->nullable();
    $table->string('razorpay_plan_id')->nullable();
    
    // Billing Periods
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamp('current_period_start')->nullable();
    $table->timestamp('current_period_end')->nullable();
    $table->timestamp('cancelled_at')->nullable();
    $table->timestamp('ends_at')->nullable(); // grace period end
    
    // Pricing Snapshot (lock in pricing)
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3)->default('INR');
    
    // Usage Tracking (reset monthly)
    $table->integer('events_used')->default(0);
    $table->integer('bookings_used')->default(0);
    $table->integer('team_members_used')->default(0);
    $table->timestamp('usage_reset_at')->nullable();
    
    // Cancellation
    $table->text('cancellation_reason')->nullable();
    $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->onDelete('set null');
    
    $table->timestamps();
    
    $table->index(['organization_id', 'status']);
    $table->index(['razorpay_subscription_id', 'status']);
});
```

#### Migration 2.3: Invoices
```sql
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('subscription_id')->nullable()->constrained()->onDelete('set null');
    
    // Invoice Details
    $table->string('invoice_number')->unique(); // INV-2026-0001
    $table->date('invoice_date');
    $table->date('due_date')->nullable();
    
    // Line Items (can store as JSON)
    $table->json('line_items'); // [{description, quantity, unit_price, total}]
    
    // Amounts
    $table->decimal('subtotal', 10, 2);
    $table->decimal('tax_amount', 10, 2)->default(0); // GST 18%
    $table->decimal('discount_amount', 10, 2)->default(0);
    $table->decimal('total_amount', 10, 2);
    $table->string('currency', 3)->default('INR');
    
    // Payment Status
    $table->enum('status', ['draft', 'pending', 'paid', 'failed', 'refunded'])->default('pending');
    $table->timestamp('paid_at')->nullable();
    
    // Razorpay Payment Details
    $table->string('razorpay_payment_id')->nullable();
    $table->string('razorpay_order_id')->nullable();
    $table->string('razorpay_invoice_id')->nullable();
    $table->json('payment_metadata')->nullable();
    
    // File Storage
    $table->string('pdf_path')->nullable(); // storage path
    
    $table->timestamps();
    
    $table->index(['organization_id', 'status', 'invoice_date']);
    $table->index('invoice_number');
    $table->index(['razorpay_payment_id', 'status']);
});
```

#### Migration 2.4: Payment Attempts Log
```sql
Schema::create('payment_attempts', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
    $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');
    
    $table->string('razorpay_payment_id')->nullable();
    $table->decimal('amount', 10, 2);
    $table->string('currency', 3)->default('INR');
    
    $table->enum('status', ['pending', 'authorized', 'captured', 'failed'])->default('pending');
    $table->text('failure_reason')->nullable();
    $table->json('gateway_response')->nullable();
    
    $table->timestamps();
    
    $table->index(['subscription_id', 'status', 'created_at']);
});
```

#### Migration 2.5: Usage Tracking
```sql
Schema::create('usage_records', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->date('period_date'); // YYYY-MM-DD (monthly aggregation)
    $table->string('metric'); // events_count, bookings_count, team_members_count
    $table->integer('value')->default(0);
    $table->json('metadata')->nullable(); // breakdown details
    
    $table->timestamps();
    
    $table->unique(['organization_id', 'period_date', 'metric']);
    $table->index(['organization_id', 'period_date']);
});
```

### Services

#### SubscriptionService
```php
class SubscriptionService
{
    protected $razorpay;
    
    public function __construct()
    {
        $key = config('services.razorpay.key');
        $secret = config('services.razorpay.secret');
        $this->razorpay = new \Razorpay\Api\Api($key, $secret);
    }
    
    /**
     * Create a new subscription for an organization
     */
    public function createSubscription(Organization $org, SubscriptionPlan $plan, string $billingCycle): Subscription
    {
        // Create Razorpay customer if doesn't exist
        if (!$org->razorpay_customer_id) {
            $customer = $this->razorpay->customer->create([
                'name' => $org->name,
                'email' => $org->billing_email ?: $org->contact_email,
                'contact' => $org->contact_phone,
                'notes' => [
                    'organization_id' => $org->id,
                ]
            ]);
            $org->update(['razorpay_customer_id' => $customer->id]);
        }
        
        // Determine plan ID based on billing cycle
        $razorpayPlanId = $billingCycle === 'yearly' 
            ? $plan->razorpay_plan_id_yearly 
            : $plan->razorpay_plan_id_monthly;
            
        $amount = $billingCycle === 'yearly' 
            ? $plan->price_yearly 
            : $plan->price_monthly;
        
        // Create Razorpay subscription
        $razorpaySub = $this->razorpay->subscription->create([
            'plan_id' => $razorpayPlanId,
            'customer_id' => $org->razorpay_customer_id,
            'total_count' => 12, // 12 billing cycles then auto-renew
            'quantity' => 1,
            'start_at' => now()->addDays(14)->timestamp, // after trial
            'customer_notify' => 1,
            'notes' => [
                'organization_id' => $org->id,
                'plan_name' => $plan->name,
            ]
        ]);
        
        // Create local subscription record
        $subscription = Subscription::create([
            'organization_id' => $org->id,
            'subscription_plan_id' => $plan->id,
            'billing_cycle' => $billingCycle,
            'status' => 'trialing',
            'razorpay_subscription_id' => $razorpaySub->id,
            'razorpay_customer_id' => $org->razorpay_customer_id,
            'razorpay_plan_id' => $razorpayPlanId,
            'trial_ends_at' => now()->addDays(14),
            'current_period_start' => now(),
            'current_period_end' => now()->addMonth(),
            'amount' => $amount,
            'currency' => 'INR',
            'usage_reset_at' => now()->startOfMonth()->addMonth(),
        ]);
        
        // Update organization
        $org->update([
            'current_plan_id' => $plan->id,
            'status' => 'trial',
            'trial_ends_at' => now()->addDays(14),
        ]);
        
        return $subscription;
    }
    
    /**
     * Upgrade/downgrade subscription
     */
    public function changePlan(Subscription $subscription, SubscriptionPlan $newPlan): void
    {
        // Calculate prorated amount
        // Update Razorpay subscription
        // Update local record
    }
    
    /**
     * Cancel subscription
     */
    public function cancelSubscription(Subscription $subscription, bool $immediately = false): void
    {
        if ($immediately) {
            $this->razorpay->subscription->fetch($subscription->razorpay_subscription_id)->cancel();
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'ends_at' => now(),
            ]);
        } else {
            // Cancel at period end
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'ends_at' => $subscription->current_period_end,
            ]);
        }
        
        $subscription->organization->update(['status' => 'cancelled']);
    }
    
    /**
     * Resume cancelled subscription
     */
    public function resumeSubscription(Subscription $subscription): void
    {
        $this->razorpay->subscription->fetch($subscription->razorpay_subscription_id)->resume();
        
        $subscription->update([
            'status' => 'active',
            'cancelled_at' => null,
            'ends_at' => null,
        ]);
        
        $subscription->organization->update(['status' => 'active']);
    }
}
```

#### UsageTrackingService
```php
class UsageTrackingService
{
    public function recordUsage(Organization $org, string $metric, int $value = 1): void
    {
        $today = now()->toDateString();
        
        UsageRecord::updateOrCreate(
            [
                'organization_id' => $org->id,
                'period_date' => $today,
                'metric' => $metric,
            ],
            [
                'value' => DB::raw("value + {$value}"),
            ]
        );
        
        // Update subscription usage counters
        if ($org->subscription) {
            switch ($metric) {
                case 'events_created':
                    $org->subscription->increment('events_used');
                    break;
                case 'bookings_created':
                    $org->subscription->increment('bookings_used');
                    break;
                case 'team_members_added':
                    $org->subscription->increment('team_members_used');
                    break;
            }
        }
    }
    
    public function checkLimit(Organization $org, string $resource): bool
    {
        $plan = $org->currentPlan;
        
        if (!$plan) return true; // No limits
        
        switch ($resource) {
            case 'events':
                return $org->events()->count() < $plan->max_events;
            case 'bookings':
                return $org->bookings()->whereMonth('created_at', now()->month)->count() < $plan->max_bookings_per_month;
            case 'team_members':
                return $org->users()->count() < $plan->max_team_members;
            default:
                return true;
        }
    }
}
```

### Razorpay Webhooks Controller
```php
class RazorpayWebhookController extends Controller
{
    public function handleSubscriptionWebhook(Request $request)
    {
        $payload = $request->all();
        $event = $payload['event'];
        $subscription = $payload['payload']['subscription']['entity'];
        
        // Verify webhook signature
        $this->verifyWebhookSignature($request);
        
        // Find local subscription
        $localSub = Subscription::where('razorpay_subscription_id', $subscription['id'])->first();
        
        if (!$localSub) {
            Log::error('Subscription not found for webhook', ['subscription_id' => $subscription['id']]);
            return response()->json(['status' => 'error'], 404);
        }
        
        switch ($event) {
            case 'subscription.activated':
                $localSub->update(['status' => 'active']);
                $localSub->organization->update(['status' => 'active', 'subscribed_at' => now()]);
                break;
                
            case 'subscription.charged':
                // Create invoice record
                $this->createInvoiceFromCharge($localSub, $payload['payload']['payment']['entity']);
                break;
                
            case 'subscription.pending':
                $localSub->update(['status' => 'past_due']);
                break;
                
            case 'subscription.cancelled':
                $localSub->update(['status' => 'cancelled', 'cancelled_at' => now()]);
                $localSub->organization->update(['status' => 'cancelled']);
                break;
                
            case 'subscription.completed':
                $localSub->update(['status' => 'expired', 'ends_at' => now()]);
                break;
                
            case 'subscription.paused':
                $localSub->update(['status' => 'past_due']);
                $localSub->organization->update(['status' => 'suspended']);
                break;
                
            case 'subscription.resumed':
                $localSub->update(['status' => 'active']);
                $localSub->organization->update(['status' => 'active']);
                break;
        }
        
        return response()->json(['status' => 'success']);
    }
}
```

### Seeder for Subscription Plans
```php
class SubscriptionPlanSeeder extends Seeder
{
    public function run()
    {
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for getting started',
                'price_monthly' => 999,
                'price_yearly' => 9990, // 17% off
                'discount_yearly_percent' => 17,
                'max_events' => 10,
                'max_bookings_per_month' => 100,
                'max_team_members' => 3,
                'max_promo_codes' => 5,
                'google_calendar' => true,
                'email_reminders' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Growth',
                'slug' => 'growth',
                'description' => 'For growing teams',
                'price_monthly' => 2999,
                'price_yearly' => 29990, // 17% off
                'discount_yearly_percent' => 17,
                'max_events' => 50,
                'max_bookings_per_month' => 500,
                'max_team_members' => 10,
                'max_promo_codes' => 20,
                'google_calendar' => true,
                'email_reminders' => true,
                'advanced_analytics' => true,
                'api_access' => true,
                'sort_order' => 2,
                'is_featured' => true,
            ],
            [
                'name' => 'Business',
                'slug' => 'business',
                'description' => 'For established businesses',
                'price_monthly' => 7999,
                'price_yearly' => 79990, // 17% off
                'discount_yearly_percent' => 17,
                'max_events' => 200,
                'max_bookings_per_month' => 2000,
                'max_team_members' => 50,
                'max_promo_codes' => 50,
                'custom_domain' => true,
                'white_label' => true,
                'api_access' => true,
                'priority_support' => true,
                'advanced_analytics' => true,
                'google_calendar' => true,
                'email_reminders' => true,
                'remove_branding' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Custom solutions for large organizations',
                'price_monthly' => 19999,
                'price_yearly' => 199990, // 17% off
                'discount_yearly_percent' => 17,
                'max_events' => 999999, // unlimited
                'max_bookings_per_month' => 999999,
                'max_team_members' => 999999,
                'max_promo_codes' => 999999,
                'custom_domain' => true,
                'white_label' => true,
                'api_access' => true,
                'priority_support' => true,
                'advanced_analytics' => true,
                'google_calendar' => true,
                'email_reminders' => true,
                'remove_branding' => true,
                'sort_order' => 4,
            ],
        ];
        
        foreach ($plans as $plan) {
            SubscriptionPlan::firstOrCreate(['slug' => $plan['slug']], $plan);
        }
    }
}
```

---

## 📋 Phase 3: Team Management & Permissions (Week 5)

### Features
1. **Invite Team Members**: Send email invitations with role assignment
2. **Role Management**: Granular permissions for org-owner, org-admin, org-member
3. **Team Directory**: View all team members, their roles, and activity
4. **Remove Members**: Revoke access and optionally reassign their data

### Permission Matrix

| Permission                    | org-owner | org-admin | org-member | guest |
|-------------------------------|-----------|-----------|------------|-------|
| Manage Billing & Subscription | ✅        | ❌       | ❌         | ❌ |
| View Organization Settings    | ✅        | ✅       | ❌         | ❌ |
| Edit Organization Settings    | ✅        | ✅       | ❌         | ❌ |
| Invite Team Members           | ✅        | ✅       | ❌         | ❌ |
| Remove Team Members           | ✅        | ✅       | ❌         | ❌ |
| Create Events                 | ✅        | ✅       | ✅         | ❌ |
| Edit Own Events               | ✅        | ✅       | ✅         | ❌ |
| Edit Others' Events           | ✅        | ✅       | ❌         | ❌ |
| Delete Events                 | ✅        | ✅       | Own Only   | ❌ |
| View All Bookings             | ✅        | ✅       | Own Events | Own Only |
| Cancel Bookings               | ✅        | ✅       | Own Events | Own Only |
| Manage Promo Codes            | ✅        | ✅       | ❌         | ❌ |
| View Analytics                | ✅        | ✅       | Own Events | ❌ |
| Manage Payment Gateway        | ✅        | ✅       | ❌         | ❌ |
| Process Refunds               | ✅        | ✅       | ❌         | ❌ |

### Policies

#### OrganizationPolicy
```php
class OrganizationPolicy
{
    public function viewSettings(User $user, Organization $org)
    {
        return $user->organization_id === $org->id 
            && $user->hasAnyRole(['org-owner', 'org-admin']);
    }
    
    public function updateSettings(User $user, Organization $org)
    {
        return $user->organization_id === $org->id 
            && $user->hasAnyRole(['org-owner', 'org-admin']);
    }
    
    public function manageBilling(User $user, Organization $org)
    {
        return $user->organization_id === $org->id 
            && $user->hasRole('org-owner');
    }
    
    public function inviteMembers(User $user, Organization $org)
    {
        return $user->organization_id === $org->id 
            && $user->hasAnyRole(['org-owner', 'org-admin']);
    }
}
```

---

## 📋 Phase 4: Subdomain & Custom Domain Support (Week 6)

### Infrastructure Setup

#### DNS Configuration
- Wildcard DNS: `*.meetflow.app` → Server IP
- Main domain: `meetflow.app` → Marketing site
- App dashboard: `app.meetflow.app` → Platform admin

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name *.meetflow.app meetflow.app;
    
    root /var/www/meetflow/public;
    index index.php;
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Database Schema

#### Migration 4.1: Organization Domains
```sql
Schema::create('organization_domains', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->string('domain')->unique();
    $table->enum('type', ['subdomain', 'custom'])->default('subdomain');
    $table->enum('status', ['pending_verification', 'verified', 'failed'])->default('pending_verification');
    
    // DNS Verification
    $table->string('verification_token', 64)->nullable();
    $table->string('verification_record_type')->default('TXT'); // TXT or CNAME
    $table->string('verification_record_name')->nullable();
    $table->string('verification_record_value')->nullable();
    $table->timestamp('verified_at')->nullable();
    
    // SSL
    $table->boolean('ssl_enabled')->default(false);
    $table->timestamp('ssl_issued_at')->nullable();
    
    // Meta
    $table->boolean('is_primary')->default(false);
    $table->timestamps();
    
    $table->index(['organization_id', 'type', 'status']);
    $table->index(['domain', 'status']);
});
```

### Controllers

#### OrganizationDomainController
```php
class OrganizationDomainController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'domain' => 'required|string|unique:organization_domains,domain',
        ]);
        
        $org = auth()->user()->organization;
        
        // Check if plan allows custom domain
        if (!$org->currentPlan->custom_domain) {
            return back()->with('error', 'Custom domain not available in your plan. Please upgrade.');
        }
        
        // Generate verification token
        $token = Str::random(32);
        
        $domain = $org->domains()->create([
            'domain' => $request->domain,
            'type' => 'custom',
            'status' => 'pending_verification',
            'verification_token' => $token,
            'verification_record_type' => 'TXT',
            'verification_record_name' => '_meetflow-verify',
            'verification_record_value' => $token,
        ]);
        
        return back()->with('success', 'Domain added. Please add the TXT record to verify ownership.');
    }
    
    public function verify(OrganizationDomain $domain)
    {
        // Check DNS records
        $records = dns_get_record("_meetflow-verify.{$domain->domain}", DNS_TXT);
        
        $verified = false;
        foreach ($records as $record) {
            if (isset($record['txt']) && $record['txt'] === $domain->verification_token) {
                $verified = true;
                break;
            }
        }
        
        if ($verified) {
            $domain->update([
                'status' => 'verified',
                'verified_at' => now(),
            ]);
            
            // Issue SSL certificate (using Let's Encrypt + Certbot)
            dispatch(new IssueSslCertificateJob($domain));
            
            return back()->with('success', 'Domain verified successfully!');
        }
        
        return back()->with('error', 'Domain verification failed. Please check your DNS records.');
    }
}
```

### Middleware Update

#### TenantMiddleware (Enhanced)
```php
class TenantMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $host = $request->getHost();
        
        // Skip for main platform domain
        if ($host === config('app.platform_domain')) {
            return $next($request);
        }
        
        // Check custom domain first
        $domain = OrganizationDomain::where('domain', $host)
            ->where('status', 'verified')
            ->first();
        
        if ($domain) {
            $org = $domain->organization;
        } else {
            // Check subdomain
            $subdomain = explode('.', $host)[0];
            $org = Organization::where('slug', $subdomain)->first();
        }
        
        if (!$org) {
            abort(404, 'Organization not found');
        }
        
        // Set organization context
        app()->instance('currentOrganization', $org);
        view()->share('currentOrganization', $org);
        
        // Check organization status
        if ($org->status === 'suspended') {
            return redirect()->route('organization.suspended');
        }
        
        if ($org->hasExpiredTrial() && !$org->subscription) {
            return redirect()->route('organization.trial-expired');
        }
        
        return $next($request);
    }
}
```

---

## 📋 Phase 5: White-Label & Branding (Week 7)

### Features
1. **Custom Logo**: Upload organization logo
2. **Color Scheme**: Primary and secondary colors
3. **Custom CSS**: Advanced styling (Business+ plans)
4. **Email Branding**: Custom email templates with org branding
5. **Remove Platform Branding**: "Powered by MeetFlow" removal (Business+ plans)
6. **Favicon**: Custom favicon for booking pages

### Database Schema

#### Update organizations table branding JSON structure
```json
{
  "logo_url": "https://cdn.meetflow.app/orgs/123/logo.png",
  "favicon_url": "https://cdn.meetflow.app/orgs/123/favicon.ico",
  "primary_color": "#6366f1",
  "secondary_color": "#8b5cf6",
  "booking_page_background": "#ffffff",
  "custom_css": "body { font-family: 'Inter', sans-serif; }",
  "custom_js": "console.log('Custom tracking');",
  "email_header_image": "https://cdn.meetflow.app/orgs/123/email-header.png",
  "email_footer_text": "© 2026 Acme Corp. All rights reserved."
}
```

### Controllers

#### OrganizationBrandingController
```php
class OrganizationBrandingController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:512',
            'primary_color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'secondary_color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'custom_css' => 'nullable|string',
        ]);
        
        $org = auth()->user()->organization;
        
        // Check plan permissions
        if ($request->custom_css && !$org->currentPlan->white_label) {
            return back()->with('error', 'Custom CSS requires Business plan or higher.');
        }
        
        $branding = $org->branding ?? [];
        
        // Handle logo upload
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store("organizations/{$org->id}/branding", 's3');
            $branding['logo_url'] = Storage::disk('s3')->url($path);
        }
        
        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store("organizations/{$org->id}/branding", 's3');
            $branding['favicon_url'] = Storage::disk('s3')->url($path);
        }
        
        // Update colors
        $branding['primary_color'] = $request->primary_color;
        $branding['secondary_color'] = $request->secondary_color ?? $request->primary_color;
        
        // Update custom CSS (sanitize for security)
        if ($request->custom_css) {
            $branding['custom_css'] = $this->sanitizeCss($request->custom_css);
        }
        
        $org->update(['branding' => $branding]);
        
        return back()->with('success', 'Branding updated successfully!');
    }
    
    protected function sanitizeCss(string $css): string
    {
        // Strip dangerous CSS (e.g., external imports, JS)
        $css = preg_replace('/@import\s+url\([^)]+\);/', '', $css);
        $css = preg_replace('/javascript:[^;]+;/', '', $css);
        return $css;
    }
}
```

### Views

#### Inject Branding into Booking Pages
```php
// In BookingController@show
public function show($username, $eventSlug)
{
    $org = app('currentOrganization');
    
    return Inertia::render('Public/BookingPage', [
        'event' => $event,
        'branding' => [
            'logo_url' => $org->branding['logo_url'] ?? null,
            'primary_color' => $org->branding['primary_color'] ?? '#6366f1',
            'secondary_color' => $org->branding['secondary_color'] ?? '#8b5cf6',
            'custom_css' => $org->branding['custom_css'] ?? null,
            'show_powered_by' => !$org->currentPlan->remove_branding,
        ],
    ]);
}
```

---

## 📋 Phase 6: Public API & Webhooks (Week 8-9)

### API Features
- RESTful API with versioning (v1)
- OAuth 2.0 or API key authentication
- Rate limiting per organization
- Comprehensive API documentation (auto-generated with Swagger)
- SDK for popular languages (JavaScript, Python, PHP)

### Database Schema

#### Migration 6.1: API Keys
```sql
Schema::create('api_keys', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    
    $table->string('name'); // "Production Server", "Dev Environment"
    $table->string('key', 64)->unique(); // hashed in DB
    $table->string('prefix', 12); // first 12 chars for display: mf_live_abc123
    
    // Permissions (JSON array of scopes)
    $table->json('scopes'); // ['events:read', 'events:write', 'bookings:read']
    
    // Usage Tracking
    $table->timestamp('last_used_at')->nullable();
    $table->ipAddress('last_used_ip')->nullable();
    $table->integer('request_count')->default(0);
    
    // Rate Limiting
    $table->integer('rate_limit_per_minute')->default(60);
    
    // Status
    $table->boolean('is_active')->default(true);
    $table->timestamp('expires_at')->nullable();
    
    $table->timestamps();
    
    $table->index(['organization_id', 'is_active']);
    $table->index('key');
    $table->index('prefix');
});
```

#### Migration 6.2: API Request Logs
```sql
Schema::create('api_request_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('api_key_id')->constrained()->onDelete('cascade');
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->string('method', 10); // GET, POST, PUT, DELETE
    $table->string('endpoint', 255);
    $table->integer('status_code');
    $table->integer('response_time_ms');
    
    $table->ipAddress('ip_address');
    $table->text('user_agent')->nullable();
    
    // Only log for debugging (auto-delete after 30 days)
    $table->json('request_payload')->nullable();
    $table->json('response_payload')->nullable();
    
    $table->timestamp('created_at');
    
    $table->index(['api_key_id', 'created_at']);
    $table->index(['organization_id', 'created_at']);
    $table->index('status_code');
});
```

#### Migration 6.3: Webhooks
```sql
Schema::create('webhooks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    
    $table->string('name');
    $table->string('url');
    $table->string('secret', 64); // for HMAC signature verification
    
    // Events to listen to (JSON array)
    $table->json('events'); // ['booking.created', 'booking.cancelled', 'payment.completed']
    
    // Configuration
    $table->boolean('is_active')->default(true);
    $table->integer('timeout_seconds')->default(30);
    $table->integer('max_retry_attempts')->default(3);
    
    // Stats
    $table->timestamp('last_triggered_at')->nullable();
    $table->integer('success_count')->default(0);
    $table->integer('failure_count')->default(0);
    
    $table->timestamps();
    
    $table->index(['organization_id', 'is_active']);
});
```

#### Migration 6.4: Webhook Deliveries
```sql
Schema::create('webhook_deliveries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('webhook_id')->constrained()->onDelete('cascade');
    
    $table->string('event_type'); // booking.created
    $table->json('payload');
    $table->string('signature'); // HMAC signature
    
    $table->enum('status', ['pending', 'sent', 'failed', 'retrying'])->default('pending');
    $table->integer('attempt_count')->default(0);
    $table->integer('http_status_code')->nullable();
    $table->text('response_body')->nullable();
    $table->text('error_message')->nullable();
    
    $table->timestamp('next_retry_at')->nullable();
    $table->timestamp('sent_at')->nullable();
    
    $table->timestamps();
    
    $table->index(['webhook_id', 'status', 'created_at']);
    $table->index(['status', 'next_retry_at']);
});
```

### API Routes

#### routes/api_v1.php
```php
Route::prefix('v1')->middleware('api.auth')->group(function () {
    
    // Events
    Route::apiResource('events', Api\V1\EventController::class);
    
    // Bookings
    Route::apiResource('bookings', Api\V1\BookingController::class);
    Route::post('bookings/{booking}/cancel', [Api\V1\BookingController::class, 'cancel']);
    
    // Payments
    Route::get('payments', [Api\V1\PaymentController::class, 'index']);
    Route::get('payments/{payment}', [Api\V1\PaymentController::class, 'show']);
    
    // Promo Codes
    Route::apiResource('promo-codes', Api\V1\PromoCodeController::class);
    Route::post('promo-codes/{code}/validate', [Api\V1\PromoCodeController::class, 'validate']);
    
    // Webhooks
    Route::apiResource('webhooks', Api\V1\WebhookController::class);
    Route::post('webhooks/{webhook}/test', [Api\V1\WebhookController::class, 'test']);
    
    // Analytics
    Route::get('analytics/overview', [Api\V1\AnalyticsController::class, 'overview']);
    Route::get('analytics/bookings', [Api\V1\AnalyticsController::class, 'bookings']);
});
```

### Middleware

#### ApiKeyAuthMiddleware
```php
class ApiKeyAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->bearerToken();
        
        if (!$apiKey) {
            return response()->json(['error' => 'API key required'], 401);
        }
        
        // Hash the key to compare
        $hashedKey = hash('sha256', $apiKey);
        
        $apiKeyRecord = ApiKey::where('key', $hashedKey)
            ->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->first();
        
        if (!$apiKeyRecord) {
            return response()->json(['error' => 'Invalid API key'], 401);
        }
        
        // Check rate limit
        $rateLimitKey = "api_rate_limit:{$apiKeyRecord->id}:" . now()->format('Y-m-d-H-i');
        $requests = Cache::increment($rateLimitKey);
        Cache::put($rateLimitKey, $requests, 60); // 1 minute TTL
        
        if ($requests > $apiKeyRecord->rate_limit_per_minute) {
            return response()->json(['error' => 'Rate limit exceeded'], 429);
        }
        
        // Log usage
        $apiKeyRecord->update([
            'last_used_at' => now(),
            'last_used_ip' => $request->ip(),
        ]);
        $apiKeyRecord->increment('request_count');
        
        // Set organization context
        $request->merge(['organization_id' => $apiKeyRecord->organization_id]);
        app()->instance('currentOrganization', $apiKeyRecord->organization);
        
        return $next($request);
    }
}
```

### Services

#### WebhookDispatcher
```php
class WebhookDispatcher
{
    public function dispatch(string $eventType, array $payload): void
    {
        $org = app('currentOrganization');
        
        // Find all webhooks listening to this event
        $webhooks = Webhook::where('organization_id', $org->id)
            ->where('is_active', true)
            ->whereJsonContains('events', $eventType)
            ->get();
        
        foreach ($webhooks as $webhook) {
            // Create delivery record
            $signature = $this->generateSignature($payload, $webhook->secret);
            
            $delivery = $webhook->deliveries()->create([
                'event_type' => $eventType,
                'payload' => $payload,
                'signature' => $signature,
                'status' => 'pending',
            ]);
            
            // Dispatch job to send webhook
            dispatch(new SendWebhookJob($delivery));
        }
    }
    
    protected function generateSignature(array $payload, string $secret): string
    {
        return hash_hmac('sha256', json_encode($payload), $secret);
    }
}
```

#### SendWebhookJob
```php
class SendWebhookJob implements ShouldQueue
{
    protected $delivery;
    
    public function handle()
    {
        $webhook = $this->delivery->webhook;
        
        try {
            $response = Http::timeout($webhook->timeout_seconds)
                ->withHeaders([
                    'X-MeetFlow-Signature' => $this->delivery->signature,
                    'X-MeetFlow-Event' => $this->delivery->event_type,
                ])
                ->post($webhook->url, $this->delivery->payload);
            
            if ($response->successful()) {
                $this->delivery->update([
                    'status' => 'sent',
                    'http_status_code' => $response->status(),
                    'response_body' => $response->body(),
                    'sent_at' => now(),
                ]);
                
                $webhook->increment('success_count');
                $webhook->update(['last_triggered_at' => now()]);
            } else {
                throw new Exception("HTTP {$response->status()}: {$response->body()}");
            }
            
        } catch (Exception $e) {
            $this->delivery->increment('attempt_count');
            $this->delivery->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
            
            $webhook->increment('failure_count');
            
            // Retry logic
            if ($this->delivery->attempt_count < $webhook->max_retry_attempts) {
                $nextRetry = now()->addMinutes(pow(2, $this->delivery->attempt_count)); // exponential backoff
                $this->delivery->update([
                    'status' => 'retrying',
                    'next_retry_at' => $nextRetry,
                ]);
                
                SendWebhookJob::dispatch($this->delivery)->delay($nextRetry);
            }
        }
    }
}
```

---

## 📋 Phase 7: Super Admin Panel (Week 10)

### Features
1. **Platform Dashboard**: Revenue, MRR, active organizations, total users
2. **Organization Management**: View, suspend, activate, delete organizations
3. **Subscription Management**: Override plans, apply credits, cancel subscriptions
4. **User Impersonation**: Login as any organization owner for support
5. **System Health**: Queue status, failed jobs, error logs
6. **Analytics**: Churn rate, conversion rate, ARPU, LTV

### Routes

#### routes/superadmin.php
```php
Route::prefix('superadmin')
    ->middleware(['auth', 'super.admin'])
    ->name('superadmin.')
    ->group(function () {
        
        // Dashboard
        Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        
        // Organizations
        Route::resource('organizations', SuperAdminOrganizationController::class);
        Route::post('organizations/{org}/suspend', [SuperAdminOrganizationController::class, 'suspend']);
        Route::post('organizations/{org}/activate', [SuperAdminOrganizationController::class, 'activate']);
        Route::post('organizations/{org}/impersonate', [SuperAdminOrganizationController::class, 'impersonate']);
        
        // Subscriptions
        Route::get('subscriptions', [SuperAdminSubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::post('subscriptions/{sub}/override-plan', [SuperAdminSubscriptionController::class, 'overridePlan']);
        Route::post('subscriptions/{sub}/apply-credit', [SuperAdminSubscriptionController::class, 'applyCredit']);
        
        // Plans
        Route::resource('plans', SuperAdminPlanController::class);
        
        // System
        Route::get('system/health', [SuperAdminSystemController::class, 'health'])->name('system.health');
        Route::get('system/jobs', [SuperAdminSystemController::class, 'jobs'])->name('system.jobs');
        Route::get('system/logs', [SuperAdminSystemController::class, 'logs'])->name('system.logs');
        
        // Analytics
        Route::get('analytics', [SuperAdminAnalyticsController::class, 'index'])->name('analytics');
    });
```

### Middleware

#### SuperAdminMiddleware
```php
class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check() || !auth()->user()->is_super_admin) {
            abort(403, 'Unauthorized access');
        }
        
        return $next($request);
    }
}
```

---

## 📋 Phase 8: Optimization & Scaling (Week 11-12)

### Performance Optimizations

#### Database Indexes (Priority)
```sql
-- Users
ALTER TABLE users ADD INDEX idx_org_role (organization_id, is_super_admin);
ALTER TABLE users ADD INDEX idx_email_verified (email, email_verified_at);

-- Events
ALTER TABLE events ADD INDEX idx_org_user_date (organization_id, user_id, available_from_date);
ALTER TABLE events ADD INDEX idx_slug_deleted (slug, deleted_at);

-- Bookings
ALTER TABLE bookings ADD INDEX idx_org_status_date (organization_id, status, booked_at_date);
ALTER TABLE bookings ADD INDEX idx_event_date (event_id, booked_at_date, status);
ALTER TABLE bookings ADD INDEX idx_user_status (user_id, status, created_at);

-- Payments
ALTER TABLE payments ADD INDEX idx_org_status (organization_id, status, created_at);
ALTER TABLE payments ADD INDEX idx_booking_status (booking_id, status);

-- Subscriptions
ALTER TABLE subscriptions ADD INDEX idx_status_period (status, current_period_end);
ALTER TABLE subscriptions ADD INDEX idx_razorpay_status (razorpay_subscription_id, status);

-- API Logs (with partitioning)
ALTER TABLE api_request_logs ADD INDEX idx_created_status (created_at, status_code);
```

#### Caching Strategy
```php
// Cache organization data
Cache::remember("org:{$orgId}", 3600, function () use ($orgId) {
    return Organization::with('currentPlan')->find($orgId);
});

// Cache subscription plan details
Cache::remember("plan:{$planId}", 86400, function () use ($planId) {
    return SubscriptionPlan::find($planId);
});

// Cache event timeslots (most expensive query)
Cache::remember("event:{$eventId}:timeslots:{$date}", 3600, function () use ($event, $date) {
    return $event->getAvailableTimeslots($date);
});
```

#### Queue Optimization
```php
// config/queue.php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => env('REDIS_QUEUE', 'default'),
        'retry_after' => 90,
        'block_for' => null,
    ],
],

// Separate queues for priority
'high' => [...], // Payment processing, refunds
'default' => [...], // Booking notifications
'low' => [...], // Analytics, logs
```

#### Eager Loading
```php
// In controllers/services
$bookings = Booking::with(['event.user', 'payment', 'tracking'])
    ->where('organization_id', $orgId)
    ->get();
```

### Scalability Considerations

#### Horizontal Scaling
- Load balancer (Nginx/HAProxy)
- Multiple application servers
- Database read replicas
- Redis cluster for caching and queues

#### Auto-Scaling (AWS Example)
```yaml
# docker-compose.yml or AWS ECS/Fargate config
services:
  app:
    image: meetflow:latest
    deploy:
      replicas: 3
      resources:
        limits:
          cpus: '0.5'
          memory: 512M
      restart_policy:
        condition: on-failure
```

#### CDN Setup
- Use CloudFlare or AWS CloudFront
- Cache static assets (logo, images, CSS, JS)
- Edge caching for public booking pages

---

## 🚀 Deployment Roadmap

### Pre-Launch Checklist

#### Technical
- [ ] All migrations tested on staging
- [ ] Database backups automated (daily)
- [ ] SSL certificates for wildcard domain
- [ ] Monitoring setup (New Relic, Sentry, DataDog)
- [ ] Queue workers scaled appropriately
- [ ] Redis configured and tested
- [ ] Email deliverability tested (SPF, DKIM, DMARC)
- [ ] Payment webhooks configured in Razorpay
- [ ] Rate limiting tested
- [ ] API documentation published

#### Business
- [ ] Pricing strategy finalized
- [ ] Terms of Service updated
- [ ] Privacy Policy updated for multi-tenancy
- [ ] Refund policy defined
- [ ] Support workflows established
- [ ] Billing email templates ready
- [ ] Trial expiration email flow tested

#### Marketing
- [ ] Landing page live
- [ ] Signup funnel tested
- [ ] Onboarding flow optimized
- [ ] Email drip campaign ready
- [ ] Documentation site live

### Launch Strategy

#### Phase 1: Soft Launch (Week 1-2)
- Migrate existing customers to default organization
- Grandfather existing customers (no subscription required)
- Beta access for 10-20 new organizations
- Collect feedback and fix bugs

#### Phase 2: Public Beta (Week 3-4)
- Open signups with waitlist
- 30-day trial (extended from 14 days)
- Aggressive onboarding support
- Feature iteration based on feedback

#### Phase 3: General Availability (Week 5+)
- Remove waitlist
- Standard 14-day trial
- Launch marketing campaigns
- Scale infrastructure as needed

---

## 📊 Success Metrics (KPIs)

### Revenue Metrics
- **MRR (Monthly Recurring Revenue)**: Target ₹5L in 6 months
- **ARR (Annual Recurring Revenue)**: Target ₹60L in 12 months
- **ARPU (Average Revenue Per User)**: ₹2,000-₹5,000
- **LTV (Customer Lifetime Value)**: Target ₹50,000

### Growth Metrics
- **Trial-to-Paid Conversion**: Target 25-30%
- **Monthly Churn Rate**: Keep below 5%
- **Net Revenue Retention**: Target 100%+
- **CAC (Customer Acquisition Cost)**: Keep below ₹5,000
- **LTV:CAC Ratio**: Target 10:1

### Product Metrics
- **Active Organizations**: Track weekly
- **Events per Organization**: Median & P90
- **Bookings per Organization**: Monthly trend
- **API Usage**: Requests per day
- **Support Tickets per 100 Customers**: Target < 5

### Technical Metrics
- **Uptime**: 99.9% SLA
- **API Response Time**: p95 < 200ms
- **Page Load Time**: < 2s
- **Error Rate**: < 0.1%
- **Queue Processing Time**: p95 < 5 minutes

---

## 💰 Updated Investment Estimate

### Development Costs (14 weeks)
- **Senior Developer**: 14 weeks × ₹1,00,000/week = ₹14,00,000
- **UI/UX Designer**: 30 hours × ₹2,000/hour = ₹60,000
- **QA Engineer**: 60 hours × ₹1,000/hour = ₹60,000
- **DevOps**: 20 hours × ₹2,500/hour = ₹50,000

**Total Development**: ₹15,70,000

### Infrastructure Costs (Monthly)
- **AWS/DigitalOcean**: ₹15,000-₹30,000
- **Database (RDS or Managed)**: ₹8,000-₹15,000
- **Redis**: ₹3,000-₹6,000
- **CDN (CloudFlare Pro)**: ₹2,000-₹5,000
- **Email (AWS SES)**: ₹2,000-₹8,000
- **Monitoring (New Relic)**: ₹5,000-₹10,000
- **Backups & Storage**: ₹3,000-₹5,000

**Total Infrastructure**: ₹38,000-₹79,000/month

### One-Time Costs
- **Domain**: ₹1,500/year
- **SSL (Wildcard)**: Free (Let's Encrypt)
- **Legal (Terms/Privacy)**: ₹25,000-₹50,000
- **Branding/Logo**: Already done

**Total One-Time**: ₹26,500-₹51,500

### **Grand Total**: ₹16,00,000-₹17,00,000 + ₹40,000-₹80,000/month

### Break-Even Analysis
- **Monthly Cost**: ₹80,000 (infra + support)
- **Average Plan Price**: ₹2,000/month
- **Break-even Organizations**: 40 paying customers
- **Estimated Timeline**: 4-6 months post-launch

---

## 🎯 Next Steps

### Immediate Actions (This Week)
1. **Run Data Migration**: Execute `MigrateToMultiTenant` command
2. **Update Role Names**: Run updated `RoleSeeder`
3. **Deploy Organization Model**: Merge Phase 1 code
4. **Test Multi-Tenancy**: Verify data isolation works

### Month 1
- Complete Phase 1 & 2 (Foundation + Billing)
- Set up Razorpay subscription plans
- Test end-to-end subscription flow
- Add usage tracking hooks

### Month 2
- Complete Phase 3 & 4 (Teams + Domains)
- Configure wildcard DNS
- Test subdomain routing
- Implement invitation system

### Month 3
- Complete Phase 5 & 6 (Branding + API)
- Launch beta program
- Onboard first 10 customers
- Iterate based on feedback

### Month 4+
- Public launch
- Marketing push
- Scale infrastructure
- Continuous optimization

---

## 📚 Technical Documentation

### Required Documentation
1. **API Documentation**: OpenAPI (Swagger) spec
2. **Webhook Guide**: Event types, payload structure, retry logic
3. **Migration Guide**: For existing single-tenant users
4. **Admin Guide**: For organization owners
5. **Developer Guide**: SDK usage, code examples

### Tools
- **API Docs**: Swagger UI / Postman
- **Webhooks**: Webhook.site for testing
- **SDK Generation**: OpenAPI Generator
- **Helpdesk**: Setup Crisp or Intercom

---

**End of Roadmap v2.0**

*This is a living document. Update as features are completed and priorities shift.*

---

## Quick Reference: Migration Order

1. ✅ Create organizations table
2. ✅ Add organization_id to all tables
3. ✅ Update roles (rename admin → org-admin, etc.)
4. ✅ Run data migration script
5. ✅ Add tenant middleware
6. ✅ Create subscription_plans table
7. ✅ Create subscriptions table
8. ✅ Create invoices table
9. ✅ Seed subscription plans
10. ✅ Build subscription UI
11. Continue with Phase 3...

---

**Status**: Ready for implementation  
**Estimated Timeline**: 12-14 weeks  
**Last Updated**: February 8, 2026
