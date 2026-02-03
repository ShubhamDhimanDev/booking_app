<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * EventReminder Model - SaaS Ready
 *
 * Recommended Database Indexes:
 * - Index: (event_id, enabled)
 * - Index: (offset_minutes)
 *
 * @property int $id
 * @property int $event_id
 * @property int $offset_minutes Minutes before event to send reminder
 * @property string|null $name Human-friendly label for the reminder
 * @property bool $enabled
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class EventReminder extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes
     */
    protected $fillable = [
        'event_id',
        'offset_minutes',
        'name',
        'enabled',
    ];

    /**
     * Attributes that should be cast
     */
    protected $casts = [
        'enabled' => 'boolean',
        'offset_minutes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * The event this reminder belongs to
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope: Filter enabled reminders
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('enabled', true);
    }

    /**
     * Scope: Order by time before event
     */
    public function scopeByTime(Builder $query): Builder
    {
        return $query->orderBy('offset_minutes');
    }
}
