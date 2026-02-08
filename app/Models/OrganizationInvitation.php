<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Organization Invitation Model
 *
 * @property int $id
 * @property int $organization_id
 * @property int $invited_by
 * @property string $email
 * @property string $token
 * @property string $role
 * @property string $status
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon|null $accepted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OrganizationInvitation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organization_id',
        'invited_by',
        'email',
        'token',
        'role',
        'status',
        'expires_at',
        'accepted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== CONSTANTS ====================

    public const STATUS_PENDING = 'pending';
    public const STATUS_ACCEPTED = 'accepted';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const ROLE_ORG_ADMIN = 'org-admin';
    public const ROLE_ORG_MEMBER = 'org-member';

    // ==================== RELATIONSHIPS ====================

    /**
     * The organization this invitation belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user who sent the invitation
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if invitation is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at < now();
    }

    /**
     * Check if invitation is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING && !$this->isExpired();
    }

    /**
     * Mark invitation as accepted
     */
    public function markAsAccepted(): void
    {
        $this->update([
            'status' => self::STATUS_ACCEPTED,
            'accepted_at' => now(),
        ]);
    }

    /**
     * Mark invitation as expired
     */
    public function markAsExpired(): void
    {
        $this->update(['status' => self::STATUS_EXPIRED]);
    }

    /**
     * Cancel invitation
     */
    public function cancel(): void
    {
        $this->update(['status' => self::STATUS_CANCELLED]);
    }
}
