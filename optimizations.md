# Database & Model Optimization Guide

This document contains step-by-step instructions for optimizing the MeetFlow database schema. Each section is written as a prompt that can be fed to an AI coding assistant to execute the optimization.

---

## Phase 1: Critical Index Optimizations (Execute First)

### Step 1.1: Add Index on bookings.status
```
Create a new migration to add an index on the `status` column in the `bookings` table. This column is frequently used in WHERE clauses to filter bookings by their status (pending, confirmed, declined, rescheduled).

Migration should:
- Add index: $table->index('status');
- In down(), drop the index properly
```

### Step 1.2: Add Index on bookings.user_id
```
Create a new migration to add an index on the `user_id` column in the `bookings` table. This foreign key column is used heavily for user booking lookups but currently has no index.

Migration should:
- Add index: $table->index('user_id');
- In down(), drop the index properly
```

### Step 1.3: Add Composite Index on bookings for Date/Time Queries
```
Create a new migration to add a composite index on the `bookings` table for the columns: event_id, booked_at_date, and status. This composite index will optimize queries that check available slots for specific events on specific dates.

Migration should:
- Add composite index: $table->index(['event_id', 'booked_at_date', 'status'], 'bookings_event_date_status_index');
- Use a custom index name to avoid MySQL's length limits
- In down(), drop the index using: $table->dropIndex('bookings_event_date_status_index');
```

### Step 1.4: Add Index on payments.status
```
Create a new migration to add an index on the `status` column in the `payments` table. This is used frequently to filter payments by status (pending, completed, failed).

Migration should:
- Add index: $table->index('status');
- In down(), drop the index properly
```

### Step 1.5: Add Index on payments.transaction_id
```
Create a new migration to add an index on the `transaction_id` column in the `payments` table. This is used by payment gateway webhooks to lookup payments by their transaction ID.

Migration should:
- Add index: $table->index('transaction_id');
- In down(), drop the index properly
```

---

## Phase 2: High Priority Schema Optimizations

### Step 2.1: Remove Redundant Refund Columns from Bookings Table
```
The bookings table currently has `refund_status` and `refund_amount` columns, but there's a separate `refunds` table that properly tracks all refund data. This creates data duplication and potential sync issues.

Create a new migration to:
1. Drop the `refund_status` column from bookings table
2. Drop the `refund_amount` column from bookings table
3. In down() method, restore these columns:
   - $table->enum('refund_status', ['not_applicable', 'pending', 'processing', 'completed', 'failed'])->default('not_applicable')->after('cancellation_reason');
   - $table->decimal('refund_amount', 10, 2)->default(0)->after('refund_status');

After creating the migration, update the Booking model:
- Remove any references to `refund_status` and `refund_amount` from the model
- Update any methods that use these fields to query the related `refund` relationship instead
- Search the codebase for usage of `$booking->refund_status` and `$booking->refund_amount` and update to use `$booking->refund->status` and `$booking->refund->amount` instead
```

### Step 2.2: Change payments.promo_code to Foreign Key
```
The payments table currently stores promo_code as a string, but it should reference the promo_codes table via foreign key for better integrity and analytics.

Create a new migration to:
1. Add a new column: $table->foreignId('promo_code_id')->nullable()->after('status')->constrained('promo_codes')->onDelete('set null');
2. Copy data from promo_code string to promo_code_id by matching against promo_codes.code
3. Drop the old promo_code column
4. In down() method, reverse the process (add promo_code string, copy data back, drop promo_code_id)

After the migration, update the Payment model:
- Add relationship: public function promoCode() { return $this->belongsTo(PromoCode::class); }
- Update any code that references $payment->promo_code to use $payment->promo_code_id or $payment->promoCode relationship
```

### Step 2.3: Add Index on refunds.status
```
Create a new migration to add an index on the `status` column in the `refunds` table. This is used to filter refunds by their processing status (pending, processing, completed, failed).

Migration should:
- Add index: $table->index('status');
- In down(), drop the index properly
```

