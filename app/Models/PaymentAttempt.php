<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * PaymentAttempt Model
 *
 * Tracks all payment attempts for subscriptions
 *
 * @property int $id
 * @property int $subscription_id
 * @property int|null $invoice_id
 * @property string $gateway
 * @property string|null $gateway_payment_id
 * @property float $amount
 * @property string $currency
 * @property string $status
 * @property string|null $failure_reason
 * @property array|null $gateway_response
 * @property string|null $payment_method
 * @property string|null $card_last4
 * @property string|null $card_network
 * @property string|null $ip_address
 * @property string|null $user_agent
 */
class PaymentAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'invoice_id',
        'gateway',
        'gateway_payment_id',
        'amount',
        'currency',
        'status',
        'failure_reason',
        'gateway_response',
        'payment_method',
        'card_last4',
        'card_network',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
    ];

    // ==================== CONSTANTS ====================

    public const STATUS_PENDING = 'pending';
    public const STATUS_AUTHORIZED = 'authorized';
    public const STATUS_CAPTURED = 'captured';
    public const STATUS_FAILED = 'failed';
    public const STATUS_REFUNDED = 'refunded';

    // ==================== RELATIONSHIPS ====================

    /**
     * The subscription this payment attempt belongs to
     */
    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * The invoice this payment attempt is for
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Successful attempts
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', self::STATUS_CAPTURED);
    }

    /**
     * Scope: Failed attempts
     */
    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope: Recent attempts
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if payment was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_CAPTURED;
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
     * Get formatted amount
     */
    public function getFormattedAmountAttribute(): string
    {
        return '₹' . number_format($this->amount, 2);
    }

    /**
     * Get masked card number
     */
    public function getMaskedCardAttribute(): ?string
    {
        if (!$this->card_last4) {
            return null;
        }

        return '**** **** **** ' . $this->card_last4;
    }

    /**
     * Get payment method display name
     */
    public function getPaymentMethodDisplayAttribute(): string
    {
        return match($this->payment_method) {
            'card' => 'Credit/Debit Card',
            'netbanking' => 'Net Banking',
            'upi' => 'UPI',
            'wallet' => 'Wallet',
            default => ucfirst($this->payment_method ?? 'Unknown'),
        };
    }
}
