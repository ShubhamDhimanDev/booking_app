<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Payment;

/**
 * Booking Model - SaaS Ready
 *
 * Recommended Database Indexes:
 * - Index: (event_id, booked_at_date) - for event bookings list
 * - Index: (user_id, status) - for user bookings with status filter
 * - Index: (status, booked_at_date) - for dashboard queries
 * - Index: (booker_email, status) - for guest bookings lookup
 *
 * @property int $id
 * @property int $event_id
 * @property int|null $user_id
 * @property bool $is_followup
 * @property int|null $followup_invite_id
 * @property string $booker_email
 * @property string $booker_name
 * @property string|null $phone
 * @property string $status
 * @property \Carbon\Carbon $booked_at_date
 * @property string $booked_at_time
 * @property string|null $calendar_id
 * @property string|null $calendar_link
 * @property string|null $meet_link
 * @property \Carbon\Carbon|null $cancelled_at
 * @property int|null $cancelled_by
 * @property string|null $cancellation_reason
 * @property string $refund_status
 * @property float $refund_amount
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * Virtual/Accessor Properties (for backward compatibility):
 * @property string $email Alias for booker_email
 * @property string $name Alias for booker_name
 * @property \Carbon\Carbon $scheduled_at Combines booked_at_date and booked_at_time
 */
class Booking extends Model
{
  use HasFactory, SoftDeletes;

  /**
   * Mass-assignable attributes
   */
  protected $fillable = [
    'organization_id',
    'event_id',
    'user_id',
    'is_followup',
    'followup_invite_id',
    'booker_email',
    'booker_name',
    'phone',
    'status',
    'booked_at_date',
    'booked_at_time',
    'calendar_id',
    'calendar_link',
    'meet_link',
    'cancelled_at',
    'cancelled_by',
    'cancellation_reason',
    'refund_status',
    'refund_amount',
  ];

  /**
   * Boot the model - Add multi-tenancy scopes
   */
  protected static function booted()
  {
      // Auto-assign organization_id on creation
      static::creating(function ($booking) {
          // Skip during bootstrap
          if (!app()->isBooted()) {
              return;
          }

          if (!$booking->organization_id && app()->has('currentOrganization')) {
              $org = app('currentOrganization');
              if ($org && is_object($org) && property_exists($org, 'id')) {
                  $booking->organization_id = $org->id;
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
                  $builder->where('bookings.organization_id', $org->id);
              }
          }
      });
  }

  /**
   * Relationship: Booking belongs to an Organization
   */
  public function organization()
  {
      return $this->belongsTo(Organization::class);
  }

  /**
   * Attributes that should be cast
   */
  protected $casts = [
    'booked_at_date' => 'date',
    'is_followup' => 'boolean',
    'cancelled_at' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'refund_amount' => 'decimal:2',
  ];

  /**
   * Attributes to append to model's array/JSON form
   */
  protected $appends = [];

  // ==================== CONSTANTS ====================

  public const STATUS_PENDING = 'pending';
  public const STATUS_CONFIRMED = 'confirmed';
  public const STATUS_CANCELLED = 'cancelled';
  public const STATUS_COMPLETED = 'completed';
  public const STATUS_NO_SHOW = 'no_show';

  public const STATUS_DECLINED = 'declined';

  // ==================== ATTRIBUTES ====================

