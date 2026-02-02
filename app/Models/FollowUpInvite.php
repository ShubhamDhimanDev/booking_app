<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * FollowUpInvite Model - SaaS Ready
 *
 * Recommended Database Indexes:
 * - UNIQUE Index: (token)
 * - Index: (booking_id)
 * - Index: (event_id)
 * - Index: (status, created_at)
 *
 * @property int $id
 * @property int $booking_id
 * @property int $event_id
 * @property string $token
 * @property string $recipient_email
 * @property string|null $recipient_name
 * @property string $status
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $sent_at
 */
class FollowUpInvite extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes
     */
    protected $fillable = [
        'booking_id',
        'event_id',
        'inviter_user_id',
        'recipient_email',
        'recipient_name',
        'token',
        'status',
        'expires_at',
        'sent_at',
        'accepted_at',
        'custom_message',
        'custom_price',
        'is_normal_invite',
    ];

    /**
     * Attributes that should be cast
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'accepted_at' => 'datetime',
        'custom_price' => 'decimal:2',
        'is_normal_invite' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== CONSTANTS ====================

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    // ==================== RELATIONSHIPS ====================

    /**
     * The booking this invite is for
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * The event being invited to
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * User who sent the invite
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_user_id');
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope: Filter pending invites
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope: Filter accepted invites
     */
    public function scopeAccepted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    /**
     * Scope: Filter expired invites
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_EXPIRED)
                     ->orWhere('expires_at', '<', now());
    }

    /**
     * Scope: Filter active (non-expired) invites
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
                     ->where('expires_at', '>', now());
    }

    // ==================== HELPER METHODS ====================

    /**
     * Generate a unique token for the invite
     */
    public static function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (self::where('token', $token)->exists());

        return $token;
    }

    /**
     * Check if the invite is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if the invite is still valid
     */
    public function isValid(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }

    /**
     * Check if the invite has been accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }
}
