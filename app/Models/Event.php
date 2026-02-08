<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * Event Model - SaaS Ready
 *
 * Recommended Database Indexes:
 * - Index: (user_id)
 * - Index: (slug) UNIQUE
 * - Index: (created_at)
 *
 * NOTE: This model contains heavy business logic that should be extracted to services:
 * - calculateRefundAmount() → RefundService
 * - canBeCancelled() → BookingService
 * - Timeslot generation → EventService with caching
 *
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $slug
 * @property string|null $description
 * @property int $duration Duration in minutes
 * @property float|null $price
 * @property \Carbon\Carbon $available_from_date
 * @property \Carbon\Carbon $available_to_date
 * @property array|null $available_week_days
 * @property array|null $custom_timeslots
 * @property array|null $refund_rules
 * @property bool $refund_enabled
 * @property string $refund_policy_type
 * @property int $min_cancellation_hours
 * @property bool $deduct_gateway_charges
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 *
 * Virtual/Accessor Properties (for backward compatibility):
 * @property string $name Alias for title
 * @property int $duration_minutes Alias for duration
 */
class Event extends Model
{
  use HasFactory, SoftDeletes;

  /**
   * Mass-assignable attributes
   */
  protected $fillable = [
    'organization_id',
    'user_id',
    'title',
    'slug',
    'description',
    'duration',
    'price',
    'available_from_date',
    'available_to_date',
    'available_week_days',
    'custom_timeslots',
    'refund_enabled',
    'refund_policy_type',
    'min_cancellation_hours',
    'refund_rules',
    'deduct_gateway_charges',
  ];

  /**
   * Boot the model - Add multi-tenancy scopes
   */
  protected static function booted()
  {
      // Auto-assign organization_id on creation
      static::creating(function ($event) {
          // Skip during bootstrap
          if (!app()->isBooted()) {
              return;
          }

          if (!$event->organization_id && app()->has('currentOrganization')) {
              $org = app('currentOrganization');
              if ($org && is_object($org) && property_exists($org, 'id')) {
                  $event->organization_id = $org->id;
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
                  $builder->where('events.organization_id', $org->id);
              }
          }
      });
  }

  /**
   * Relationship: Event belongs to an Organization
   */
  public function organization()
  {
      return $this->belongsTo(Organization::class);
  }

  /**
   * Attributes that should be cast
   */
  protected $casts = [
    'available_from_date' => 'date',
    'available_to_date' => 'date',
    'available_week_days' => 'array',
    'custom_timeslots' => 'array',
    'refund_rules' => 'array',
    'refund_enabled' => 'boolean',
    'deduct_gateway_charges' => 'boolean',
    'price' => 'decimal:2',
    'duration' => 'integer',
    'min_cancellation_hours' => 'integer',
    'created_at' => 'datetime',
    'updated_at' => 'datetime',
    'deleted_at' => 'datetime',
  ];

  /**
   * Count bookings by default (DISABLED - load explicitly when needed to avoid memory issues)
   * Use Event::withCount('bookings')->get() when you need the count
   */
  // protected $withCount = ['bookings'];

  // ==================== ATTRIBUTES/ACCESSORS ====================

  /**
   * Virtual attribute for name (maps to title)
   * Provides backward compatibility
   */
  protected function name(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->title,
      set: fn ($value) => ['title' => $value],
    );
  }

  /**
   * Virtual attribute for duration_minutes (maps to duration)
   * Provides backward compatibility
   */
  protected function durationMinutes(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->duration,
      set: fn ($value) => ['duration' => $value],
    );
  }

  // ==================== RELATIONSHIPS ====================

  /**
   * The user who created this event
   */
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Bookings for this event
   */
  public function bookings()
  {
    return $this->hasMany(Booking::class);
  }

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

  // ==================== QUERY SCOPES ====================

  /**
   * Scope: Filter by user
   */
  public function scopeForUser(Builder $query, int $userId): Builder
  {
    return $query->where('user_id', $userId);
  }

  /**
   * Scope: Filter free events
   */
  public function scopeFree(Builder $query): Builder
  {
    return $query->whereNull('price')->orWhere('price', 0);
  }

  /**
   * Scope: Filter paid events
   */
  public function scopePaid(Builder $query): Builder
  {
    return $query->where('price', '>', 0);
  }

  /**
   * Scope: Search events by title or description
   */
  public function scopeSearch(Builder $query, string $search): Builder
  {
    return $query->where(function($q) use ($search) {
      $q->where('title', 'LIKE', "%{$search}%")
        ->orWhere('description', 'LIKE', "%{$search}%")
        ->orWhere('slug', 'LIKE', "%{$search}%");
    });
  }

  // ==================== ATTRIBUTES & ACCESSORS ====================

  /**
   * Virtual attribute for formatted available_from_date (e.g., "01 Feb 2026")
   */
  protected function formattedFromDate(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->available_from_date
        ? $this->available_from_date->format('d M Y')
        : null,
    );
  }

  /**
   * Virtual attribute for formatted available_to_date (e.g., "28 Feb 2026")
   */
  protected function formattedToDate(): Attribute
  {
    return Attribute::make(
      get: fn () => $this->available_to_date
        ? $this->available_to_date->format('d M Y')
        : null,
    );
  }


  /**
   * Timeslots - Generate available time slots for this event
   *
   * NOTE: This accessor performs expensive calculations.
   * TODO: Move to EventService with caching for better performance
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

  // ==================== BUSINESS LOGIC (TO BE MOVED TO SERVICES) ====================

  // ==================== BUSINESS LOGIC (TO BE MOVED TO SERVICES) ====================

  /**
   * Check if a booking can be cancelled based on event refund policy
   *
   * @deprecated Use BookingService::canCancel() instead
   * NOTE: This business logic will be moved to BookingService in next refactoring phase
   *
   * @param \App\Models\Booking $booking
   * @return bool
   */
  public function canBeCancelled($booking): bool
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
      $eventDateTime = Carbon::parse($booking->booked_at_date->toDateString() . ' ' . $booking->booked_at_time);
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
   * @deprecated Use RefundService::calculateRefundAmount() instead
   * NOTE: This complex business logic (200+ lines) will be moved to RefundService
   *
   * @param \App\Models\Booking $booking
   * @return array ['percentage' => int, 'amount' => float, 'gateway_charges' => float]
   */
  public function calculateRefundAmount($booking): array
  {
    if (!$this->canBeCancelled($booking)) {
      return ['percentage' => 0, 'amount' => 0, 'gateway_charges' => 0];
    }

    $eventDateTime = Carbon::parse($booking->booked_at_date->toDateString() . ' ' . $booking->booked_at_time);
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