### Step 2.4: Add Composite Index on event_reminders
```
Create a new migration to add a composite index on the `event_reminders` table for columns: event_id and enabled. This optimizes queries that find all enabled reminders for a specific event.

Migration should:
- Add composite index: $table->index(['event_id', 'enabled'], 'event_reminders_event_enabled_index');
- In down(), drop the index: $table->dropIndex('event_reminders_event_enabled_index');
```

---

## Phase 3: Moderate Priority Optimizations

### Step 3.1: Add Index on promo_codes.is_active
```
Create a new migration to add an index on the `is_active` column in the `promo_codes` table. The PromoCode model has an active() scope that filters by this column.

Migration should:
- Add index: $table->index('is_active');
- In down(), drop the index properly
```

### Step 3.2: Add Composite Index on promo_codes for Validity Queries
```
Create a new migration to add a composite index on the `promo_codes` table for columns: is_active, valid_from, and valid_until. This optimizes queries that check if promo codes are currently valid.

Migration should:
- Add composite index: $table->index(['is_active', 'valid_from', 'valid_until'], 'promo_codes_validity_index');
- In down(), drop the index: $table->dropIndex('promo_codes_validity_index');
```

### Step 3.3: Rename payments.provider to payments.gateway
```
For naming consistency across the codebase (refunds table uses "gateway", services use "gateway"), rename the `provider` column in payments table to `gateway`.

Create a new migration to:
1. Rename column: $table->renameColumn('provider', 'gateway');
2. In down(), rename it back: $table->renameColumn('gateway', 'provider');

After the migration, update the Payment model and all related code:
- Update any references from `provider` to `gateway`
- Search for `$payment->provider` in controllers and services
- Update any form inputs, validation rules that reference "provider"
```

### Step 3.4: Remove Unused Time Columns from Events Table
```
The events table has `available_from_time` and `available_to_time` columns that were supposed to be dropped by migration 2025_12_09_150000_drop_available_time_columns but may still exist or have references in code.

First, check if these columns still exist in the events table. If they do:
1. Verify the drop_available_time_columns migration exists and was run
2. If migration exists but columns remain, investigate why
3. Clean up the Event model code that references these columns (lines 68-89 in Event.php have fallback logic)

If columns are already dropped:
- Just remove the fallback code from Event.php that references $this->available_from_time and $this->available_to_time
```

### Step 3.5: Review and Optimize booking_reminder_logs Indexes
```
The booking_reminder_logs table has both a composite unique constraint on ['booking_id', 'reminder_key'] and a separate index on 'reminder_key'. 

Review if the separate reminder_key index is necessary:
- The composite unique constraint already creates an index that can be used for reminder_key lookups (as the second column)
- If most queries filter by booking_id first, the composite index is sufficient
- If there are queries that filter ONLY by reminder_key (without booking_id), keep the separate index

If the separate index is redundant, create a migration to drop it:
- $table->dropIndex(['reminder_key']);
```

---

## Phase 4: Low Priority Optimizations (Nice to Have)

### Step 4.1: Optimize events.duration Data Type
```
Create a new migration to change the `duration` column in the `events` table from integer (4 bytes) to smallInteger (2 bytes). Event durations rarely exceed 32,767 minutes (22 days).

Migration should:
- Change column: $table->smallInteger('duration')->change();
- Require doctrine/dbal package for column changes
- In down(), change back to integer: $table->integer('duration')->change();
- Note: Run composer require doctrine/dbal first if not already installed
```

### Step 4.2: Optimize event_reminders.offset_minutes Data Type
```
Create a new migration to change the `offset_minutes` column in the `event_reminders` table from integer unsigned (4 bytes) to smallInteger unsigned (2 bytes). Reminder offsets rarely exceed 65,535 minutes (45 days).

Migration should:
- Change column: $table->smallInteger('offset_minutes')->unsigned()->change();
- Require doctrine/dbal package
- In down(), change back: $table->integer('offset_minutes')->unsigned()->change();
```

### Step 4.3: Consider Soft Deletes on Events Table
```
The events table currently uses hard deletes which cascade to bookings. For better audit trails and data recovery, consider adding soft deletes.

Create a new migration to:
1. Add soft deletes: $table->softDeletes();
2. In down(), drop: $table->dropSoftDeletes();

After the migration:
- Add SoftDeletes trait to Event model
- Update event deletion logic in controllers to check if soft delete is appropriate
- Consider adding a "restore" feature in admin panel
- Review cascade delete constraints on related tables (bookings, event_reminders, event_exclusions)
- Note: This requires careful consideration of business logic around deleted events with existing bookings
```

