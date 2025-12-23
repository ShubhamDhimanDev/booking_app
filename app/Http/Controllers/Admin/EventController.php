<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Carbon\Carbon;
use App\Http\Requests\StoreEventRequest;
use App\Http\Requests\UpdateEventRequest;
use App\Http\Middleware\LinkedWithGoogleMiddleware;

class EventController extends Controller
{
  public function __construct()
  {
    $this->middleware('role:admin|owner')->except('showPublic');
    $this->middleware(LinkedWithGoogleMiddleware::class)->except('showPublic');
  }

  /**
   * Events List
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
   */
  public function index()
  {
    /** @var mixed */
    $user = auth()->user();
    $events = $user->events()->latest()->get();

    return view('admin.events.index', compact('events'));
  }

  /**
   * Create event
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
   */
  public function create()
  {
    $days = ['monday','tuesday','wednesday','thursday','friday','saturday','sunday'];
    return view('admin.events.create', compact('days'));
  }

  /**
   * Post Event Create
   * @param StoreEventRequest $request
   * @return \Illuminate\Http\RedirectResponse
   */
  public function store(StoreEventRequest $request)
  {
    /** @var mixed */
    $user = auth()->user();
    // retrieve validated payload, but also allow reminders from raw input
    $payload = $request->validated();
    // Ensure weekdays are present as an array when the form sends none (unchecked)
    $payload['available_week_days'] = $request->input('available_week_days', []);

    // detach exclusions from payload so we don't try to mass-assign a non-existent column on events
    $exclusions = $payload['exclusions'] ?? null;
    if (array_key_exists('exclusions', $payload)) {
      unset($payload['exclusions']);
    }

    // Allow reminders via the raw request input (in case the FormRequest doesn't include them)
    // Use a `reminders_present` hidden field to indicate the admin intended to manage reminders.
    $reminders = null;
    if ($request->has('reminders_present')) {
      $reminders = $request->input('reminders', []);
    }
    if (array_key_exists('reminders', $payload)) unset($payload['reminders']);
    /** @var \App\Models\Event */
    $event = $user->events()->create($payload);

    // handle exclusions persisted separately in event_exclusions table
    if (! empty($exclusions) && is_array($exclusions)) {
      foreach ($exclusions as $ex) {
        $event->exclusions()->create([
          'date' => $ex['date'] ?? null,
          'exclude_all' => !empty($ex['exclude_all']),
          'times' => !empty($ex['times']) ? array_values($ex['times']) : null,
        ]);
      }
    }

    // handle reminders persisted separately in event_reminders table
    if (! empty($reminders) && is_array($reminders)) {
      foreach ($reminders as $r) {
        // allow either `offset_minutes` directly or {value,unit}
        $offset = null;
        if (isset($r['offset_minutes'])) {
          $offset = intval($r['offset_minutes']);
        } elseif (isset($r['value']) && isset($r['unit'])) {
          $value = intval($r['value']);
          switch ($r['unit']) {
            case 'minutes': $offset = $value; break;
            case 'hours': $offset = $value * 60; break;
            case 'days': $offset = $value * 60 * 24; break;
            default: $offset = $value; break;
          }
        }

        if ($offset !== null) {
          $event->reminders()->create([
            'offset_minutes' => $offset,
            'name' => $r['name'] ?? null,
            'enabled' => !empty($r['enabled']) ? true : false,
          ]);
        }
      }
    }


    return redirect()->route('admin.events.index')->with([
      'alert_type' => 'success',
      'alert_message' => "Event created successfully!"
    ]);
  }

