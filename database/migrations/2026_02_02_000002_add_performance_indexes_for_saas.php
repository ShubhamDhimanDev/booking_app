<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add Performance Indexes for SaaS Scalability
 * 
 * This migration adds database indexes recommended during model refactoring
 * to improve query performance for a SaaS application at scale.
 * 
 * Based on: MODEL_REFACTORING_PLAN.md
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ==================== USERS TABLE ====================
        Schema::table('users', function (Blueprint $table) {
            // Primary lookups
            $table->index('email', 'idx_users_email');
            $table->index('username', 'idx_users_username');
            
            // Email verification queries
            $table->index('email_verified_at', 'idx_users_email_verified_at');
            
            // Dashboard sorting/filtering
            $table->index('created_at', 'idx_users_created_at');
        });

        // ==================== BOOKINGS TABLE ====================
        Schema::table('bookings', function (Blueprint $table) {
            // Event bookings list - most common query
            $table->index(['event_id', 'scheduled_at'], 'idx_bookings_event_scheduled');
            
            // User bookings with status filter
            $table->index(['user_id', 'status'], 'idx_bookings_user_status');
            
            // Dashboard queries (status + date)
            $table->index(['status', 'scheduled_at'], 'idx_bookings_status_scheduled');
            
            // Confirmation lookups
            $table->unique('confirmation_token', 'idx_bookings_confirmation_token');
            
            // Guest bookings lookup
            $table->index(['email', 'status'], 'idx_bookings_email_status');
        });

        // ==================== EVENTS TABLE ====================
        Schema::table('events', function (Blueprint $table) {
            // User's events
            $table->index('user_id', 'idx_events_user_id');
            
            // Active events filter (most common)
            $table->index(['user_id', 'is_active'], 'idx_events_user_active');
            
            // Public event discovery
            $table->index(['is_active', 'is_public'], 'idx_events_active_public');
            
            // Slug lookups
            $table->index('slug', 'idx_events_slug');
            
            // Dashboard sorting
            $table->index('created_at', 'idx_events_created_at');
        });

        // ==================== PAYMENTS TABLE ====================
        Schema::table('payments', function (Blueprint $table) {
            // Transaction ID must be unique
            $table->unique('transaction_id', 'idx_payments_transaction_id');
            
            // Booking payment lookup
            $table->index('booking_id', 'idx_payments_booking_id');
            
            // User payment history
            $table->index(['user_id', 'status'], 'idx_payments_user_status');
            
            // Status-based queries (dashboard, reports)
            $table->index(['status', 'created_at'], 'idx_payments_status_created');
            
            // Gateway-specific queries
            $table->index('gateway', 'idx_payments_gateway');
        });

        // ==================== REFUNDS TABLE ====================
        Schema::table('refunds', function (Blueprint $table) {
            // Booking refund lookup
            $table->index('booking_id', 'idx_refunds_booking_id');
            
            // Payment refund lookup
            $table->index('payment_id', 'idx_refunds_payment_id');
            
            // Status-based processing queues
            $table->index(['status', 'created_at'], 'idx_refunds_status_created');
            
            // Admin/user-initiated refunds
            $table->index('initiated_by_user_id', 'idx_refunds_user_id');
        });

        // ==================== PROMO_CODES TABLE ====================
        Schema::table('promo_codes', function (Blueprint $table) {
            // Code lookup must be unique
            $table->unique('code', 'idx_promo_codes_code');
            
            // Active codes filter
            $table->index('is_active', 'idx_promo_codes_active');
            
            // Valid codes queries (checkout page)
            $table->index(['is_active', 'valid_until'], 'idx_promo_codes_active_valid');
            
            // Expiry cleanup jobs
            $table->index('valid_until', 'idx_promo_codes_expires_at');
        });

        // ==================== FOLLOW_UP_INVITES TABLE ====================
        Schema::table('follow_up_invites', function (Blueprint $table) {
            // Token lookup must be unique
            $table->unique('token', 'idx_follow_up_invites_token');
            
            // Booking invites
            $table->index('booking_id', 'idx_follow_up_invites_booking');
            
            // Event invites
            $table->index('event_id', 'idx_follow_up_invites_event');
            
            // Status-based queries
            $table->index(['status', 'created_at'], 'idx_follow_up_invites_status');
            
            // Expiry cleanup
            $table->index('expires_at', 'idx_follow_up_invites_expires');
        });

        // ==================== EVENT_REMINDERS TABLE ====================
        Schema::table('event_reminders', function (Blueprint $table) {
            // Event's reminders
            $table->index(['event_id', 'enabled'], 'idx_event_reminders_event_enabled');
            
            // Reminder processing (ordered by time)
            $table->index('minutes_before', 'idx_event_reminders_minutes');
        });

        // ==================== BOOKING_TRACKINGS TABLE ====================
        Schema::table('booking_trackings', function (Blueprint $table) {
            // Booking analytics lookup
            $table->index('booking_id', 'idx_booking_trackings_booking_id');
            
            // UTM campaign analysis
            $table->index('utm_campaign', 'idx_booking_trackings_utm_campaign');
            $table->index('utm_source', 'idx_booking_trackings_utm_source');
        });

        // ==================== EVENT_EXCLUSIONS TABLE ====================
        Schema::table('event_exclusions', function (Blueprint $table) {
            // Event exclusions lookup
            $table->index('event_id', 'idx_event_exclusions_event_id');
            
            // Date-based queries
            $table->index(['event_id', 'excluded_date'], 'idx_event_exclusions_event_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== USERS TABLE ====================
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_email');
            $table->dropIndex('idx_users_username');
            $table->dropIndex('idx_users_email_verified_at');
            $table->dropIndex('idx_users_created_at');
        });

        // ==================== BOOKINGS TABLE ====================
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex('idx_bookings_event_scheduled');
            $table->dropIndex('idx_bookings_user_status');
            $table->dropIndex('idx_bookings_status_scheduled');
            $table->dropIndex('idx_bookings_confirmation_token');
            $table->dropIndex('idx_bookings_email_status');
        });

        // ==================== EVENTS TABLE ====================
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('idx_events_user_id');
            $table->dropIndex('idx_events_user_active');
            $table->dropIndex('idx_events_active_public');
            $table->dropIndex('idx_events_slug');
            $table->dropIndex('idx_events_created_at');
        });

        // ==================== PAYMENTS TABLE ====================
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique('idx_payments_transaction_id');
            $table->dropIndex('idx_payments_booking_id');
            $table->dropIndex('idx_payments_user_status');
            $table->dropIndex('idx_payments_status_created');
            $table->dropIndex('idx_payments_gateway');
        });

        // ==================== REFUNDS TABLE ====================
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropIndex('idx_refunds_booking_id');
            $table->dropIndex('idx_refunds_payment_id');
            $table->dropIndex('idx_refunds_status_created');
            $table->dropIndex('idx_refunds_user_id');
        });

        // ==================== PROMO_CODES TABLE ====================
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropUnique('idx_promo_codes_code');
            $table->dropIndex('idx_promo_codes_active');
            $table->dropIndex('idx_promo_codes_active_valid');
            $table->dropIndex('idx_promo_codes_expires_at');
        });

        // ==================== FOLLOW_UP_INVITES TABLE ====================
        Schema::table('follow_up_invites', function (Blueprint $table) {
            $table->dropUnique('idx_follow_up_invites_token');
            $table->dropIndex('idx_follow_up_invites_booking');
            $table->dropIndex('idx_follow_up_invites_event');
            $table->dropIndex('idx_follow_up_invites_status');
            $table->dropIndex('idx_follow_up_invites_expires');
        });

        // ==================== EVENT_REMINDERS TABLE ====================
        Schema::table('event_reminders', function (Blueprint $table) {
            $table->dropIndex('idx_event_reminders_event_enabled');
            $table->dropIndex('idx_event_reminders_minutes');
        });

        // ==================== BOOKING_TRACKINGS TABLE ====================
        Schema::table('booking_trackings', function (Blueprint $table) {
            $table->dropIndex('idx_booking_trackings_booking_id');
            $table->dropIndex('idx_booking_trackings_utm_campaign');
            $table->dropIndex('idx_booking_trackings_utm_source');
        });

        // ==================== EVENT_EXCLUSIONS TABLE ====================
        Schema::table('event_exclusions', function (Blueprint $table) {
            $table->dropIndex('idx_event_exclusions_event_id');
            $table->dropIndex('idx_event_exclusions_event_date');
        });
    }
};
