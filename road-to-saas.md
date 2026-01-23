# Road to SaaS: MeetFlow Transformation Plan

**Objective:** Transform MeetFlow from a single-tenant booking platform into a fully-featured multi-tenant SaaS product.

**Timeline Estimate:** 8-12 weeks (depending on team size and complexity)

---

## 🎯 Vision: Multi-Tenant SaaS Platform

**Current State:** Single organization with admin/user roles
**Target State:** Multiple organizations, each with their own users, events, bookings, and subscriptions

**Key SaaS Characteristics:**
- Multi-tenant architecture with data isolation
- Subscription-based billing with multiple plans
- Self-service signup and onboarding
- Custom domains/subdomains per organization
- Usage-based analytics and limits
- White-label capabilities
- Public API for integrations
- Scalable infrastructure

---

## Phase 1: Foundation - Multi-Tenancy Architecture (Week 1-2)

### 🗄️ Database Migrations Required

#### Migration 1.1: Create Organizations/Workspaces Table
```sql
-- create_organizations_table.php
Schema::create('organizations', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique(); // for subdomain
    $table->string('domain')->nullable()->unique(); // custom domain
    $table->text('description')->nullable();
    $table->string('logo')->nullable();
    $table->string('timezone')->default('UTC');
    $table->string('currency')->default('INR');
    $table->string('locale')->default('en');
    
    // Subscription info
    $table->foreignId('subscription_plan_id')->nullable()->constrained()->onDelete('set null');
    $table->enum('status', ['active', 'suspended', 'cancelled', 'trial'])->default('trial');
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamp('subscribed_at')->nullable();
    
    // Limits (based on plan)
    $table->integer('max_events')->default(5);
    $table->integer('max_bookings_per_month')->default(50);
    $table->integer('max_team_members')->default(3);
    
    // Settings
    $table->json('settings')->nullable(); // org-specific settings
    $table->json('branding')->nullable(); // colors, fonts for white-label
    
    // Billing
    $table->string('billing_email')->nullable();
    $table->string('stripe_customer_id')->nullable();
    
    $table->timestamps();
    $table->softDeletes();
    
    $table->index(['slug', 'status']);
    $table->index('status');
});
```

#### Migration 1.2: Add organization_id to Existing Tables
```sql
-- add_organization_id_to_tables.php
// Add to: users, events, bookings, payments, promo_codes, refunds, settings

Schema::table('users', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->enum('role_in_org', ['owner', 'admin', 'member'])->default('member')->after('organization_id');
    $table->index('organization_id');
});

Schema::table('events', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->constrained()->onDelete('cascade');
    $table->index(['organization_id', 'user_id']);
});

Schema::table('bookings', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->constrained()->onDelete('cascade');
    $table->index(['organization_id', 'status']);
});

Schema::table('payments', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->constrained()->onDelete('cascade');
    $table->index('organization_id');
});

Schema::table('promo_codes', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->constrained()->onDelete('cascade');
    $table->index('organization_id');
});

Schema::table('refunds', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->constrained()->onDelete('cascade');
    $table->index('organization_id');
});

// Settings become org-specific
Schema::table('settings', function (Blueprint $table) {
    $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
    $table->unique(['organization_id', 'key']); // each org has own settings
});
```

#### Migration 1.3: Create Organization Invitations Table
```sql
-- create_organization_invitations_table.php
Schema::create('organization_invitations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('invited_by')->constrained('users')->onDelete('cascade');
    $table->string('email');
    $table->string('token')->unique();
    $table->enum('role', ['admin', 'member'])->default('member');
    $table->timestamp('accepted_at')->nullable();
    $table->timestamp('expires_at');
    $table->timestamps();
    
    $table->index(['organization_id', 'email']);
    $table->index('token');
});
```

#### Migration 1.4: Create Organization Activity Log
```sql
-- create_organization_activity_logs_table.php
Schema::create('organization_activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    $table->string('action'); // created_event, cancelled_booking, etc.
    $table->string('resource_type')->nullable(); // Event, Booking, etc.
    $table->unsignedBigInteger('resource_id')->nullable();
    $table->json('metadata')->nullable();
    $table->ipAddress('ip_address')->nullable();
    $table->timestamps();
    
    $table->index(['organization_id', 'created_at']);
    $table->index(['resource_type', 'resource_id']);
});
```

