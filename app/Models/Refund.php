<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * Refund Model - SaaS Ready
 *
 * Recommended Database Indexes:
 * - Index: (booking_id)
 * - Index: (payment_id)
 * - Index: (status, created_at)
 * - Index: (initiated_by_user_id)
 *
 * @property int $id
 * @property int $booking_id
 * @property int $payment_id
 * @property float $amount
 * @property float $gateway_charges
 * @property float $net_refund_amount
 * @property string $status
 * @property string $gateway
 * @property string|null $gateway_refund_id
 * @property string $initiated_by
 * @property int|null $initiated_by_user_id
 * @property string|null $failure_reason
 * @property array|null $gateway_response
 * @property \Carbon\Carbon|null $processed_at
 */
class Refund extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes
     */
    protected $fillable = [
        'organization_id',
        'booking_id',
        'payment_id',
        'amount',
        'gateway_charges',
        'net_refund_amount',
        'status',
        'gateway',
        'gateway_refund_id',
        'initiated_by',
        'initiated_by_user_id',
        'failure_reason',
        'gateway_response',
        'processed_at',
    ];

    /**
     * Boot the model - Add multi-tenancy scopes
     */
    protected static function booted()
    {
        // Auto-assign organization_id on creation
        static::creating(function ($refund) {
            // Skip during bootstrap
            if (!app()->isBooted()) {
                return;
            }

            if (!$refund->organization_id && app()->has('currentOrganization')) {
                $org = app('currentOrganization');
                if ($org && is_object($org) && property_exists($org, 'id')) {
                    $refund->organization_id = $org->id;
                }
            }
        });

        // Global scope to filter by organization (except for super-admin)
        static::addGlobalScope('organization', function (Builder $builder) {
            // Skip during bootstrap
            if (!app()->isBooted()) {
                return;
            }

            if (app()->has('currentOrganization') && !app()->has('bypassTenantScope')) {
                $org = app('currentOrganization');
                if ($org && is_object($org) && property_exists($org, 'id')) {
                    $builder->where('refunds.organization_id', $org->id);
                }
            }
        });
    }

    /**
     * Relationship: Refund belongs to an Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Attributes that should be cast
     */
    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_charges' => 'decimal:2',
        'net_refund_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== CONSTANTS ====================

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public const INITIATED_BY_USER = 'user';
    public const INITIATED_BY_ADMIN = 'admin';
    public const INITIATED_BY_SYSTEM = 'system';

    // ==================== RELATIONSHIPS ====================

    /**
     * The booking this refund is for
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * The payment being refunded
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * User who initiated the refund
     */
    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope: Filter pending refunds
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Filter processing refunds
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope: Filter completed refunds
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Filter failed refunds
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('initiated_by_user_id', $userId);
    }

    /**
     * Scope: Filter by booking
     */
    public function scopeForBooking(Builder $query, int $bookingId): Builder
    {
        return $query->where('booking_id', $bookingId);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ==================== HELPER METHODS ====================

    // ==================== HELPER METHODS ====================

    /**
     * Check if refund is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if refund is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === self::STATUS_PROCESSING;
    }

    /**
     * Check if refund is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if refund failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark refund as completed
     */
    public function markAsCompleted(string $gatewayRefundId = null): bool
    {
        return $this->update([
            'status' => self::STATUS_COMPLETED,
            'processed_at' => now(),
            'gateway_refund_id' => $gatewayRefundId,
        ]);
    }

    /**
     * Mark refund as failed
     */
    public function markAsFailed(string $reason): bool
    {
        return $this->update([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason,
            'processed_at' => now(),
        ]);
    }
}
