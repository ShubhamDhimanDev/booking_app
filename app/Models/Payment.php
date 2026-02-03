<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;

/**
 * Payment Model - SaaS Ready
 *
 * Recommended Database Indexes:
 * - Index: (transaction_id)
 * - Index: (booking_id)
 * - Index: (user_id, status)
 * - Index: (status, created_at)
 *
 * @property int $id
 * @property int $booking_id
 * @property int|null $user_id
 * @property string $transaction_id
 * @property int $amount Amount in smallest currency unit (paise for INR)
 * @property string $currency
 * @property string $status
 * @property string $provider Payment provider (razorpay, etc)
 * @property string|null $promo_code
 * @property array|null $metadata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Payment extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes
     */
    protected $fillable = [
        'booking_id',
        'user_id',
        'transaction_id',
        'amount',
        'currency',
        'status',
        'provider',
        'promo_code',
        'metadata',
    ];

    /**
     * Attributes that should be cast
     */
    protected $casts = [
        'amount' => 'integer',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== CONSTANTS ====================

    public const STATUS_PENDING = 'pending';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';
    public const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';

    public const PROVIDER_RAZORPAY = 'razorpay';
    public const PROVIDER_STRIPE = 'stripe';
    public const PROVIDER_PAYPAL = 'paypal';

    // ==================== RELATIONSHIPS ====================

    /**
     * The user who made this payment
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The booking this payment is for
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Refunds associated with this payment
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope: Filter successful payments
     */
    public function scopeSuccessful(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: Filter failed payments
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope: Filter pending payments
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Filter refunded payments
     */
    public function scopeRefunded(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Scope: Filter by gateway
     */
    public function scopeByGateway(Builder $query, string $gateway): Builder
    {
        return $query->where('gateway', $gateway);
    }

    /**
     * Scope: Filter by booking
     */
    public function scopeForBooking(Builder $query, int $bookingId): Builder
    {
        return $query->where('booking_id', $bookingId);
    }

    /**
     * Scope: Filter by user
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeBetweenDates(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if payment was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment failed
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if payment is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment has been refunded
     */
    public function isRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Get refundable amount
     */
    public function getRefundableAmount(): float
    {
        $refundedAmount = $this->refunds()
            ->completed()
            ->sum('amount');

        return max(0, $this->amount - $refundedAmount);
    }
}
