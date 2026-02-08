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
            // Primary lookups - check if not already exists
            if (!$this->indexExists('users', 'idx_users_email') && !$this->indexExists('users', 'users_email_unique')) {
                $table->index('email', 'idx_users_email');
            }
            if (!$this->indexExists('users', 'idx_users_username')) {
                $table->index('username', 'idx_users_username');
            }

            // Email verification queries
            if (!$this->indexExists('users', 'idx_users_email_verified_at')) {
                $table->index('email_verified_at', 'idx_users_email_verified_at');
            }

            // Dashboard sorting/filtering
            if (!$this->indexExists('users', 'idx_users_created_at')) {
                $table->index('created_at', 'idx_users_created_at');
            }
        });

        // ==================== BOOKINGS TABLE ====================
        Schema::table('bookings', function (Blueprint $table) {
            $hasConfirmationToken = Schema::hasColumn('bookings', 'confirmation_token');

            // Event bookings list - most common query (using actual column name)
            if (!$this->indexExists('bookings', 'idx_bookings_event_scheduled')) {
                $table->index(['event_id', 'booked_at_date'], 'idx_bookings_event_scheduled');
            }

            // User bookings with status filter
            if (!$this->indexExists('bookings', 'idx_bookings_user_status')) {
                $table->index(['user_id', 'status'], 'idx_bookings_user_status');
            }

            // Dashboard queries (status + date)
            if (!$this->indexExists('bookings', 'idx_bookings_status_scheduled')) {
                $table->index(['status', 'booked_at_date'], 'idx_bookings_status_scheduled');
            }

            // Confirmation lookups - only if column exists
            if ($hasConfirmationToken && !$this->indexExists('bookings', 'idx_bookings_confirmation_token')) {
                $table->index('confirmation_token', 'idx_bookings_confirmation_token');
            }

            // Guest bookings lookup (using actual column name)
            if (!$this->indexExists('bookings', 'idx_bookings_email_status')) {
                $table->index(['booker_email', 'status'], 'idx_bookings_email_status');
            }
        });

        // ==================== EVENTS TABLE ====================
        Schema::table('events', function (Blueprint $table) {
            // User's events
            if (!$this->indexExists('events', 'idx_events_user_id')) {
                $table->index('user_id', 'idx_events_user_id');
            }

            // Active events filter (most common) - only if is_active column exists
            // This requires checking if the column exists
            $conn = Schema::getConnection();
            $hasIsActive = Schema::hasColumn('events', 'is_active');
            $hasIsPublic = Schema::hasColumn('events', 'is_public');

            if ($hasIsActive && !$this->indexExists('events', 'idx_events_user_active')) {
                $table->index(['user_id', 'is_active'], 'idx_events_user_active');
            }

            // Public event discovery
            if ($hasIsActive && $hasIsPublic && !$this->indexExists('events', 'idx_events_active_public')) {
                $table->index(['is_active', 'is_public'], 'idx_events_active_public');
            }

            // Slug lookups
            if (!$this->indexExists('events', 'idx_events_slug') && !$this->indexExists('events', 'events_slug_unique')) {
                $table->index('slug', 'idx_events_slug');
            }

            // Dashboard sorting
            if (!$this->indexExists('events', 'idx_events_created_at')) {
                $table->index('created_at', 'idx_events_created_at');
            }
        });

        // ==================== PAYMENTS TABLE ====================
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                // Transaction ID - use regular index instead of unique since there may be duplicates
                if (Schema::hasColumn('payments', 'transaction_id') && !$this->indexExists('payments', 'idx_payments_transaction_id')) {
                    $table->index('transaction_id', 'idx_payments_transaction_id');
                }

                // Booking payment lookup
                if (!$this->indexExists('payments', 'idx_payments_booking_id')) {
                    $table->index('booking_id', 'idx_payments_booking_id');
                }

                // User payment history
                if (Schema::hasColumn('payments', 'user_id') && Schema::hasColumn('payments', 'status') && !$this->indexExists('payments', 'idx_payments_user_status')) {
                    $table->index(['user_id', 'status'], 'idx_payments_user_status');
                }

                // Status-based queries (dashboard, reports)
                if (Schema::hasColumn('payments', 'status') && !$this->indexExists('payments', 'idx_payments_status_created')) {
                    $table->index(['status', 'created_at'], 'idx_payments_status_created');
                }

                // Gateway-specific queries
                if (Schema::hasColumn('payments', 'gateway') && !$this->indexExists('payments', 'idx_payments_gateway')) {
                    $table->index('gateway', 'idx_payments_gateway');
                }
            });
        }

        // ==================== REFUNDS TABLE ====================
        if (Schema::hasTable('refunds')) {
            Schema::table('refunds', function (Blueprint $table) {
                // Booking refund lookup
                if (!$this->indexExists('refunds', 'idx_refunds_booking_id')) {
                    $table->index('booking_id', 'idx_refunds_booking_id');
                }

                // Payment refund lookup
                if (!$this->indexExists('refunds', 'idx_refunds_payment_id')) {
                    $table->index('payment_id', 'idx_refunds_payment_id');
                }

                // Status-based processing queues
                if (!$this->indexExists('refunds', 'idx_refunds_status_created')) {
                    $table->index(['status', 'created_at'], 'idx_refunds_status_created');
                }

                // Admin/user-initiated refunds
                if (Schema::hasColumn('refunds', 'initiated_by_user_id') && !$this->indexExists('refunds', 'idx_refunds_user_id')) {
                    $table->index('initiated_by_user_id', 'idx_refunds_user_id');
                }
            });
        }

        // ==================== PROMO_CODES TABLE ====================
        if (Schema::hasTable('promo_codes')) {
            Schema::table('promo_codes', function (Blueprint $table) {
                // Code lookup must be unique
                if (!$this->indexExists('promo_codes', 'idx_promo_codes_code') && !$this->indexExists('promo_codes', 'promo_codes_code_unique')) {
                    $table->unique('code', 'idx_promo_codes_code');
                }

                // Active codes filter
                if (Schema::hasColumn('promo_codes', 'is_active') && !$this->indexExists('promo_codes', 'idx_promo_codes_active')) {
                    $table->index('is_active', 'idx_promo_codes_active');
                }

                // Valid codes queries (checkout page)
                if (Schema::hasColumn('promo_codes', 'is_active') && Schema::hasColumn('promo_codes', 'valid_until') && !$this->indexExists('promo_codes', 'idx_promo_codes_active_valid')) {
                    $table->index(['is_active', 'valid_until'], 'idx_promo_codes_active_valid');
                }

                // Expiry cleanup jobs
                if (Schema::hasColumn('promo_codes', 'valid_until') && !$this->indexExists('promo_codes', 'idx_promo_codes_expires_at')) {
                    $table->index('valid_until', 'idx_promo_codes_expires_at');
                }
            });
        }

        // ==================== FOLLOW_UP_INVITES TABLE ====================
        if (Schema::hasTable('follow_up_invites')) {
            Schema::table('follow_up_invites', function (Blueprint $table) {
                // Token lookup must be unique
                if (!$this->indexExists('follow_up_invites', 'idx_follow_up_invites_token') && !$this->indexExists('follow_up_invites', 'follow_up_invites_token_unique')) {
                    $table->unique('token', 'idx_follow_up_invites_token');
                }

                // Booking invites
                if (!$this->indexExists('follow_up_invites', 'idx_follow_up_invites_booking')) {
                    $table->index('booking_id', 'idx_follow_up_invites_booking');
                }

                // Event invites
                if (!$this->indexExists('follow_up_invites', 'idx_follow_up_invites_event')) {
                    $table->index('event_id', 'idx_follow_up_invites_event');
                }

                // Status-based queries
                if (Schema::hasColumn('follow_up_invites', 'status') && !$this->indexExists('follow_up_invites', 'idx_follow_up_invites_status')) {
                    $table->index(['status', 'created_at'], 'idx_follow_up_invites_status');
                }

                // Expiry cleanup
                if (Schema::hasColumn('follow_up_invites', 'expires_at') && !$this->indexExists('follow_up_invites', 'idx_follow_up_invites_expires')) {
                    $table->index('expires_at', 'idx_follow_up_invites_expires');
                }
            });
        }

        // ==================== EVENT_REMINDERS TABLE ====================
        if (Schema::hasTable('event_reminders')) {
            Schema::table('event_reminders', function (Blueprint $table) {
                // Event's reminders
                if (Schema::hasColumn('event_reminders', 'enabled') && !$this->indexExists('event_reminders', 'idx_event_reminders_event_enabled')) {
                    $table->index(['event_id', 'enabled'], 'idx_event_reminders_event_enabled');
                }

                // Reminder processing (ordered by time)
                if (Schema::hasColumn('event_reminders', 'minutes_before') && !$this->indexExists('event_reminders', 'idx_event_reminders_minutes')) {
                    $table->index('minutes_before', 'idx_event_reminders_minutes');
                }
            });
        }

        // ==================== BOOKING_TRACKINGS TABLE ====================
        if (Schema::hasTable('booking_trackings')) {
            Schema::table('booking_trackings', function (Blueprint $table) {
                // Booking analytics lookup
                if (!$this->indexExists('booking_trackings', 'idx_booking_trackings_booking_id')) {
                    $table->index('booking_id', 'idx_booking_trackings_booking_id');
                }

                // UTM campaign analysis
                if (Schema::hasColumn('booking_trackings', 'utm_campaign') && !$this->indexExists('booking_trackings', 'idx_booking_trackings_utm_campaign')) {
                    $table->index('utm_campaign', 'idx_booking_trackings_utm_campaign');
                }
                if (Schema::hasColumn('booking_trackings', 'utm_source') && !$this->indexExists('booking_trackings', 'idx_booking_trackings_utm_source')) {
                    $table->index('utm_source', 'idx_booking_trackings_utm_source');
                }
            });
        }

        // ==================== EVENT_EXCLUSIONS TABLE ====================
        if (Schema::hasTable('event_exclusions')) {
            Schema::table('event_exclusions', function (Blueprint $table) {
                // Event exclusions lookup
                if (!$this->indexExists('event_exclusions', 'idx_event_exclusions_event_id')) {
                    $table->index('event_id', 'idx_event_exclusions_event_id');
                }

                // Date-based queries
                if (Schema::hasColumn('event_exclusions', 'excluded_date') && !$this->indexExists('event_exclusions', 'idx_event_exclusions_event_date')) {
                    $table->index(['event_id', 'excluded_date'], 'idx_event_exclusions_event_date');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ==================== USERS TABLE ====================
        Schema::table('users', function (Blueprint $table) {
            if ($this->indexExists('users', 'idx_users_email')) {
                $table->dropIndex('idx_users_email');
            }
            if ($this->indexExists('users', 'idx_users_username')) {
                $table->dropIndex('idx_users_username');
            }
            if ($this->indexExists('users', 'idx_users_email_verified_at')) {
                $table->dropIndex('idx_users_email_verified_at');
            }
            if ($this->indexExists('users', 'idx_users_created_at')) {
                $table->dropIndex('idx_users_created_at');
            }
        });

        // ==================== BOOKINGS TABLE ====================
        if (Schema::hasTable('bookings')) {
            try {
                Schema::table('bookings', function (Blueprint $table) {
                    // idx_bookings_event_scheduled may be used by foreign key, skip it
                    // idx_bookings_user_status may be used by foreign key on user_id, skip it

                    if ($this->indexExists('bookings', 'idx_bookings_status_scheduled')) {
                        $table->dropIndex('idx_bookings_status_scheduled');
                    }

                    if ($this->indexExists('bookings', 'idx_bookings_confirmation_token')) {
                        $table->dropIndex('idx_bookings_confirmation_token');
                    }

                    if ($this->indexExists('bookings', 'idx_bookings_email_status')) {
                        $table->dropIndex('idx_bookings_email_status');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore errors when dropping indexes that are used by foreign keys
            }
        }

        // ==================== EVENTS TABLE ====================
        if (Schema::hasTable('events')) {
            try {
                Schema::table('events', function (Blueprint $table) {
                    // idx_events_user_id may be used by foreign key on user_id, skip it

                    if ($this->indexExists('events', 'idx_events_user_active')) {
                        $table->dropIndex('idx_events_user_active');
                    }

                    if ($this->indexExists('events', 'idx_events_active_public')) {
                        $table->dropIndex('idx_events_active_public');
                    }

                    if ($this->indexExists('events', 'idx_events_slug')) {
                        $table->dropIndex('idx_events_slug');
                    }

                    if ($this->indexExists('events', 'idx_events_created_at')) {
                        $table->dropIndex('idx_events_created_at');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore errors when dropping indexes that are used by foreign keys
            }
        }

        // ==================== PAYMENTS TABLE ====================
        if (Schema::hasTable('payments')) {
            try {
                Schema::table('payments', function (Blueprint $table) {
                    // idx_payments_booking_id may be used by foreign key, skip it

                    if ($this->indexExists('payments', 'idx_payments_transaction_id')) {
                        $table->dropIndex('idx_payments_transaction_id');
                    }

                    if ($this->indexExists('payments', 'idx_payments_user_status')) {
                        $table->dropIndex('idx_payments_user_status');
                    }

                    if ($this->indexExists('payments', 'idx_payments_status_created')) {
                        $table->dropIndex('idx_payments_status_created');
                    }

                    if ($this->indexExists('payments', 'idx_payments_gateway')) {
                        $table->dropIndex('idx_payments_gateway');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore errors when dropping indexes that are used by foreign keys
            }
        }

        // ==================== REFUNDS TABLE ====================
        if (Schema::hasTable('refunds')) {
            try {
                Schema::table('refunds', function (Blueprint $table) {
                    // idx_refunds_booking_id and idx_refunds_payment_id may be used by foreign keys, skip them

                    if ($this->indexExists('refunds', 'idx_refunds_status_created')) {
                        $table->dropIndex('idx_refunds_status_created');
                    }

                    if ($this->indexExists('refunds', 'idx_refunds_user_id')) {
                        $table->dropIndex('idx_refunds_user_id');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore errors when dropping indexes that are used by foreign keys
            }
        }

        // ==================== PROMO_CODES TABLE ====================
        if (Schema::hasTable('promo_codes')) {
            try {
                Schema::table('promo_codes', function (Blueprint $table) {
                    if ($this->indexExists('promo_codes', 'idx_promo_codes_code')) {
                        $table->dropUnique('idx_promo_codes_code');
                    }

                    if ($this->indexExists('promo_codes', 'idx_promo_codes_active')) {
                        $table->dropIndex('idx_promo_codes_active');
                    }

                    if ($this->indexExists('promo_codes', 'idx_promo_codes_active_valid')) {
                        $table->dropIndex('idx_promo_codes_active_valid');
                    }

                    if ($this->indexExists('promo_codes', 'idx_promo_codes_expires_at')) {
                        $table->dropIndex('idx_promo_codes_expires_at');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore errors when dropping indexes that are used by foreign keys
            }
        }

        // ==================== FOLLOW_UP_INVITES TABLE ====================
        if (Schema::hasTable('follow_up_invites')) {
            try {
                Schema::table('follow_up_invites', function (Blueprint $table) {
                    // idx_follow_up_invites_booking and idx_follow_up_invites_event may be used by foreign keys, skip them

                    if ($this->indexExists('follow_up_invites', 'idx_follow_up_invites_token')) {
                        $table->dropUnique('idx_follow_up_invites_token');
                    }

                    if ($this->indexExists('follow_up_invites', 'idx_follow_up_invites_status')) {
                        $table->dropIndex('idx_follow_up_invites_status');
                    }

                    if ($this->indexExists('follow_up_invites', 'idx_follow_up_invites_expires')) {
                        $table->dropIndex('idx_follow_up_invites_expires');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore errors when dropping indexes that are used by foreign keys
            }
        }

        // ==================== EVENT_REMINDERS TABLE ====================
        if (Schema::hasTable('event_reminders')) {
            try {
                Schema::table('event_reminders', function (Blueprint $table) {
                    // idx_event_reminders_event may be used by foreign key, skip it

                    if ($this->indexExists('event_reminders', 'idx_event_reminders_active_time')) {
                        $table->dropIndex('idx_event_reminders_active_time');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore errors when dropping indexes that are used by foreign keys
            }
        }

        // ==================== BOOKING_TRACKINGS TABLE ====================
        if (Schema::hasTable('booking_trackings')) {
            try {
                Schema::table('booking_trackings', function (Blueprint $table) {
                    // idx_booking_trackings_booking may be used by foreign key, skip it

                    if ($this->indexExists('booking_trackings', 'idx_booking_trackings_session')) {
                        $table->dropIndex('idx_booking_trackings_session');
                    }

                    if ($this->indexExists('booking_trackings', 'idx_booking_trackings_referrer')) {
                        $table->dropIndex('idx_booking_trackings_referrer');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore errors when dropping indexes that are used by foreign keys
            }
        }

        // ==================== EVENT_EXCLUSIONS TABLE ====================
        if (Schema::hasTable('event_exclusions')) {
            try {
                Schema::table('event_exclusions', function (Blueprint $table) {
                    // idx_event_exclusions_event_id may be used by foreign key, skip it

                    if ($this->indexExists('event_exclusions', 'idx_event_exclusions_event_date')) {
                        $table->dropIndex('idx_event_exclusions_event_date');
                    }
                });
            } catch (\Throwable $e) {
                // Ignore errors when dropping indexes that are used by foreign keys
            }
        }
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExists(string $table, string $index): bool
    {
        $conn = Schema::getConnection();
        $dbName = $conn->getDatabaseName();

        $result = $conn->select(
            "SELECT COUNT(*) as count FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$dbName, $table, $index]
        );

        return $result[0]->count > 0;
    }
};