### Step 4.4: Consider Merging Settings Tables
```
Currently, there are two separate tables: `settings` (app-wide config) and `system_settings` (user preferences). Consider whether to merge them or rename for clarity.

Option A - Merge Tables:
Create a migration to:
1. Add user_id to settings table (nullable)
2. Migrate data from system_settings to settings
3. Drop system_settings table
4. Add composite unique key: unique(['user_id', 'key'])

Option B - Rename for Clarity (Recommended):
Create a migration to:
1. Rename system_settings to user_preferences
2. Update SystemSetting model name to UserPreference
3. Update all references in code

Option B is safer and maintains clear separation of concerns.
```

---

## Verification Steps After Each Phase

After completing each phase, run these verification steps:

### Step V.1: Verify Migrations
```
Run the following commands to verify migrations executed successfully:
1. php artisan migrate:status - Check all migrations are executed
2. Check database for new indexes using:
   SHOW INDEXES FROM bookings;
   SHOW INDEXES FROM payments;
   SHOW INDEXES FROM refunds;
   SHOW INDEXES FROM promo_codes;
   SHOW INDEXES FROM event_reminders;
```

### Step V.2: Verify No Breaking Changes
```
Run these commands to ensure no breaking changes:
1. php artisan test - Run all tests
2. Check for errors in logs: tail -f storage/logs/laravel.log
3. Test key user flows:
   - Creating a booking
   - Processing a payment
   - Applying a promo code
   - Cancelling a booking with refund
   - Sending reminders
```

### Step V.3: Verify Query Performance
```
Enable query logging temporarily and verify that queries are using the new indexes:
- Add DB::enableQueryLog() in a test route
- Execute key queries (list bookings, check payment status, etc.)
- Check DB::getQueryLog() to see EXPLAIN results
- Look for "Using index" in EXPLAIN output
```

---

## Notes

- Always backup database before running migrations in production
- Test each migration in development/staging environment first
- Monitor query performance before and after optimizations
- Consider running migrations during low-traffic periods
- Keep track of which steps have been completed
- Each step can be executed independently unless noted otherwise
- Some steps (like Step 2.1 and 2.2) require code updates in addition to migrations

---

## Execution Checklist

Mark completed steps:

### Phase 1 (Critical)
- [ ] Step 1.1: Index on bookings.status
- [ ] Step 1.2: Index on bookings.user_id
- [ ] Step 1.3: Composite index on bookings
- [ ] Step 1.4: Index on payments.status
- [ ] Step 1.5: Index on payments.transaction_id

### Phase 2 (High Priority)
- [ ] Step 2.1: Remove refund columns from bookings
- [ ] Step 2.2: Change promo_code to foreign key
- [ ] Step 2.3: Index on refunds.status
- [ ] Step 2.4: Composite index on event_reminders

### Phase 3 (Moderate Priority)
- [ ] Step 3.1: Index on promo_codes.is_active
- [ ] Step 3.2: Composite index on promo_codes
- [ ] Step 3.3: Rename payments.provider to gateway
- [ ] Step 3.4: Remove unused time columns
- [ ] Step 3.5: Review booking_reminder_logs indexes

### Phase 4 (Low Priority)
- [ ] Step 4.1: Optimize events.duration data type
- [ ] Step 4.2: Optimize event_reminders.offset_minutes
- [ ] Step 4.3: Soft deletes on events
- [ ] Step 4.4: Merge/rename settings tables

### Verification
- [ ] V.1: Verify migrations
- [ ] V.2: Verify no breaking changes
- [ ] V.3: Verify query performance

---

## Estimated Impact

**Phase 1:** 60-80% performance improvement on booking/payment queries
**Phase 2:** Additional 15-20% improvement + better data integrity
**Phase 3:** 5-10% improvement + code consistency
**Phase 4:** Minimal performance impact, mostly maintenance benefits

---

*Last Updated: January 22, 2026*
*Total Steps: 19 optimization steps + 3 verification steps*
