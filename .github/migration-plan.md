# Laravel 9 to Laravel 12 Migration Plan for Natega

## Current Version Analysis
- **Current Laravel Version**: 9.19
- **Target Laravel Version**: 12.x (latest)
- **PHP Version**: 8.0.2 (needs upgrade to 8.2+)
- **Migration Path**: Laravel 9 → 10 → 11 → 12 (incremental upgrades recommended)

---

## Pre-Migration Checklist

### 1. Backup Everything
```bash
# Database backup
php artisan db:backup  # or use your database tool
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# Code backup
git add .
git commit -m "Pre-migration backup - Laravel 9"
git tag v1.0-laravel9
git push origin --tags

# Files backup
cp -r storage/ storage_backup/
cp .env .env.backup
```

### 2. Document Current State
- [ ] List all installed packages: `composer show`
- [ ] Document custom configurations
- [ ] Note any package-specific customizations
- [ ] Test current application thoroughly
- [ ] Document all queue jobs and scheduled tasks

### 3. Set Up Testing Environment
- [ ] Create separate testing branch: `git checkout -b upgrade-laravel-12`
- [ ] Set up local testing environment (copy of production data)
- [ ] Ensure all tests are passing: `php artisan test`

---

## Migration Step 1: Laravel 9 → Laravel 10

### A. Update PHP Version
**Requirement**: PHP 8.1+

```bash
# Check current PHP version
php -v

# Update PHP (Windows - install PHP 8.2 or 8.3)
# Download from: https://windows.php.net/download/
# Update PATH environment variable
```

### B. Update composer.json Dependencies

```json
{
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0",
        "laravel/sanctum": "^3.2",
        "spatie/laravel-permission": "^5.10",
        "inertiajs/inertia-laravel": "^0.6.9"
    },
    "require-dev": {
        "laravel/breeze": "^1.19",
        "nunomaduro/collision": "^7.0",
        "phpunit/phpunit": "^10.0",
        "spatie/laravel-ignition": "^2.0"
    }
}
```

### C. Run Composer Update

```bash
composer update

# If conflicts occur, update individual packages first:
composer update laravel/framework --with-all-dependencies
```

### D. Laravel 10 Specific Changes

#### 1. Update Service Providers (if any custom ones)
- Laravel 10 uses simplified service provider registration
- Check `config/app.php` providers array

#### 2. Update Route Model Binding
In `app/Providers/RouteServiceProvider.php`:
```php
// Remove $namespace property (deprecated in L10)
// Use fully qualified class names in routes instead
```

#### 3. Update Blade Directives
- `@lang` → Use `@lang` or `__()` (no change needed)
- Check for deprecated Blade syntax

#### 4. Update Validation Rules
- String rules: Ensure `max:255` is present where needed
- Password rules: Update to new `Password::min(8)` syntax if using old style

#### 5. Update Database
```bash
php artisan migrate:status
php artisan migrate  # Run any new migrations
```

#### 6. Update Config Files
```bash
# Compare and merge new config files
php artisan vendor:publish --tag=config --force
# Review changes in: config/database.php, config/logging.php, config/sanctum.php
```

#### 7. Test Laravel 10
```bash
php artisan test
php artisan serve
# Manually test critical flows:
# - Authentication
# - Booking creation
# - Payment processing
# - Email sending (queue)
# - Google Calendar sync
```

---

## Migration Step 2: Laravel 10 → Laravel 11

### A. Update PHP Version
**Requirement**: PHP 8.2+

```bash
# Update to PHP 8.2 or 8.3
php -v
```

