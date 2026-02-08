<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

/**
 * Subscription Model
 *
 * Manages organization subscriptions and billing
 *
 * @property int $id
 * @property int $organization_id
 * @property int $subscription_plan_id
 * @property string $billing_cycle
 * @property string $status
 * @property string $gateway
 * @property string|null $gateway_subscription_id
 * @property string|null $gateway_customer_id
 * @property string|null $gateway_plan_id
 * @property array|null $gateway_metadata
 * @property Carbon|null $trial_ends_at
 * @property Carbon|null $current_period_start
 * @property Carbon|null $current_period_end
 * @property Carbon|null $cancelled_at
 * @property Carbon|null $ends_at
 * @property float $amount
 * @property string $currency
 * @property int $events_used
 * @property int $bookings_used
 * @property int $team_members_used
 * @property Carbon|null $usage_reset_at
 * @property string|null $cancellation_reason
 * @property int|null $cancelled_by_user_id
 */
class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'subscription_plan_id',
        'billing_cycle',
        'status',
        'gateway',
        'gateway_subscription_id',
        'gateway_customer_id',
        'gateway_plan_id',
        'gateway_metadata',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'ends_at',
        'amount',
        'currency',
        'events_used',
        'bookings_used',
        'team_members_used',
        'usage_reset_at',
        'cancellation_reason',
        'cancelled_by_user_id',
    ];

    protected $casts = [
        'gateway_metadata' => 'array',
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancelled_at' => 'datetime',
        'ends_at' => 'datetime',
        'usage_reset_at' => 'datetime',
        'amount' => 'decimal:2',
        'events_used' => 'integer',
        'bookings_used' => 'integer',
        'team_members_used' => 'integer',
    ];

    // ==================== CONSTANTS ====================

    public const STATUS_TRIALING = 'trialing';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_EXPIRED = 'expired';

    public const BILLING_MONTHLY = 'monthly';
    public const BILLING_YEARLY = 'yearly';

    // ==================== RELATIONSHIPS ====================

    /**
     * The organization this subscription belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The subscription plan
     */
    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'subscription_plan_id');
    }

    /**
     * User who cancelled the subscription
     */
    public function cancelledBy()
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }

    /**
     * Invoices for this subscription
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Payment attempts for this subscription
     */
    public function paymentAttempts()
    {
        return $this->hasMany(PaymentAttempt::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Active subscriptions
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: Trialing subscriptions
     */
    public function scopeTrialing($query)
    {
        return $query->where('status', self::STATUS_TRIALING);
    }

    /**
     * Scope: Cancelled subscriptions
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Scope: Past due subscriptions
     */
    public function scopePastDue($query)
    {
        return $query->where('status', self::STATUS_PAST_DUE);
    }

    /**
     * Scope: On grace period (cancelled but still active)
     */
    public function scopeOnGracePeriod($query)
    {
        return $query->where('status', self::STATUS_CANCELLED)
            ->whereNotNull('ends_at')
            ->where('ends_at', '>', now());
    }

    // ==================== STATUS CHECKS ====================

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if subscription is on trial
     */
    public function isTrialing(): bool
    {
        return $this->status === self::STATUS_TRIALING
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Check if subscription is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Check if subscription is past due
     */
    public function isPastDue(): bool
    {
        return $this->status === self::STATUS_PAST_DUE;
    }

    /**
     * Check if subscription is on grace period
     */
    public function isOnGracePeriod(): bool
    {
        return $this->isCancelled()
            && $this->ends_at
            && $this->ends_at->isFuture();
    }

    /**
     * Check if subscription has expired
     */
    public function hasExpired(): bool
    {
        return $this->status === self::STATUS_EXPIRED
            || ($this->ends_at && $this->ends_at->isPast());
    }

    /**
     * Check if trial has expired
     */
    public function hasExpiredTrial(): bool
    {
        return $this->status === self::STATUS_TRIALING
            && $this->trial_ends_at
            && $this->trial_ends_at->isPast();
    }

    // ==================== USAGE TRACKING ====================

    /**
     * Check if within events limit
     */
    public function withinEventsLimit(): bool
    {
        return $this->events_used < $this->plan->max_events;
    }

    /**
     * Check if within bookings limit
     */
    public function withinBookingsLimit(): bool
    {
        return $this->bookings_used < $this->plan->max_bookings_per_month;
    }

    /**
     * Check if within team members limit
     */
    public function withinTeamMembersLimit(): bool
    {
        return $this->team_members_used < $this->plan->max_team_members;
    }

    /**
     * Increment usage counter
     */
    public function incrementUsage(string $metric): void
    {
        switch ($metric) {
            case 'events':
                $this->increment('events_used');
                break;
            case 'bookings':
                $this->increment('bookings_used');
                break;
            case 'team_members':
                $this->increment('team_members_used');
                break;
        }
    }

    /**
     * Reset usage counters (called monthly)
     */
    public function resetUsageCounters(): void
    {
        $this->update([
            'bookings_used' => 0, // Monthly limit
            'usage_reset_at' => now()->addMonth()->startOfMonth(),
        ]);
    }

    /**
     * Get usage percentage for a metric
     */
    public function getUsagePercentage(string $metric): int
    {
        $limit = match($metric) {
            'events' => $this->plan->max_events,
            'bookings' => $this->plan->max_bookings_per_month,
            'team_members' => $this->plan->max_team_members,
            default => 0,
        };

        if ($limit === 0 || $limit >= 999999) return 0;

        $used = match($metric) {
            'events' => $this->events_used,
            'bookings' => $this->bookings_used,
            'team_members' => $this->team_members_used,
            default => 0,
        };

        return (int) min(100, ($used / $limit) * 100);
    }

    // ==================== FORMATTING ====================

    /**
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount, 2);
    }

    /**
     * Get days remaining in trial
     */
    public function getDaysRemainingInTrialAttribute(): ?int
    {
        if (!$this->isTrialing() || !$this->trial_ends_at) {
            return null;
        }

        return (int) now()->diffInDays($this->trial_ends_at, false);
    }

    /**
     * Get days remaining in current period
     */
    public function getDaysRemainingInPeriodAttribute(): ?int
    {
        if (!$this->current_period_end) {
            return null;
        }

        return (int) now()->diffInDays($this->current_period_end, false);
    }

    /**
     * Get next billing date
     */
    public function getNextBillingDateAttribute(): ?Carbon
    {
        return $this->current_period_end;
    }
}
