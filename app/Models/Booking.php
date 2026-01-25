<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Payment;

class Booking extends Model
{
  use HasFactory, SoftDeletes;

  protected $guarded = [];

  protected $casts = [
    'cancelled_at' => 'datetime',
  ];

  /**
   * Format booked_at_time in hours and minutes only (H:i)
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  protected function bookedAtTime(): Attribute
  {
    return Attribute::make(
      get: fn ($value) => Carbon::parse($value)->format('H:i'),
    );
  }

  /**
   * The event this bookin is associated with
   *
   * @return \App\Models\Event
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

  public function booker()
  {
      return $this->belongsTo(User::class, 'user_id');
  }

  /**
   * Follow-up invite this booking was created from (if applicable)
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function followUpInvite()
  {
      return $this->belongsTo(FollowUpInvite::class, 'followup_invite_id');
  }

  /**
   * Check if this is a follow-up booking
   *
   * @return bool
   */
  public function isFollowUp()
  {
      return $this->is_followup;
  }

  /**
   * Check if this booking is completed (past date/time)
   *
   * @return bool
   */
  public function isCompleted()
  {
      $bookingDateTime = \Carbon\Carbon::parse($this->booked_at_date . ' ' . $this->booked_at_time);
      return $bookingDateTime->isPast() && $this->status !== 'cancelled';
  }

  /**
   * User who cancelled this booking
   *
   * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
   */
  public function cancelledBy()
  {
      return $this->belongsTo(User::class, 'cancelled_by');
  }

  /**
   * Refund associated with this booking (if any)
   *
   * @return \Illuminate\Database\Eloquent\Relations\HasOne
   */
  public function refund()
  {
      return $this->hasOne(\App\Models\Refund::class);
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
          'status' => 'declined',
          'cancelled_at' => now(),
          'cancelled_by' => $userId,
          'cancellation_reason' => $reason,
          'refund_status' => 'pending',
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
