<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
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

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_charges' => 'decimal:2',
        'net_refund_amount' => 'decimal:2',
        'gateway_response' => 'array',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship with Booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Relationship with Payment
     */
    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Relationship with User who initiated refund
     */
    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by_user_id');
    }

    /**
     * Check if refund is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if refund is processing
     */
    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    /**
     * Check if refund is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if refund failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Mark refund as processing
     */
    public function markAsProcessing()
    {
        $this->update(['status' => 'processing']);
    }

    /**
     * Mark refund as completed
     */
    public function markAsCompleted(string $gatewayRefundId, array $gatewayResponse = [])
    {
        $this->update([
            'status' => 'completed',
            'gateway_refund_id' => $gatewayRefundId,
            'gateway_response' => $gatewayResponse,
            'processed_at' => now(),
        ]);
    }

    /**
     * Mark refund as failed
     */
    public function markAsFailed(string $reason, array $gatewayResponse = [])
    {
        $this->update([
            'status' => 'failed',
            'failure_reason' => $reason,
            'gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Scope for pending refunds
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for completed refunds
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed refunds
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }
}
