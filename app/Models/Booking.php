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
 * - Index: (event_id, scheduled_at) - for event bookings list
 * - Index: (user_id, status) - for user bookings with status filter
 * - Index: (status, scheduled_at) - for dashboard queries
 * - Index: (confirmation_token) - unique for confirmations
 * - Index: (email, status) - for guest bookings lookup
 *
 * @property int $id
 * @property int $event_id
 * @property int|null $user_id
 * @property string $email
 * @property string $name
 * @property string|null $phone
 * @property string $status
 * @property \Carbon\Carbon $scheduled_at
 * @property \Carbon\Carbon|null $cancelled_at
 * @property string|null $cancellation_reason
 */
class Booking extends Model
{
  use HasFactory, SoftDeletes;

  /**
   * Mass-assignable attributes
   */
  protected $fillable = [
    'event_id',
    'user_id',
    'email',
    'name',
    'phone',
    'status',
    'scheduled_at',
    'notes',
    'timezone',
    'confirmation_token',
    'answered_questions',
    'location',
  ];

  /**
   * Attributes that should be cast
   */
  protected $casts = [
    'scheduled_at' => 'datetime',
    'cancelled_at' => 'datetime',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
    'answered_questions' => 'array',
  ];

  // ==================== CONSTANTS ====================

  public const STATUS_PENDING = 'pending';
  public const STATUS_CONFIRMED = 'confirmed';
  public const STATUS_CANCELLED = 'cancelled';
  public const STATUS_COMPLETED = 'completed';
  public const STATUS_NO_SHOW = 'no_show';

  // ==================== ATTRIBUTES ====================

  /**
   * Format booked_at_time in hours and minutes only (H:i)
   */
  protected function bookedAtTime(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => $value ? Carbon::parse($value)->format('H:i') : null,
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
      return $query->where('scheduled_at', '>', now());
  }

  /**
   * Scope: Filter past bookings
   */
  public function scopePast(Builder $query): Builder
  {
      return $query->where('scheduled_at', '<=', now());
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
      return $query->whereBetween('scheduled_at', [$startDate, $endDate]);
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
      $bookingDateTime = \Carbon\Carbon::parse($this->booked_at_date . ' ' . $this->booked_at_time);
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
