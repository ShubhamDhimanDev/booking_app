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
}
