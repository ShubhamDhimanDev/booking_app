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
 * @property int $user_id
 * @property float $custom_price
 * @property bool $is_normal_invite
 * @property int $event_id
 * @property string $token
 * @property string $status
 * @property \Carbon\Carbon|null $expires_at
 * @property \Carbon\Carbon|null $sent_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class FollowUpInvite extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes
     */
    protected $fillable = [
        'organization_id',
        'booking_id',
        'event_id',
        'user_id',
        'token',
        'status',
        'expires_at',
        'sent_at',
        'custom_price',
        'is_normal_invite',
    ];

    /**
     * Boot the model - Add multi-tenancy scopes
     */
    protected static function booted()
    {
        // Auto-assign organization_id on creation
        static::creating(function ($invite) {
            // Skip during bootstrap
            if (!app()->isBooted()) {
                return;
            }

            if (!$invite->organization_id && app()->has('currentOrganization')) {
                $org = app('currentOrganization');
                if ($org && is_object($org) && property_exists($org, 'id')) {
                    $invite->organization_id = $org->id;
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
                    $builder->where('follow_up_invites.organization_id', $org->id);
                }
            }
        });
    }

    /**
     * Relationship: FollowUpInvite belongs to an Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Attributes that should be cast
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'sent_at' => 'datetime',
        'custom_price' => 'decimal:2',
        'is_normal_invite' => 'boolean',
    ];

    // ==================== CONSTANTS ====================

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_EXPIRED = 'expired';

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
        return $this->belongsTo(User::class, 'user_id');
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
