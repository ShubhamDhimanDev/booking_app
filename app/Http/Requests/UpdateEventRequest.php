<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
  /**
   * Get the validation rules that apply to the request.
   *
   * @return array<string, mixed>
   */
  public function rules()
  {
    return [
      'title' => 'required|string',
      'description' => 'nullable|string',
      'price' => 'required|numeric|min:0',
      'slug' => 'required|alpha_dash|unique:events,slug,' . $this->event->id,
      'color' => 'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i',
      'available_from_date' => 'required|date',
      'available_to_date' => 'required|date|after_or_equal:available_from_date',
      'available_from_time' => 'nullable|date_format:H:i',
      'available_to_time' => 'nullable|date_format:H:i|after_or_equal:available_from_time',
      'duration' => 'nullable|integer|min:1',
      'available_week_days' => 'nullable|array',
      'available_week_days.*' => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
      'custom_timeslots' => 'required|array',
      'custom_timeslots.*.start' => 'required_with:custom_timeslots|date_format:H:i',
      'custom_timeslots.*.end' => 'required_with:custom_timeslots|date_format:H:i',
      'exclusions' => 'nullable|array',
      'exclusions.*.date' => 'required_with:exclusions|date',
      'exclusions.*.exclude_all' => 'nullable|boolean',
      'exclusions.*.times' => 'nullable|array',
      'exclusions.*.times.*' => 'date_format:H:i',
    ];
  }

  public function withValidator($validator)
  {
    $validator->after(function ($validator) {
      $slots = $this->input('custom_timeslots');
      if (! $slots) return;

      if (! is_array($slots)) return;
      $list = array_values($slots);

      $intervals = [];
      foreach ($list as $idx => $s) {
        if (empty($s['start']) || empty($s['end'])) {
          $validator->errors()->add('custom_timeslots', "Timeslot #" . ($idx+1) . " must have start and end time.");
          continue;
        }
        [$sh, $sm] = explode(':', $s['start']);
        [$eh, $em] = explode(':', $s['end']);
        $startMin = intval($sh) * 60 + intval($sm);
        $endMin = intval($eh) * 60 + intval($em);
        if ($endMin <= $startMin) {
          $validator->errors()->add('custom_timeslots', "Timeslot #" . ($idx+1) . " must end after it starts.");
        }
        $intervals[] = ['start' => $startMin, 'end' => $endMin, 'idx' => $idx];
      }

      usort($intervals, function($a, $b){ return $a['start'] <=> $b['start']; });
      for ($i = 1; $i < count($intervals); $i++) {
        if ($intervals[$i]['start'] < $intervals[$i-1]['end']) {
          $validator->errors()->add('custom_timeslots', 'Custom timeslots must not overlap.');
          break;
        }
      }
    });
  }
}