  /**
   * Show event to public
   * @param Event $event
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
   */
  public function showPublic(Event $event)
  {
    $event->load('user')->append('timeslots');

    // Load only confirmed bookings; pending should stay available for others to book
    $confirmedBookings = $event->bookings()->where('status', 'confirmed')->get()->groupBy(function($item) {
      return $item->booked_at_date;
    });

    // Build an array of booked slots per date for easy lookup on the frontend
    $bookedSlots = [];
    foreach ($confirmedBookings as $date => $collection) {
      $bookedSlots[$date] = $collection->pluck('booked_at_time')->values()->all();
    }

    // Build availableSlots (dates that have at least one free timeslot)
    $startDate = Carbon::parse($event->available_from_date);
    $endDate = Carbon::parse($event->available_to_date);
    $availableSlots = [];

    for ($date = $startDate->copy(); $date->lessThanOrEqualTo($endDate); $date->addDay()) {
      $dateStr = $date->toDateString();
      $free = [];

      foreach ($event->timeslots as $ts) {
        $startTime = $ts['start'];
        $isBooked = isset($bookedSlots[$dateStr]) && in_array($startTime, $bookedSlots[$dateStr]);
        if (! $isBooked) {
          $free[] = $ts;
        }
      }

      // ensure timeslots are ordered early -> late
      usort($free, function($a, $b) {
        $ta = strtotime($a['start']);
        $tb = strtotime($b['start']);
        return $ta <=> $tb;
      });

      if (count($free) > 0) {
        $availableSlots[] = [
          'date' => $dateStr,
          'timeslots' => $free,
        ];
      }
    }

    // Get active payment gateway config for frontend
    $paymentGatewayManager = app(\App\Services\PaymentGatewayManager::class);
    $gatewayConfig = $paymentGatewayManager->getActiveGatewayConfig();
    $activeGateway = $gatewayConfig['name'] ?? 'razorpay';

    return view('book-event', compact('event', 'availableSlots', 'bookedSlots', 'activeGateway', 'gatewayConfig'));
  }

  /**
   * edit event
   * @param Event $event
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
   */
  public function edit(Event $event)
  {
    $this->authorize('update', $event);

    return view('admin.events.edit', compact('event'));
  }

  /**
   * update event
   * @param UpdateEventRequest $request
   * @param Event $event
   * @return \Illuminate\Http\RedirectResponse
   */
  public function update(UpdateEventRequest $request, Event $event)
  {
    $this->authorize('update', $event);

    $payload = $request->validated();
    // Extract exclusions so update() doesn't try to write a non-existent column
    $exclusions = array_key_exists('exclusions', $payload) ? $payload['exclusions'] : null;
    if (array_key_exists('exclusions', $payload)) {
      unset($payload['exclusions']);
    }

    // If the form omitted weekdays (all unchecked), ensure we persist an empty array
    $payload['available_week_days'] = $request->input('available_week_days', []);

    // Allow reminders via raw input (or validated payload if present)
    // If the form included the reminders area (we add a hidden marker input), treat lack of rows as an intent to clear reminders
    $reminders = null;
    if ($request->has('reminders_present')) {
      $reminders = $request->input('reminders', []);
    }
    if (array_key_exists('reminders', $payload)) unset($payload['reminders']);

    $event->update($payload);

    // sync exclusions: delete existing and recreate (only when the field was present in the request)
    if ($exclusions !== null) {
      $event->exclusions()->delete();
      if (! empty($exclusions) && is_array($exclusions)) {
        foreach ($exclusions as $ex) {
          $event->exclusions()->create([
            'date' => $ex['date'] ?? null,
            'exclude_all' => !empty($ex['exclude_all']),
            'times' => !empty($ex['times']) ? array_values($ex['times']) : null,
          ]);
        }
      }
    }

    // sync reminders: delete existing and recreate (only when the field was present in the request)
    if ($reminders !== null) {
      $event->reminders()->delete();
      if (! empty($reminders) && is_array($reminders)) {
        foreach ($reminders as $r) {
          $offset = null;
          if (isset($r['offset_minutes'])) {
            $offset = intval($r['offset_minutes']);
          } elseif (isset($r['value']) && isset($r['unit'])) {
            $value = intval($r['value']);
            switch ($r['unit']) {
              case 'minutes': $offset = $value; break;
              case 'hours': $offset = $value * 60; break;
              case 'days': $offset = $value * 60 * 24; break;
              default: $offset = $value; break;
            }
          }

          if ($offset !== null) {
            $event->reminders()->create([
              'offset_minutes' => $offset,
              'name' => $r['name'] ?? null,
              'enabled' => isset($r['enabled']) ? (bool)$r['enabled'] : true,
            ]);
          }
        }
      }
    }

    return back()->with([
      'alert_type' => 'success',
      'alert_message' => "Event updated successfully!"
    ]);
  }

  /**
   * delete event
   * @param Event $event
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Event $event)
  {
    $this->authorize('delete', $event);

    if ($event->delete()) {
      return back()->with([
        'alert_type' => 'success',
        'alert_message' => "Event deleted!"
      ]);
    }
  }
}