---

## Phase 2: Subscription & Billing System (Week 3-4)

### 🗄️ Database Migrations Required

#### Migration 2.1: Create Subscription Plans Table
```sql
-- create_subscription_plans_table.php
Schema::create('subscription_plans', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Starter, Professional, Enterprise
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->decimal('price_monthly', 10, 2);
    $table->decimal('price_yearly', 10, 2);
    $table->string('stripe_price_id_monthly')->nullable();
    $table->string('stripe_price_id_yearly')->nullable();
    $table->string('razorpay_plan_id_monthly')->nullable();
    $table->string('razorpay_plan_id_yearly')->nullable();
    
    // Limits
    $table->integer('max_events')->default(10);
    $table->integer('max_bookings_per_month')->default(100);
    $table->integer('max_team_members')->default(5);
    $table->boolean('custom_domain')->default(false);
    $table->boolean('white_label')->default(false);
    $table->boolean('api_access')->default(false);
    $table->boolean('priority_support')->default(false);
    $table->boolean('advanced_analytics')->default(false);
    
    // Features (JSON)
    $table->json('features')->nullable(); // ["Google Calendar", "Email Reminders", etc.]
    
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
    
    $table->index(['is_active', 'sort_order']);
});
```

#### Migration 2.2: Create Subscriptions Table
```sql
-- create_subscriptions_table.php
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('subscription_plan_id')->constrained()->onDelete('restrict');
    
    $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
    $table->enum('status', ['active', 'cancelled', 'past_due', 'expired', 'trial'])->default('trial');
    
    // Payment Gateway Info
    $table->string('gateway')->default('stripe'); // stripe, razorpay
    $table->string('gateway_subscription_id')->nullable();
    $table->string('gateway_customer_id')->nullable();
    
    // Dates
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamp('current_period_start')->nullable();
    $table->timestamp('current_period_end')->nullable();
    $table->timestamp('cancelled_at')->nullable();
    $table->timestamp('ends_at')->nullable(); // grace period end
    
    // Pricing
    $table->decimal('amount', 10, 2);
    $table->string('currency')->default('INR');
    
    // Usage tracking
    $table->integer('events_count')->default(0);
    $table->integer('bookings_count')->default(0);
    $table->integer('team_members_count')->default(0);
    $table->timestamp('usage_reset_at')->nullable();
    
    $table->timestamps();
    
    $table->index(['organization_id', 'status']);
    $table->index(['gateway', 'gateway_subscription_id']);
    $table->unique('organization_id'); // one active subscription per org
});
```

#### Migration 2.3: Create Subscription Invoices Table
```sql
-- create_subscription_invoices_table.php
Schema::create('subscription_invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->string('invoice_number')->unique();
    $table->decimal('amount', 10, 2);
    $table->decimal('tax_amount', 10, 2)->default(0);
    $table->decimal('total_amount', 10, 2);
    $table->string('currency')->default('INR');
    
    $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
    $table->string('gateway')->default('stripe');
    $table->string('gateway_invoice_id')->nullable();
    $table->string('gateway_payment_intent')->nullable();
    
    $table->timestamp('paid_at')->nullable();
    $table->timestamp('due_date')->nullable();
    
    $table->json('line_items')->nullable(); // breakdown of charges
    $table->string('invoice_pdf_url')->nullable();
    
    $table->timestamps();
    
    $table->index(['organization_id', 'status']);
    $table->index('invoice_number');
});
```

#### Migration 2.4: Create Usage Tracking Table
```sql
-- create_organization_usage_table.php
Schema::create('organization_usage', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->date('period_date'); // YYYY-MM-DD for monthly tracking
    $table->integer('events_created')->default(0);
    $table->integer('bookings_made')->default(0);
    $table->integer('bookings_cancelled')->default(0);
    $table->integer('payments_processed')->default(0);
    $table->decimal('revenue_generated', 10, 2)->default(0);
    $table->integer('reminders_sent')->default(0);
    $table->integer('active_team_members')->default(0);
    
    $table->timestamps();
    
    $table->unique(['organization_id', 'period_date']);
    $table->index(['organization_id', 'period_date']);
});
```

### 💡 Features to Build