### B. Update composer.json Dependencies

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "laravel/sanctum": "^4.0",
        "spatie/laravel-permission": "^6.0",
        "inertiajs/inertia-laravel": "^1.0"
    },
    "require-dev": {
        "laravel/breeze": "^2.0",
        "nunomaduro/collision": "^8.0",
        "phpunit/phpunit": "^11.0",
        "spatie/laravel-ignition": "^2.4"
    }
}
```

### C. Run Composer Update

```bash
composer update --with-all-dependencies
```

### D. Laravel 11 Major Changes

#### 1. **New Application Structure** (Optional but Recommended)
Laravel 11 introduced a slimmer application structure:

**Option A: Keep existing structure** (easier, less risky)
- Your current structure will work fine

**Option B: Migrate to new structure** (cleaner, but more work)
```bash
# Bootstrap directory changes
# Middleware changes - moved to app/Http/Middleware
# Service provider consolidation in AppServiceProvider
```

#### 2. Update Middleware
Laravel 11 changes middleware registration:

In `bootstrap/app.php` (new file in L11):
```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'googleLinked' => \App\Http\Middleware\LinkedWithGoogleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
```

Move middleware aliases from `app/Http/Kernel.php` to the new bootstrap structure.

#### 3. Update Database Configuration
- Check `config/database.php` for new options
- Laravel 11 improved SQLite support

#### 4. Update Rate Limiting
If using rate limiting, check `app/Providers/RouteServiceProvider.php`:
```php
// Rate limiting moved to routes/web.php or routes/api.php
```

#### 5. Update Queue Configuration
Check `config/queue.php` for new options.

#### 6. Update Notifications
- No breaking changes for your notification files
- Verify queued notifications still work

#### 7. Environment Variables
Add new Laravel 11 variables to `.env`:
```env
APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database
BCRYPT_ROUNDS=12
```

#### 8. Update Models
- Check for deprecated model methods
- Ensure all models have proper type hints (L11 encourages strict types)

#### 9. Test Laravel 11
```bash
php artisan about  # Check system status
php artisan test
php artisan migrate:status

# Test all features:
# - User registration → email verification (queued)
# - Password reset (queued)
# - Event creation
# - Booking flow → payment → email notifications
# - Rescheduling
# - Google Calendar sync
# - Reminders (BookingReminderJob)
```

---

## Migration Step 3: Laravel 11 → Laravel 12

### A. PHP Version Check
**Requirement**: PHP 8.2+ (same as L11)

### B. Update composer.json Dependencies

```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^12.0",
        "laravel/sanctum": "^4.0",
        "spatie/laravel-permission": "^6.0",
        "inertiajs/inertia-laravel": "^2.0"
    },
    "require-dev": {
        "laravel/breeze": "^2.0",
        "nunomaduro/collision": "^8.0",
        "phpunit/phpunit": "^11.0",
        "spatie/laravel-ignition": "^2.5"
    }
}
```

### C. Run Composer Update

```bash
composer update --with-all-dependencies
```

### D. Laravel 12 Specific Changes

> **Note**: Laravel 12 details will be finalized upon release. Check official upgrade guide:
> https://laravel.com/docs/12.x/upgrade

#### Anticipated Changes (based on Laravel development patterns):

1. **Enhanced Type Safety**
   - Add return types to all controller methods
   - Add property types to all model properties
   - Enable strict types: `declare(strict_types=1);`

2. **Updated Eloquent**
   - Review any breaking changes in Eloquent query builder
   - Check model casting changes

3. **Middleware Updates**
   - Verify middleware signature compatibility
   - Check for deprecated middleware methods

4. **Testing Updates**
   - Update PHPUnit tests for version 11
   - Check for changes in Laravel's testing helpers

5. **Configuration Updates**
```bash
# Compare and update config files
php artisan vendor:publish --tag=config
```

6. **Database Updates**
   - Run migrations: `php artisan migrate`
   - Check for deprecations in migration syntax

### E. Test Laravel 12

```bash
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan test
php artisan serve

# Comprehensive manual testing
```

---

## Package-Specific Migrations

### 1. Spatie Laravel Permission (v6.x)
```bash
# Check for breaking changes
# https://github.com/spatie/laravel-permission/blob/main/CHANGELOG.md

