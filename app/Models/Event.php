<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Event extends Model
{
  use HasFactory;

  protected $guarded = [];

  protected $casts = [
    'available_week_days' => 'array',
    'custom_timeslots' => 'array',
    'refund_rules' => 'array',
    'refund_enabled' => 'boolean',
    'deduct_gateway_charges' => 'boolean',
  ];

  /**
   * Exclusions for this event (per-date excluded times or full-day exclusion)
   */
  public function exclusions()
  {
    return $this->hasMany(\App\Models\EventExclusion::class);
  }

  /**
   * Reminders configured for this event (admin-defined offsets in minutes)
   */
  public function reminders()
  {
    return $this->hasMany(\App\Models\EventReminder::class);
  }

  protected $withCount = ['bookings'];


  /**
   * Format available from time in hours and minutes only (H:i)
   *
   * @return \Illuminate\Database\Eloquent\Casts\Attribute
   */
  // Note: `available_from_time` / `available_to_time` columns removed — custom_timeslots is used instead.


  /**
   * Timeslots
   *
   * @return array
   */
  public function getTimeslotsAttribute()
  {
    // If admin provided custom_timeslots, use them (apply to every date)
    if (! empty($this->custom_timeslots) && is_array($this->custom_timeslots) && count($this->custom_timeslots) > 0) {
      $result = [];
      foreach ($this->custom_timeslots as $ts) {
        if (empty($ts['start']) || empty($ts['end'])) continue;
        $result[] = [
          'start' => Carbon::parse($ts['start'])->format('H:i'),
          'end' => Carbon::parse($ts['end'])->format('H:i'),
        ];
      }
      return $result;
    }

    // If no explicit times are set (legacy mode removed), return empty
    if (empty($this->available_from_time) || empty($this->available_to_time) || empty($this->duration)) {
      return [];
    }

    $startTime = Carbon::parse($this->available_from_time);
    $endTime = Carbon::parse($this->available_to_time);
    $timeSlots = [];

    while ($startTime->lessThan($endTime)) {
      $timeSlots[] = [
        'start' => Carbon::parse($startTime)->format('H:i'),
        'end' => Carbon::parse($startTime)->addMinutes($this->duration)->format('H:i'),
      ];

      $startTime->addMinutes($this->duration);
    }

    return $timeSlots;
  }


  /**
   * The user created the event
   *
   * @return \App\Models\User
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }


  /**
   * The bookings associated with this event
   *
   * @return \Illuminate\Support\Collection<\App\Models\Booking>
   */
  public function bookings()
  {
    return $this->hasMany(Booking::class);
  }

  /**
   * Check if a booking can be cancelled based on event refund policy
   *
   * @param \App\Models\Booking $booking
   * @return bool
   */
  public function canBeCancelled($booking)
  {
    // Refund must be enabled
    if (!$this->refund_enabled) {
      return false;
    }

    // Booking must be confirmed
    if ($booking->status !== 'confirmed') {
      return false;
    }

    // Cannot cancel if already cancelled
    if ($booking->cancelled_at) {
      return false;
    }

    // Check minimum cancellation hours
    if ($this->min_cancellation_hours > 0) {
      $eventDateTime = Carbon::parse($booking->booked_at_date . ' ' . $booking->booked_at_time);
      $hoursUntilEvent = Carbon::now()->diffInHours($eventDateTime, false);

      if ($hoursUntilEvent < $this->min_cancellation_hours) {
        return false;
      }
    }

    return true;
  }

  /**
   * Calculate refund amount based on refund policy
   *
   * @param \App\Models\Booking $booking
   * @return array ['percentage' => int, 'amount' => float, 'gateway_charges' => float]
   */
  public function calculateRefundAmount($booking)
  {
    if (!$this->canBeCancelled($booking)) {
      return ['percentage' => 0, 'amount' => 0, 'gateway_charges' => 0];
    }

    $eventDateTime = Carbon::parse($booking->booked_at_date . ' ' . $booking->booked_at_time);
    $hoursUntilEvent = Carbon::now()->diffInHours($eventDateTime, false);
    $daysUntilEvent = floor($hoursUntilEvent / 24);

    $refundPercentage = 0;

    // Determine refund percentage based on policy type
    switch ($this->refund_policy_type) {
      case 'flexible':
        // Flexible: 100% if 7+ days, 50% if 2-7 days, 0% otherwise
        if ($daysUntilEvent >= 7) {
          $refundPercentage = 100;
        } elseif ($daysUntilEvent >= 2) {
          $refundPercentage = 50;
        }
        break;

      case 'moderate':
        // Moderate: 100% if 48+ hours, 50% if 24-48 hours, 0% otherwise
        if ($hoursUntilEvent >= 48) {
          $refundPercentage = 100;
        } elseif ($hoursUntilEvent >= 24) {
          $refundPercentage = 50;
        }
        break;

      case 'strict':
        // Strict: 100% if 72+ hours, 0% otherwise
        if ($hoursUntilEvent >= 72) {
          $refundPercentage = 100;
        }
        break;

      case 'custom':
        // Custom: Use refund_rules JSON
        $refundRules = $this->refund_rules;

        if (!empty($refundRules)) {
          // Convert to array if it's a string (JSON)
          if (is_string($refundRules)) {
            $refundRules = json_decode($refundRules, true);
          }

          // If it's an object with timestamp keys, convert to indexed array
          if (is_array($refundRules) && !empty($refundRules)) {
            // Extract values if it has non-numeric keys (timestamps)
            $rules = array_values($refundRules);

            // Sort rules by hours in descending order
            usort($rules, function ($a, $b) {
              return ((int)($b['hours'] ?? 0)) - ((int)($a['hours'] ?? 0));
            });

            foreach ($rules as $rule) {
              if ($hoursUntilEvent >= ((int)($rule['hours'] ?? 0))) {
                $refundPercentage = (int)($rule['percentage'] ?? 0);
                break;
              }
            }
          }
        }
        break;
    }

    // Calculate amounts
    $bookingAmount = $booking->payment ? $booking->payment->amount : 0;
    $refundAmount = ($bookingAmount * $refundPercentage) / 100;

    // Calculate gateway charges if applicable
    $gatewayCharges = 0;
    if ($this->deduct_gateway_charges && $refundPercentage > 0) {
      // Gateway charges are typically 2-3% (we'll use payment gateway to calculate actual)
      // For now, use a placeholder - will be calculated by RefundService
      $gatewayCharges = 0; // Set by RefundService based on gateway
    }

    return [
      'percentage' => $refundPercentage,
      'amount' => round($refundAmount, 2),
      'gateway_charges' => $gatewayCharges,
    ];
  }

  /**
   * Get human-readable refund policy description
   *
   * @return string
   */
  public function getRefundPolicyDescription()
  {
    if (!$this->refund_enabled) {
      return 'No refunds available for this event.';
    }

    $description = '';

    switch ($this->refund_policy_type) {
      case 'flexible':
        $description = 'Flexible: 100% refund if cancelled 7+ days before, 50% refund if cancelled 2-7 days before.';
        break;

      case 'moderate':
        $description = 'Moderate: 100% refund if cancelled 48+ hours before, 50% refund if cancelled 24-48 hours before.';
        break;

      case 'strict':
        $description = 'Strict: 100% refund only if cancelled 72+ hours before the event.';
        break;

      case 'custom':
        $refundRules = $this->refund_rules;

        // Handle empty or null rules
        if (empty($refundRules)) {
          $description = 'Custom refund policy (contact organizer for details).';
          break;
        }

        // Convert to array if it's a string (JSON)
        if (is_string($refundRules)) {
          $refundRules = json_decode($refundRules, true);
        }

        // If it's an object with timestamp keys, convert to indexed array
        if (is_array($refundRules) && !empty($refundRules)) {
          // Extract values if it has non-numeric keys (timestamps)
          $rules = array_values($refundRules);

          // Sort rules by hours in descending order
          usort($rules, function ($a, $b) {
            return ((int)($b['hours'] ?? 0)) - ((int)($a['hours'] ?? 0));
          });

          $parts = [];
          foreach ($rules as $rule) {
            $hours = $rule['hours'] ?? 0;
            $percentage = $rule['percentage'] ?? 0;
            $parts[] = "{$percentage}% refund if cancelled {$hours}+ hours before";
          }
          $description = 'Custom: ' . implode(', ', $parts) . '.';
        } else {
          $description = 'Custom refund policy (contact organizer for details).';
        }
        break;

      default:
        $description = 'Refund policy not specified.';
    }

    if ($this->min_cancellation_hours > 0) {
      $description .= " Minimum {$this->min_cancellation_hours} hours notice required.";
    }

    if ($this->deduct_gateway_charges) {
      $description .= ' Payment gateway charges will be deducted from refund.';
    }

    return $description;
  }
}