1. **Subscription Management Dashboard**
   - View current plan and usage
   - Upgrade/downgrade plans
   - Cancel subscription (with grace period)
   - View billing history
   - Download invoices

2. **Stripe/Razorpay Integration**
   - Subscription creation via webhook
   - Automatic payment retry for failed charges
   - Prorated upgrades/downgrades
   - Subscription cancellation handling
   - Invoice generation

3. **Usage Limits Enforcement**
   - Middleware to check plan limits before actions
   - Soft limits (warnings) vs hard limits (blocks)
   - Upgrade prompts when approaching limits
   - Real-time usage tracking

4. **Trial Management**
   - 14-day free trial for new signups
   - Trial expiration notifications
   - Auto-conversion to paid or suspension

---

## Phase 3: Multi-Tenancy Features (Week 5-6)

### 🗄️ Database Migrations Required

#### Migration 3.1: Create Organization Domains Table
```sql
-- create_organization_domains_table.php
Schema::create('organization_domains', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->string('domain')->unique(); // custom.com
    $table->enum('type', ['subdomain', 'custom'])->default('subdomain');
    $table->enum('status', ['pending', 'verified', 'failed'])->default('pending');
    
    // DNS verification
    $table->string('verification_token')->nullable();
    $table->timestamp('verified_at')->nullable();
    $table->json('dns_records')->nullable(); // required DNS settings
    
    $table->boolean('is_primary')->default(false);
    $table->boolean('ssl_enabled')->default(false);
    
    $table->timestamps();
    
    $table->index(['organization_id', 'is_primary']);
    $table->index(['domain', 'status']);
});
```

#### Migration 3.2: Create Organization Settings Table (Separate from app settings)
```sql
-- create_organization_settings_table.php
Schema::create('organization_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->unique()->constrained()->onDelete('cascade');
    
    // Booking settings
    $table->integer('min_booking_notice_hours')->default(24);
    $table->integer('max_booking_advance_days')->default(60);
    $table->boolean('require_approval')->default(false);
    $table->boolean('allow_cancellation')->default(true);
    
    // Notification settings
    $table->boolean('send_booking_confirmations')->default(true);
    $table->boolean('send_reminder_emails')->default(true);
    $table->boolean('send_cancellation_emails')->default(true);
    $table->string('notification_email')->nullable();
    
    // Payment settings (org-specific gateways)
    $table->string('payment_gateway')->default('razorpay');
    $table->json('razorpay_credentials')->nullable(); // encrypted
    $table->json('stripe_credentials')->nullable(); // encrypted
    $table->boolean('collect_payment_upfront')->default(true);
    
    // Branding
    $table->string('primary_color')->default('#6366f1');
    $table->string('logo_url')->nullable();
    $table->string('favicon_url')->nullable();
    $table->text('custom_css')->nullable();
    $table->text('custom_js')->nullable();
    
    // SEO
    $table->string('meta_title')->nullable();
    $table->text('meta_description')->nullable();
    $table->string('meta_image')->nullable();
    
    // Integrations
    $table->string('google_analytics_id')->nullable();
    $table->string('facebook_pixel_id')->nullable();
    $table->json('webhook_urls')->nullable();
    
    $table->timestamps();
});
```

### 💡 Features to Build

1. **Organization Onboarding Flow**
   - Create organization wizard
   - Choose subdomain (slug)
   - Select subscription plan
   - Initial setup (timezone, currency, branding)
   - Payment method collection

2. **Team Management**
   - Invite team members via email
   - Role assignment (owner, admin, member)
   - Permission management
   - Remove team members
   - View team activity