# May need to republish config
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

### 2. Inertia.js
```bash
# Check version compatibility
# Update frontend dependencies
npm update @inertiajs/vue3  # or react
npm update
```

### 3. Laravel Socialite (Google Auth)
```bash
# Usually no breaking changes
# Verify OAuth flow still works
```

### 4. Razorpay & PayU Integration
```bash
# Check SDK compatibility with PHP 8.2+
# Test payment flows thoroughly
composer update razorpay/razorpay
```

### 5. Google API Client
```bash
# Update if needed
composer update google/apiclient
# Test Google Calendar sync
```

---

## Post-Migration Tasks

### 1. Update Frontend Dependencies
```bash
npm update
npm audit fix
npm run build
```

### 2. Clear All Caches
```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
composer dump-autoload
```

### 3. Update IDE Helper (Development)
```bash
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
php artisan ide-helper:meta
```

### 4. Performance Optimization
```bash
# Production optimizations
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
composer install --optimize-autoloader --no-dev
```

### 5. Update Documentation
- [ ] Update README.md with new Laravel version
- [ ] Update deployment documentation
- [ ] Update `.env.example` with new variables

### 6. Security Audit
```bash
composer audit
npm audit
```

---

## Testing Checklist

### Critical Features to Test

#### Authentication & Authorization
- [ ] User registration → Email verification (queued)
- [ ] Login/Logout
- [ ] Forgot password → Reset email (queued)
- [ ] Email verification resend
- [ ] Google OAuth login
- [ ] Role-based access (admin vs user)
- [ ] Middleware protection (IsAdmin, LinkedWithGoogleMiddleware)

#### Event Management
- [ ] Create event (admin only)
- [ ] Edit event
- [ ] Delete event
- [ ] Google Calendar sync
- [ ] Event exclusions
- [ ] Available time slots display

#### Booking Flow
- [ ] Public booking page access (`/e/{slug}`)
- [ ] Booking creation
- [ ] Payment processing (Razorpay)
- [ ] Payment processing (PayU)
- [ ] Booking confirmation email (queued)
- [ ] Auto-create booker user
- [ ] Send credentials email (queued)

#### Booking Management
- [ ] View bookings (user dashboard)
- [ ] View bookings (admin dashboard)
- [ ] Reschedule booking
- [ ] Reschedule email (queued)
- [ ] Cancel booking
- [ ] Decline booking (admin)
- [ ] Decline email (queued)

#### Notifications & Reminders
- [ ] Queue worker running: `php artisan queue:work`
- [ ] Booking reminders (BookingReminderJob)
- [ ] Reminder emails (queued)
- [ ] BookingReminderLog prevents duplicates

#### Payment Gateway
- [ ] Payment gateway selection (admin)
- [ ] Razorpay configuration
- [ ] PayU configuration
- [ ] Encrypted credential storage (Settings table)
- [ ] Payment verification
- [ ] Payment webhook handling

#### UI/UX
- [ ] Landing page (welcome.blade.php)
- [ ] Responsive design
- [ ] Dark theme consistency
- [ ] Admin dashboard
- [ ] User dashboard
- [ ] Shared layouts and components

### Performance Testing
- [ ] Page load times
- [ ] Database query optimization
- [ ] Queue processing speed
- [ ] Memory usage
- [ ] API response times

---

## Rollback Plan

If migration fails at any step:

### 1. Restore Code
```bash
git reset --hard HEAD
git checkout main
```

### 2. Restore Database
```bash
mysql -u username -p database_name < backup_YYYYMMDD.sql
```

### 3. Restore Environment
```bash
cp .env.backup .env
composer install
php artisan key:generate
php artisan migrate:status
```

### 4. Clear Caches
```bash
php artisan optimize:clear
composer dump-autoload
```

---

## Common Issues & Solutions