  /**
   * Virtual attribute for scheduled_at that combines booked_at_date and booked_at_time
   * Provides backward compatibility
   */
  protected function scheduledAt(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->booked_at_date && $this->booked_at_time
        ? Carbon::parse($this->booked_at_date->toDateString() . ' ' . $this->booked_at_time)
        : null,
    );
  }

  /**
   * Virtual attribute for formatted date (e.g., "Mon, 25 Feb 2026")
   */
  protected function formattedDate(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->booked_at_date
        ? Carbon::parse($this->booked_at_date)->format('D, d M Y')
        : null,
    );
  }

  /**
   * Virtual attribute for formatted time (e.g., "3:00 PM")
   */
  protected function formattedTime(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->booked_at_time
        ? Carbon::parse($this->booked_at_time, 'UTC')->format('g:i A')
        : null,
    );
  }

  /**
   * Virtual attribute to check if booking is expired
   */
  protected function isExpired(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->scheduled_at ? $this->scheduled_at->isPast() : false,
    );
  }

  /**
   * Virtual attribute for name (maps to booker_name)
   */
  protected function name(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->booker_name,
      set: fn ($value) => ['booker_name' => $value],
    );
  }

  /**
   * Virtual attribute for email (maps to booker_email)
   */
  protected function email(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->booker_email,
      set: fn ($value) => ['booker_email' => $value],
    );
  }

  // ==================== RELATIONSHIPS ====================

  /**
   * The event this booking is associated with
   */
  public function event()
  {
    return $this->belongsTo(Event::class);
  }

  /**
   * Tracking data for this booking (UTM parameters, click IDs).
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
  public function tracking()
  {
    return $this->hasOne(BookingTracking::class);
  }

  /**
   * Payment associated with this booking (if any).
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
  public function payment()
  {
    return $this->hasOne(Payment::class);
  }

  /**
   * User who made this booking
   */
  public function booker()
  {
      return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Follow-up invite this booking was created from (if applicable)
   */
  public function followUpInvite()
  {
      return $this->belongsTo(FollowUpInvite::class, 'followup_invite_id');
  }

  /**
   * User who cancelled this booking
   */
  public function cancelledBy()
  {
      return $this->belongsTo(User::class, 'cancelled_by');
  }

  /**
   * Refund associated with this booking (if any)
   */
  public function refund()
  {
      return $this->hasOne(\App\Models\Refund::class);
  }

  // ==================== QUERY SCOPES ====================

  /**
   * Scope: Filter confirmed bookings
   */
  public function scopeConfirmed(Builder $query): Builder
  {
      return $query->where('status', self::STATUS_CONFIRMED);
  }

  /**
   * Scope: Filter cancelled bookings
   */
  public function scopeCancelled(Builder $query): Builder
  {
      return $query->where('status', self::STATUS_CANCELLED);
  }

  /**
   * Scope: Filter pending bookings
   */
  public function scopePending(Builder $query): Builder
  {
      return $query->where('status', self::STATUS_PENDING);
  }

  /**
   * Scope: Filter upcoming bookings
   */
  public function scopeUpcoming(Builder $query): Builder
  {
      return $query->where('booked_at_date', '>', now()->toDateString());
  }

  /**
   * Scope: Filter past bookings
   */
  public function scopePast(Builder $query): Builder
  {
      return $query->where('booked_at_date', '<=', now()->toDateString());
  }

  /**
   * Scope: Filter by event
   */
  public function scopeForEvent(Builder $query, int $eventId): Builder
  {
      return $query->where('event_id', $eventId);
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
      return $query->whereBetween('booked_at_date', [$startDate, $endDate]);
  }

  /**
   * Scope: With refundable bookings
   */
  public function scopeRefundable(Builder $query): Builder
  {
      return $query->where('status', self::STATUS_CANCELLED)
                   ->whereNull('refund_status')
                   ->orWhere('refund_status', 'pending');
  }

  /**
   * Scope: Search bookings
   */
  public function scopeSearch(Builder $query, string $search): Builder
  {
      return $query->where(function($q) use ($search) {
          $q->where('name', 'LIKE', "%{$search}%")
            ->orWhere('email', 'LIKE', "%{$search}%")
            ->orWhere('phone', 'LIKE', "%{$search}%");
      });
  }

  // ==================== HELPER METHODS ====================

  /**
   * Check if this is a follow-up booking
   */
  public function isFollowUp(): bool
  {
      return $this->is_followup ?? false;
  }

  /**
   * Check if this booking is completed (past date/time)
   */
  public function isCompleted(): bool
  {
      $bookingDateTime = \Carbon\Carbon::parse($this->booked_at_date->toDateString() . ' ' . $this->booked_at_time);
      return $bookingDateTime->isPast() && $this->status !== self::STATUS_CANCELLED;
  }

  /**
   * Check if this booking can be cancelled
   *
   * @return bool
   */
  public function canCancel()
  {
      return $this->event && $this->event->canBeCancelled($this);
  }

  /**
   * Get the refund amount for this booking
   *
   * @return array ['percentage' => int, 'amount' => float, 'gateway_charges' => float]
   */
  public function getRefundAmount()
  {
      if (!$this->event) {
          return ['percentage' => 0, 'amount' => 0, 'gateway_charges' => 0];
      }

      return $this->event->calculateRefundAmount($this);
  }

  /**
   * Cancel this booking
   *
   * @param string $reason
   * @param int $userId - ID of user who cancelled (booker or admin)
   * @return bool
   */
  public function cancel($reason, $userId)
  {
      if (!$this->canCancel()) {
          return false;
      }

      // Update booking status
      $this->update([
          'status' => self::STATUS_DECLINED,
          'cancelled_at' => now(),
          'cancelled_by' => $userId,
          'cancellation_reason' => $reason,
          'refund_status' => Refund::STATUS_PENDING,
      ]);

      return true;
  }

  /**
   * Check if booking is cancelled
   *
   * @return bool
   */
  public function isCancelled()
  {
      return !is_null($this->cancelled_at);
  }

  /**
   * Check if refund is applicable for this booking
   *
   * @return bool
   */
  public function isRefundApplicable()
  {
      return $this->isCancelled() && $this->refund_status !== 'not_applicable';
  }
}