3. **Subdomain & Custom Domain**
   - Automatic subdomain: `{slug}.meetflow.app`
   - Custom domain setup with DNS verification
   - SSL certificate automation (Let's Encrypt)
   - Domain routing middleware

4. **Organization Switching**
   - Multi-org support (users can belong to multiple orgs)
   - Organization switcher in UI
   - Context-aware navigation
   - Separate data isolation per org

5. **White-Label Branding**
   - Custom logo and colors
   - Custom booking page design
   - Hide "Powered by MeetFlow" (paid plan)
   - Custom email templates
   - Custom domain branding

---

## Phase 4: Public API & Developer Tools (Week 7-8)

### 🗄️ Database Migrations Required

#### Migration 4.1: Create API Keys Table
```sql
-- create_api_keys_table.php
Schema::create('api_keys', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
    
    $table->string('name');
    $table->string('key')->unique(); // hashed
    $table->string('prefix', 10); // first few chars for display
    $table->text('scopes')->nullable(); // JSON array of permissions
    
    $table->timestamp('last_used_at')->nullable();
    $table->ipAddress('last_used_ip')->nullable();
    $table->integer('request_count')->default(0);
    $table->integer('rate_limit')->default(1000); // per hour
    
    $table->boolean('is_active')->default(true);
    $table->timestamp('expires_at')->nullable();
    
    $table->timestamps();
    
    $table->index(['organization_id', 'is_active']);
    $table->index('key');
});
```

#### Migration 4.2: Create API Request Logs Table
```sql
-- create_api_request_logs_table.php
Schema::create('api_request_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('api_key_id')->constrained()->onDelete('cascade');
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->string('method', 10); // GET, POST, etc.
    $table->string('endpoint');
    $table->integer('status_code');
    $table->integer('response_time_ms');
    $table->ipAddress('ip_address');
    $table->text('user_agent')->nullable();
    $table->json('request_payload')->nullable();
    $table->json('response_payload')->nullable();
    
    $table->timestamp('created_at');
    
    $table->index(['organization_id', 'created_at']);
    $table->index(['api_key_id', 'created_at']);
});
```

#### Migration 4.3: Create Webhooks Table
```sql
-- create_webhooks_table.php
Schema::create('webhooks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->string('name');
    $table->string('url');
    $table->string('secret')->nullable(); // for signature verification
    $table->json('events'); // ['booking.created', 'booking.cancelled', etc.]
    
    $table->boolean('is_active')->default(true);
    $table->integer('retry_attempts')->default(3);
    $table->integer('timeout_seconds')->default(30);
    
    $table->timestamp('last_triggered_at')->nullable();
    $table->integer('success_count')->default(0);
    $table->integer('failure_count')->default(0);
    
    $table->timestamps();
    
    $table->index(['organization_id', 'is_active']);
});
```

#### Migration 4.4: Create Webhook Deliveries Table
```sql
-- create_webhook_deliveries_table.php
Schema::create('webhook_deliveries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('webhook_id')->constrained()->onDelete('cascade');
    
    $table->string('event_type');
    $table->json('payload');
    $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
    $table->integer('status_code')->nullable();
    $table->text('response')->nullable();
    $table->text('error_message')->nullable();
    $table->integer('attempt_count')->default(0);
    $table->timestamp('next_retry_at')->nullable();
    
    $table->timestamps();
    
    $table->index(['webhook_id', 'status', 'created_at']);
});
```

### 💡 Features to Build

1. **RESTful API**
   - Authentication via API keys
   - Rate limiting per organization
   - API versioning (v1, v2)
   - Complete CRUD for events, bookings
   - Webhook management endpoints
   - OpenAPI/Swagger documentation

2. **API Key Management**
   - Generate/revoke API keys
   - Scope-based permissions
   - Usage analytics per key
   - Expiration dates
   - Rate limit configuration

3. **Webhook System**
   - Subscribe to events (booking.created, booking.cancelled, etc.)
   - Signature verification for security
   - Automatic retry with exponential backoff
   - Delivery logs and debugging
   - Test webhook functionality

4. **Developer Portal**
   - API documentation
   - Interactive API explorer
   - Code examples (PHP, JavaScript, Python)
   - Webhook testing tools
   - Usage metrics dashboard

---

## Phase 5: Advanced Features & Polish (Week 9-10)

### 🗄️ Database Migrations Required

#### Migration 5.1: Create Organization Analytics Table
```sql
-- create_organization_analytics_table.php
Schema::create('organization_analytics', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->date('date');
    $table->string('metric_type'); // bookings, revenue, cancellations, etc.
    $table->string('dimension')->nullable(); // event_id, payment_gateway, etc.
    $table->decimal('value', 15, 2)->default(0);
    $table->integer('count')->default(0);
    
    $table->timestamps();
    
    $table->index(['organization_id', 'date', 'metric_type']);
    $table->index(['date', 'metric_type']);
});
```

#### Migration 5.2: Create Feature Flags Table
```sql
-- create_feature_flags_table.php
Schema::create('feature_flags', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique();
    $table->string('description')->nullable();
    $table->boolean('is_enabled')->default(false);
    $table->json('enabled_for_plans')->nullable(); // ['enterprise', 'professional']
    $table->json('enabled_for_organizations')->nullable(); // specific org IDs
    $table->integer('rollout_percentage')->default(0); // gradual rollout 0-100
    $table->timestamps();
    
    $table->index('is_enabled');
});
```

#### Migration 5.3: Create Organization Integrations Table
```sql
-- create_organization_integrations_table.php
Schema::create('organization_integrations', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    
    $table->string('integration_type'); // google_calendar, zoom, stripe, etc.
    $table->json('credentials')->nullable(); // encrypted tokens
    $table->json('settings')->nullable();
    $table->boolean('is_active')->default(true);
    
    $table->timestamp('connected_at')->nullable();
    $table->timestamp('last_synced_at')->nullable();
    $table->integer('sync_error_count')->default(0);
    $table->text('last_error')->nullable();
    
    $table->timestamps();
    
    $table->unique(['organization_id', 'integration_type']);
    $table->index(['organization_id', 'is_active']);
});
```

#### Migration 5.4: Create Support Tickets Table
```sql
-- create_support_tickets_table.php
Schema::create('support_tickets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    
    $table->string('ticket_number')->unique();
    $table->string('subject');
    $table->text('description');
    $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
    $table->enum('status', ['open', 'in_progress', 'waiting', 'resolved', 'closed'])->default('open');
    $table->enum('category', ['technical', 'billing', 'feature', 'bug', 'general'])->default('general');
    
    $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
    $table->timestamp('resolved_at')->nullable();
    $table->timestamp('closed_at')->nullable();
    
    $table->timestamps();
    
    $table->index(['organization_id', 'status']);
    $table->index('ticket_number');
});
```

### 💡 Features to Build

1. **Advanced Analytics Dashboard**
   - Revenue trends and forecasting
   - Booking conversion rates
   - Cancellation analysis
   - Popular events/time slots
   - Payment gateway performance
   - Export reports (PDF, CSV)

2. **Organization Limits & Quotas**
   - Real-time usage tracking
   - Upgrade prompts when near limits
   - Overage handling (block or charge)
   - Usage notifications
   - Historical usage graphs

3. **Email Templates & Branding**
   - Customizable email templates per org
   - Template variables system
   - Preview emails before sending
   - Multi-language support
   - Email delivery tracking

4. **Integration Marketplace**
   - Pre-built integrations (Zoom, Slack, Zapier)
   - OAuth connection flows
   - Integration status monitoring
   - Disconnect/reconnect functionality
   - Integration usage analytics

5. **Customer Support System**
   - In-app support tickets
   - Live chat integration
   - Knowledge base/FAQ
   - Priority support for premium plans
   - Support analytics

---

## Phase 6: Super Admin & Platform Management (Week 11-12)

### 🗄️ Database Migrations Required

#### Migration 6.1: Add Super Admin Flag to Users
```sql
-- add_super_admin_to_users.php
Schema::table('users', function (Blueprint $table) {
    $table->boolean('is_super_admin')->default(false)->after('email_verified_at');
    $table->index('is_super_admin');
});
```

#### Migration 6.2: Create Platform Settings Table
```sql
-- create_platform_settings_table.php
Schema::create('platform_settings', function (Blueprint $table) {
    $table->id();
    $table->string('key')->unique();
    $table->text('value');
    $table->string('type')->default('string'); // string, json, boolean, integer
    $table->text('description')->nullable();
    $table->string('group')->default('general'); // general, features, limits, etc.
    $table->timestamps();
    
    $table->index(['group', 'key']);
});
```

#### Migration 6.3: Create Platform Notifications Table
```sql
-- create_platform_notifications_table.php
Schema::create('platform_notifications', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('message');
    $table->enum('type', ['info', 'warning', 'success', 'error'])->default('info');
    $table->enum('target', ['all', 'specific_plan', 'specific_org'])->default('all');
    $table->json('target_ids')->nullable(); // plan IDs or org IDs
    $table->boolean('is_dismissible')->default(true);
    $table->timestamp('starts_at')->nullable();
    $table->timestamp('ends_at')->nullable();
    $table->boolean('is_active')->default(true);
    $table->timestamps();
    
    $table->index(['is_active', 'starts_at', 'ends_at']);
});
```

### 💡 Features to Build

1. **Super Admin Dashboard**
   - Platform-wide statistics
   - Revenue and MRR tracking
   - Organization management
   - User management across all orgs
   - System health monitoring

2. **Organization Management Panel**
   - View all organizations
   - Suspend/activate organizations
   - Force plan changes
   - Impersonate organization admin
   - View org activity logs
   - Merge or delete organizations

3. **Plan Management**
   - Create/edit subscription plans
   - Set feature flags per plan
   - Pricing changes with grandfathering
   - Plan usage analytics
   - Promotional pricing

4. **System Monitoring**
   - Queue job status
   - Failed jobs dashboard
   - Database performance metrics
   - API usage and rate limits
   - Error tracking and alerts
   - System logs viewer

5. **Platform Announcements**
   - Broadcast maintenance notices
   - Feature announcements
   - Targeted messages per plan
   - Schedule announcements
   - In-app notification banner

---

## Phase 7: Security, Compliance & Optimization (Week 13-14)

### 🗄️ Database Migrations (If Needed)

#### Migration 7.1: Create Audit Logs Table
```sql
-- create_audit_logs_table.php
Schema::create('audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->nullable()->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
    
    $table->string('event'); // user.login, booking.created, etc.
    $table->string('auditable_type')->nullable();
    $table->unsignedBigInteger('auditable_id')->nullable();
    $table->json('old_values')->nullable();
    $table->json('new_values')->nullable();
    $table->ipAddress('ip_address')->nullable();
    $table->text('user_agent')->nullable();
    
    $table->timestamps();
    
    $table->index(['organization_id', 'created_at']);
    $table->index(['auditable_type', 'auditable_id']);
    $table->index('event');
});
```

#### Migration 7.2: Create Data Export Requests Table
```sql
-- create_data_export_requests_table.php
Schema::create('data_export_requests', function (Blueprint $table) {
    $table->id();
    $table->foreignId('organization_id')->constrained()->onDelete('cascade');
    $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
    
    $table->enum('type', ['full', 'bookings', 'events', 'payments'])->default('full');
    $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
    $table->string('file_path')->nullable();
    $table->timestamp('expires_at')->nullable();
    $table->text('error_message')->nullable();
    
    $table->timestamps();
    
    $table->index(['organization_id', 'status']);
});
```

### 💡 Features to Build

1. **Data Privacy & GDPR Compliance**
   - Data export functionality (download all org data)
   - Right to be forgotten (account deletion)
   - Cookie consent management
   - Privacy policy acceptance tracking
   - Data retention policies
   - Anonymization for deleted users

2. **Security Enhancements**
   - Two-factor authentication (2FA)
   - Session management (active sessions, logout all)
   - API key rotation
   - IP whitelisting for API access
   - Suspicious activity detection
   - Security audit logs

3. **Performance Optimization**
   - Implement all indexes from optimizations.md
   - Redis caching for hot data
   - CDN for static assets
   - Database query optimization
   - Lazy loading and pagination
   - Background job optimization

4. **Backup & Recovery**
   - Automated database backups
   - Point-in-time recovery
   - Organization data snapshots
   - Disaster recovery plan
   - Backup testing procedures

5. **Testing & Quality Assurance**
   - Unit tests for all critical paths
   - Integration tests for API
   - End-to-end tests for user flows
   - Load testing for scalability
   - Security penetration testing

---

## Feature Comparison Matrix

| Feature | Free/Trial | Starter | Professional | Enterprise |
|---------|-----------|---------|--------------|------------|
| **Events** | 3 | 10 | 50 | Unlimited |
| **Bookings/Month** | 20 | 100 | 500 | Unlimited |
| **Team Members** | 1 | 3 | 10 | Unlimited |
| **Subdomain** | ✅ | ✅ | ✅ | ✅ |
| **Custom Domain** | ❌ | ❌ | ✅ | ✅ |
| **White Label** | ❌ | ❌ | ✅ | ✅ |
| **API Access** | ❌ | ❌ | ✅ | ✅ |
| **Priority Support** | ❌ | ❌ | ❌ | ✅ |
| **Advanced Analytics** | ❌ | ❌ | ✅ | ✅ |
| **Custom Integrations** | ❌ | ❌ | ❌ | ✅ |
| **SLA Guarantee** | ❌ | ❌ | ❌ | ✅ |
| **Dedicated Account Manager** | ❌ | ❌ | ❌ | ✅ |

**Suggested Pricing:**
- **Starter:** $29/month or $290/year (save 17%)
- **Professional:** $79/month or $790/year (save 17%)
- **Enterprise:** $199/month or $1990/year (save 17%)

---

## Critical Middleware & Services to Build

### 1. Tenant Identification Middleware
```php
// IdentifyTenant.php
// Extract organization from subdomain/domain
// Set organization context for request
// Verify organization is active
```

### 2. Subscription Enforcement Middleware
```php
// EnsureSubscriptionActive.php
// Check if org subscription is active
// Handle trial expiration
// Block access if subscription expired
```

### 3. Usage Limit Middleware
```php
// CheckUsageLimits.php
// Verify action doesn't exceed plan limits
// Return upgrade prompt if at limit
// Log usage metrics
```

### 4. Multi-Tenancy Service
```php
// TenantService.php
// Scope all queries by organization_id
// Handle cross-tenant data isolation
// Validate data access permissions
```

### 5. Billing Service
```php
// BillingService.php
// Handle subscription creation
// Process upgrades/downgrades
// Calculate prorated charges
// Generate invoices
```

---

## Infrastructure Requirements

### Development Environment
- Docker containers for local multi-tenant testing
- Redis for caching and queues
- MinIO or S3 for file storage
- Mailgun/SendGrid for email delivery

### Production Requirements
- **Application Servers:** Multiple instances with load balancer
- **Database:** MySQL/PostgreSQL with read replicas
- **Cache:** Redis cluster
- **Queue Workers:** Supervisor-managed queue workers
- **File Storage:** AWS S3 or DigitalOcean Spaces
- **CDN:** CloudFlare or AWS CloudFront
- **Monitoring:** New Relic, Sentry, or Datadog
- **Backups:** Automated daily backups with 30-day retention

### Subdomain/Domain Routing
```nginx
# Nginx configuration for wildcard subdomains
server {
    listen 80;
    server_name *.meetflow.app meetflow.app;
    # Route to Laravel app
    # Laravel identifies tenant from request
}
```

---

## Testing Strategy

### Unit Tests
- Model relationships and scopes
- Service layer methods
- Helper functions
- Calculation logic (refunds, usage, etc.)

### Feature Tests
- Multi-tenant data isolation
- Subscription workflows
- API endpoints
- Webhook delivery
- Usage limit enforcement

### Integration Tests
- Payment gateway integration
- Google Calendar sync per organization
- Email delivery
- Webhook callbacks

### Load Tests
- Concurrent booking creation
- API rate limiting
- Database query performance
- Queue processing throughput

---

## Migration from Single to Multi-Tenant

### Data Migration Script
```php
// Migrate existing data to multi-tenant structure
// 1. Create default organization
// 2. Migrate all users to default org
// 3. Migrate all events, bookings, etc.
// 4. Set organization_id on all records
// 5. Create default subscription for existing org
```

### Backward Compatibility
- Maintain single-tenant routes initially
- Gradual migration of existing users
- Provide migration guide/wizard
- Support legacy API endpoints (deprecated)

---

## Go-Live Checklist

### Pre-Launch
- [ ] All migrations tested and documented
- [ ] Data migration scripts ready
- [ ] Backup and rollback plan
- [ ] Performance benchmarks established
- [ ] Security audit completed
- [ ] Terms of Service updated for SaaS
- [ ] Privacy Policy updated for multi-tenant
- [ ] Pricing page ready
- [ ] Billing integration tested
- [ ] Email templates customized
- [ ] Documentation completed
- [ ] Support workflows established

### Launch Day
- [ ] Database migration executed
- [ ] DNS configured for wildcard subdomains
- [ ] SSL certificates provisioned
- [ ] Payment gateway webhooks configured
- [ ] Monitoring and alerts active
- [ ] Queue workers scaled appropriately
- [ ] Landing page live
- [ ] Signup flow tested end-to-end

### Post-Launch
- [ ] Monitor error rates and performance
- [ ] Track subscription conversions
- [ ] Gather user feedback
- [ ] Iterate on onboarding flow
- [ ] Optimize based on analytics

---

## Success Metrics (KPIs)

### Business Metrics
- **MRR (Monthly Recurring Revenue):** Target $10k in 3 months
- **Churn Rate:** Keep below 5% monthly
- **Customer Acquisition Cost (CAC):** Track per channel
- **Lifetime Value (LTV):** LTV:CAC ratio > 3:1
- **Trial Conversion Rate:** Target 20-30%
- **Upgrade Rate:** % of users upgrading from starter

### Technical Metrics
- **Uptime:** 99.9% SLA
- **Response Time:** < 200ms average
- **API Success Rate:** > 99.5%
- **Queue Processing Time:** < 5 minutes for 95th percentile
- **Error Rate:** < 0.1% of requests

### User Metrics
- **Active Organizations:** Track weekly
- **Bookings per Organization:** Average and median
- **Feature Adoption:** % using custom domains, API, etc.
- **Support Ticket Volume:** Track and reduce over time

---

## Risk Mitigation

### Technical Risks
1. **Data Isolation Failures**
   - Mitigation: Comprehensive testing, code reviews, automated tests

2. **Performance Degradation**
   - Mitigation: Load testing, monitoring, auto-scaling

3. **Security Breaches**
   - Mitigation: Regular audits, penetration testing, bug bounty

### Business Risks
1. **Low Conversion Rates**
   - Mitigation: A/B testing, user feedback, onboarding optimization

2. **High Churn**
   - Mitigation: Customer success team, usage monitoring, proactive support

3. **Competition**
   - Mitigation: Unique features, better UX, competitive pricing

---

## Resources & Learning

### Multi-Tenancy Resources
- Tenancy for Laravel package: https://tenancyforlaravel.com/
- Laravel Multi-Tenancy course: https://laracasts.com

### Billing Integration
- Laravel Cashier for Stripe: https://laravel.com/docs/billing
- Razorpay PHP SDK: https://razorpay.com/docs/api/

### SaaS Best Practices
- "Obviously Awesome" by April Dunford (Positioning)
- "The SaaS Playbook" by Rob Walling
- "Traction" by Gabriel Weinberg (Customer acquisition)

---

## Estimated Total Timeline

| Phase | Duration | Key Deliverables |
|-------|----------|------------------|
| Phase 1: Multi-Tenancy | 2 weeks | Organization model, data isolation |
| Phase 2: Billing | 2 weeks | Subscriptions, payment processing |
| Phase 3: Multi-Tenant Features | 2 weeks | Domains, branding, team management |
| Phase 4: API & Webhooks | 2 weeks | RESTful API, developer portal |
| Phase 5: Advanced Features | 2 weeks | Analytics, integrations, support |
| Phase 6: Super Admin | 2 weeks | Platform management, monitoring |
| Phase 7: Security & Polish | 2 weeks | Compliance, optimization, testing |
| **Total** | **14 weeks** | **Fully-featured SaaS Platform** |

---

## Cost Estimates

### Development Costs
- **Developer Time:** 14 weeks × $50-100/hour × 40 hours/week = $28k-$56k
- **Designer:** 20 hours × $75/hour = $1,500
- **QA/Testing:** 40 hours × $40/hour = $1,600

### Infrastructure Costs (Monthly)
- **Hosting:** $50-200 (DigitalOcean/AWS)
- **Database:** $50-150
- **Redis:** $20-50
- **CDN:** $20-100
- **Email Service:** $50-200
- **Monitoring:** $50-100
- **Backups:** $20-50
- **Total:** ~$260-850/month initially

### One-Time Costs
- **SSL Certificates:** Free (Let's Encrypt)
- **Domain:** $15/year
- **Logo/Branding:** $500-2000
- **Legal (Terms/Privacy):** $500-2000

---

**Total Estimated Investment:** $30k-$60k + $260-850/month
**Break-Even Point:** ~50-100 paying customers (depending on pricing tier)

---

*This roadmap is a living document and should be updated as features are completed and priorities shift.*

**Next Steps:** Begin with Phase 1 - Multi-Tenancy Architecture. Once organization isolation is working, all other features build upon it.

---

*Created: January 23, 2026*
*Version: 1.0*
