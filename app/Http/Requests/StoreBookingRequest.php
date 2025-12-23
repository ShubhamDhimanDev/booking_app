<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{

  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, mixed>
   */
  public function rules()
  {
    $event = request('event');

    $dateRules = ['required', 'date', 'after_or_equal:today'];
    if ($event && !empty($event->available_from_date)) {
      $dateRules[] = 'after_or_equal:' . $event->available_from_date;
    }
    if ($event && !empty($event->available_to_date)) {
      $dateRules[] = 'before_or_equal:' . $event->available_to_date;
    }
    // weekday check
    $dateRules[] = function ($attribute, $value, $fail) use ($event) {
      if ($event && !empty($event->available_week_days) && is_array($event->available_week_days)) {
        $weekday = \Carbon\Carbon::parse($value)->format('l');
        $weekday = strtolower($weekday);
        if (! in_array($weekday, $event->available_week_days)) {
          $fail('Bookings are not allowed on ' . ucfirst($weekday) . ' for this event.');
        }
      }
    };

    $timeRules = ['required', 'date_format:H:i'];
    // ensure the selected time is a defined timeslot, not excluded, and not already booked
    $timeRules[] = function ($attribute, $value, $fail) use ($event) {
      if (! $event) return;
      // ensure event timeslots are available
      $event->append('timeslots');
      $found = false;
      foreach ($event->timeslots as $ts) {
        if (isset($ts['start']) && $ts['start'] === $value) {
          $found = true;
          break;
        }
      }
      if (! $found) {
        $fail('Selected time is not available for this event.');
        return;
      }

      // check per-event exclusions for the requested date
      $bookedDate = request('booked_at_date');
      if ($bookedDate) {
        $exclusion = $event->exclusions()->whereDate('date', $bookedDate)->first();
        if ($exclusion) {
          if ($exclusion->exclude_all) {
            $fail('Selected date is blocked for this event.');
            return;
          }
          if (is_array($exclusion->times) && in_array($value, $exclusion->times)) {
            $fail('Selected time is excluded for the chosen date.');
            return;
          }
        }
      }

      // check existing bookings (confirmed/pending)
      // Only block confirmed bookings; pending (unpaid) slots can be re-booked
      if ($bookedDate && $event->bookings()->where('booked_at_date', $bookedDate)->where('booked_at_time', $value)->where('status', 'confirmed')->exists()) {
        $fail('This timeslot is already booked try another one.');
      }
    };

    return [
      'booker_name' => 'required|string',
      'booker_email' => 'required|email',
      'booked_at_date' => $dateRules,
      'booked_at_time' => $timeRules,
    ];
  }
}
