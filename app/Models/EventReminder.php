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
 * - Index: (minutes_before)
 *
 * @property int $id
 * @property int $event_id
 * @property int $minutes_before
 * @property bool $enabled
 * @property string|null $custom_message
 */
class EventReminder extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes
     */
    protected $fillable = [
        'event_id',
        'minutes_before',
        'enabled',
        'reminder_type',
        'custom_message',
    ];

    /**
     * Attributes that should be cast
     */
    protected $casts = [
        'enabled' => 'boolean',
        'minutes_before' => 'integer',
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
        return $query->orderBy('minutes_before');
    }
}