### Issue 1: Composer Dependency Conflicts
**Solution**: Update packages incrementally
```bash
composer update laravel/framework
composer update spatie/laravel-permission
composer update --no-scripts
composer install
```

### Issue 2: Middleware Not Found
**Solution**: Check middleware registration in new bootstrap/app.php structure

### Issue 3: Queue Jobs Failing
**Solution**: 
- Check queue driver configuration
- Restart queue worker: `php artisan queue:restart`
- Clear failed jobs: `php artisan queue:flush`

### Issue 4: Google OAuth Broken
**Solution**: 
- Verify redirect URI in Google Console still points to correct callback
- Check GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET in .env
- Remember: OAuth only works on 127.0.0.1 (not .test domains)

### Issue 5: Payment Gateway Errors
**Solution**:
- Verify API keys are still valid
- Check if gateway SDKs are compatible with PHP 8.2+
- Test in sandbox mode first

### Issue 6: Notification Emails Not Sending
**Solution**:
- Verify MAIL_* configuration in .env
- Check queue worker is running
- Test with `php artisan tinker`: `Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));`
- Check storage/logs/laravel.log for errors

---

## Timeline Estimate

| Phase | Duration | Description |
|-------|----------|-------------|
| Preparation | 1-2 days | Backups, documentation, testing environment |
| Laravel 9 → 10 | 2-3 days | Upgrade, test, fix issues |
| Laravel 10 → 11 | 3-5 days | Structural changes, middleware updates, testing |
| Laravel 11 → 12 | 2-3 days | Final upgrade, comprehensive testing |
| Post-migration | 1-2 days | Optimization, documentation, deployment |
| **Total** | **9-15 days** | Full migration with thorough testing |

---

## Deployment Strategy

### 1. Staging Deployment
```bash
# Deploy to staging environment first
git push staging upgrade-laravel-12

# Run migrations
php artisan migrate --force

# Test thoroughly on staging
```

### 2. Production Deployment
```bash
# Enable maintenance mode
php artisan down --secret="natega-migration-2025"

# Pull latest code
git pull origin upgrade-laravel-12

# Update dependencies
composer install --optimize-autoloader --no-dev
npm ci --production
npm run build

# Run migrations
php artisan migrate --force

# Clear and cache
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue workers
php artisan queue:restart

# Disable maintenance mode
php artisan up

# Monitor logs
tail -f storage/logs/laravel.log
```

### 3. Post-Deployment Monitoring
- [ ] Monitor error logs for 24-48 hours
- [ ] Check queue job processing
- [ ] Verify payment processing
- [ ] Monitor email delivery
- [ ] Check Google Calendar sync

---

## Resources

### Official Documentation
- [Laravel 10 Upgrade Guide](https://laravel.com/docs/10.x/upgrade)
- [Laravel 11 Upgrade Guide](https://laravel.com/docs/11.x/upgrade)
- [Laravel 12 Upgrade Guide](https://laravel.com/docs/12.x/upgrade)

### Community Resources
- [Laracasts](https://laracasts.com)
- [Laravel News](https://laravel-news.com)
- [Laravel GitHub Discussions](https://github.com/laravel/framework/discussions)

### Package Documentation
- [Spatie Permission](https://spatie.be/docs/laravel-permission)
- [Inertia.js](https://inertiajs.com)
- [Laravel Socialite](https://laravel.com/docs/socialite)

---

## Notes

1. **Do NOT skip Laravel versions** - Incremental upgrades (9→10→11→12) reduce risk
2. **Test payment flows extensively** - Critical for production
3. **Verify queue processing** - All emails now queued
4. **Google OAuth specific** - Only works on 127.0.0.1 (not .test)
5. **Database backups** - Before each migration step
6. **Git commits** - After each successful step

---

**Last Updated**: December 23, 2025
**Created By**: GitHub Copilot
**Project**: Natega Booking Platform
