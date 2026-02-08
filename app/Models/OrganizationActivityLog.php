<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Organization Activity Log Model
 *
 * @property int $id
 * @property int $organization_id
 * @property int|null $user_id
 * @property string $action
 * @property string|null $entity_type
 * @property int|null $entity_id
 * @property array|null $metadata
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class OrganizationActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'organization_id',
        'user_id',
        'action',
        'entity_type',
        'entity_id',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * The organization this log belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * The user who performed the action
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ==================== HELPER METHODS ====================

    /**
     * Log an activity
     */
    public static function logActivity(
        int $organizationId,
        string $action,
        ?int $userId = null,
        ?string $entityType = null,
        ?int $entityId = null,
        ?array $metadata = null
    ): self {
        return self::create([
            'organization_id' => $organizationId,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
