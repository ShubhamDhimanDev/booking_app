<?php

namespace App\Http\Controllers;


use Str;
use Exception;
use Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Google\Client;
use App\Models\Event;
use App\Models\Booking;
use App\Http\Requests\StoreBookingRequest;
use App\Models\User;
use App\Notifications\BookingDeclinedNotification;
use App\Notifications\BookingRescheduledNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Services\PaymentGatewayManager;
use App\Http\Controllers\Controller;

class BookingController extends Controller
{
  /**
   * Shows all bookings
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
   */
  public function index()
  {
    /** @var \App\Models\User */
    $user = auth()->user();

    $bookings = Booking::whereHas('event', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })
    ->with(['event', 'booker'])
    ->where('user_id', '!=', NULL)
    ->orderBy('booked_at_date', 'asc')
    ->paginate(10);

    return view('admin.bookings.index', compact('bookings'));
  }

  public function pay(Request $request, PaymentGatewayManager $gatewayManager)
  {
    // Use the selected payment gateway
    $gateway = $gatewayManager->getActiveGateway();
    $paymentData = [
      'amount' => $request->input('amount'),
      'receipt' => $request->input('receipt'),
      // ...other data as needed...
    ];
    $response = $gateway->initiatePayment($paymentData);
    // Return or render as needed for frontend
    return response()->json($response);
  }

    /**
     * Show user booking list
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\JsonResponse
     */
    public function userIndex(Request $request)
    {

    /** @var \App\Models\User */
    $user = auth()->user();

    $query = Booking::where('user_id', $user->id)
      ->with(['event.user', 'payment'])
      ->latest();

    // Apply status filter if provided
    if ($request->has('status') && $request->status !== 'all') {
      $query->where('status', $request->status);
    }

    $bookings = $query->paginate(9);

    // Handle AJAX requests
    if ($request->ajax() || $request->wantsJson()) {
      $html = view('user.bookings.partials.bookings-grid', compact('bookings'))->render();

      return response()->json([
        'html' => $html,
        'current_page' => $bookings->currentPage(),
        'last_page' => $bookings->lastPage(),
        'total' => $bookings->total()
      ]);
    }

    // Calculate status counts for filter buttons
    $totalCount = Booking::where('user_id', $user->id)->count();
    $confirmedCount = Booking::where('user_id', $user->id)->where('status', 'confirmed')->count();
    $pendingCount = Booking::where('user_id', $user->id)->where('status', 'pending')->count();
    $cancelledCount = Booking::where('user_id', $user->id)->where('status', 'cancelled')->count();

    return view('user.bookings.index', compact('bookings', 'totalCount', 'confirmedCount', 'pendingCount', 'cancelledCount'));
    }

    /**
     * @param Booking $booking
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function showRescheduleForm(Booking $booking)
    {
    $user = auth()->user();

    if ($booking->user_id !== $user->id) {
      abort(403);
    }

    $booking->load(['event', 'payment']);

    // Build available slots for the event between its available_from_date and available_to_date
    $event = $booking->event;
    $event->append('timeslots');

    // Only confirmed bookings occupy slots; pending (unpaid) should not block rebooking
    $confirmedBookings = $event->bookings()
      ->where('status', 'confirmed')
      ->get()
      ->groupBy('booked_at_date');

    // Build bookedSlots mapping date => [times] for the frontend to disable occupied slots
    $bookedSlots = [];
    foreach ($confirmedBookings as $date => $collection) {
      $bookedSlots[$date] = $collection->pluck('booked_at_time')->values()->all();
    }

    $startDate = Carbon::parse($event->available_from_date);
    $endDate = Carbon::parse($event->available_to_date);

    $availableSlots = [];

    for ($date = $startDate->copy(); $date->lessThanOrEqualTo($endDate); $date->addDay()) {
      $dateStr = $date->toDateString();
      // skip dates that are not allowed by event available_week_days
      if (!empty($event->available_week_days) && is_array($event->available_week_days)) {
        $weekday = strtolower($date->format('l'));
        if (! in_array($weekday, $event->available_week_days)) {
          continue;
        }
      }
      $free = [];

      foreach ($event->timeslots as $ts) {
        $startTime = $ts['start'];
        $isBooked = isset($bookedSlots[$dateStr]) && in_array($startTime, $bookedSlots[$dateStr]);

        if (! $isBooked) {
          $free[] = $ts;
        }
      }

      if (count($free) > 0) {
        $availableSlots[] = [
          'date' => $dateStr,
          'timeslots' => $free,
        ];
      }
    }

    return view('user.bookings.reschedule', compact('booking', 'availableSlots', 'bookedSlots'));
    }


  /**
   * Show the details form after slot selection
   * @param Event $event
   * @param Request $request
   * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
   */
  public function showDetailsForm(Event $event, Request $request)
  {
    $date = $request->query('date');
    $time = $request->query('time');

    // Validate that date and time are provided
    if (!$date || !$time) {
      return redirect()->route('events.show.public', $event->slug)
        ->withErrors(['error' => 'Please select a date and time first.']);
    }

    // Basic date/time validation
    if (!\Carbon\Carbon::hasFormat($date, 'Y-m-d') || !\Carbon\Carbon::hasFormat($time, 'H:i')) {
      return redirect()->route('events.show.public', $event->slug)
        ->withErrors(['error' => 'Invalid date or time format.']);
    }

    return view('bookings.details', compact('event', 'date', 'time'));
  }

  /**
   * @param StoreBookingRequest $request
   * @param Event $event
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
   */
  public function store(StoreBookingRequest $request, Event $event)
  {
    // Extract validated payload
    $validated = $request->validated();
    $bookerEmail = $validated['booker_email'];
    $bookerName = $validated['booker_name'];
    $bookedDate = $validated['booked_at_date'];
    $bookedTime = $validated['booked_at_time'];
    $phone      = $request->phone;
    // $dob         = $validated['dob'];

    // Server-side availability checks
    // 1) date within event range
    if ($event->available_from_date && Carbon::parse($bookedDate)->lt(Carbon::parse($event->available_from_date))) {
      return response()->json(['error' => 'Selected date is before event availability.'], 422);
    }
    if ($event->available_to_date && Carbon::parse($bookedDate)->gt(Carbon::parse($event->available_to_date))) {
      return response()->json(['error' => 'Selected date is after event availability.'], 422);
    }

    // 2) weekday allowed
    if (!empty($event->available_week_days) && is_array($event->available_week_days)) {
      $weekday = strtolower(Carbon::parse($bookedDate)->format('l'));
      if (! in_array($weekday, $event->available_week_days)) {
        return response()->json(['error' => 'Bookings are not allowed on ' . ucfirst($weekday) . ' for this event.'], 422);
      }
    }

    // 3) timeslot exists in event->timeslots
    $event->append('timeslots');
    $slotFound = false;
    foreach ($event->timeslots as $ts) {
      if (isset($ts['start']) && $ts['start'] === $bookedTime) {
        $slotFound = true;
        break;
      }
    }
    if (! $slotFound) {
      return response()->json(['error' => 'Selected time is not available for this event.'], 422);
    }

    // 4) per-event exclusion check
    $exclusion = $event->exclusions()->whereDate('date', $bookedDate)->first();
    if ($exclusion) {
      if ($exclusion->exclude_all) {
        return response()->json(['error' => 'Selected date is blocked for this event.'], 422);
      }
      if (is_array($exclusion->times) && in_array($bookedTime, $exclusion->times)) {
        return response()->json(['error' => 'Selected time is excluded for the chosen date.'], 422);
      }
    }

    // 5) ensure no existing confirmed booking occupies the same slot
    $exists = $event->bookings()
      ->where('booked_at_date', $bookedDate)
      ->where('booked_at_time', $bookedTime)
      ->where('status', 'confirmed')
      ->exists();
    if ($exists) {
      return response()->json(['error' => 'Selected timeslot is already booked. Please choose another slot.'], 409);
    }

    // 6) NEW: Check if the event owner has ANY confirmed booking at this date/time across all their events
$ownerHasBooking = Booking::whereHas('event', function($q) use ($event) {
      $q->where('user_id', $event->user_id);
    })
    ->where('booked_at_date', $bookedDate)
    ->where('booked_at_time', $bookedTime)
    ->where('status', 'confirmed')
    ->exists();

if ($ownerHasBooking) {
  return response()->json(['error' => 'The event owner is not available at this time.  Please choose another slot.'], 409);
}

    /** @var \App\Models\User|null */
    $user = User::where('email', $bookerEmail)->first();

    if (! $user) {
      // create a user with random password and email credentials
      $randomPassword = Str::random(12);
      $user = User::create([
        'name' => $bookerName,
        'email' => $bookerEmail,
        // 'dob' => $dob,
        'phone' => $phone,
        'password' => bcrypt($randomPassword),
      ]);

      $user->assignRole(('user'));

      // Send new user credentials using a Mailable
      try {
        // Use queued mail when possible; falls back to send if queue driver is sync
        Mail::to($user->email)->queue(new \App\Mail\NewUserCredentials($user, $randomPassword));
        // Log that we attempted to queue the mail (helps debugging delivery problems)
        Log::info('Queued new user credentials mail', ['user_id' => $user->id, 'email' => $user->email]);
      } catch (Exception $e) {
        // log mail failures so we can debug why emails are not delivered
        Log::error('New user credentials mail failed: ' . $e->getMessage());
      }
    }

    // Create a pending booking record — payment will confirm it
    $booking = $event->bookings()->create([
      'booker_name' => $bookerName,
      'booker_email' => $bookerEmail,
      'phone' => $phone,
      'booked_at_date' => $request->validated('booked_at_date'),
      'booked_at_time' => $request->validated('booked_at_time'),
      'user_id' => $user->id,
      'status' => 'pending',
    ]);

    // Redirect to payment page instead of returning JSON
    return redirect()->route('payment.page', $booking->id);
  }

  /**
   * @param Booking $booking
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Booking $booking)
  {
    $this->authorize('delete', $booking);

    // ignore in testing as it interacts with google calendar api
    // Instead of deleting, mark the booking as cancelled so bookers can see the cancelled status
    if (!app()->runningUnitTests()) {
      try {
        // attempt to remove calendar event if present
        if ($booking->calendar_id) {
          $this->deleteGoogleEvent($booking->event, $booking);
        }
      } catch (Exception $ex) {
        Log::error('Failed to delete google event during cancel: ' . $ex->getMessage());
        return redirect()->back()->with([
          'alert_type' => 'error',
          'alert_message' => "Something happened please try again!"
        ]);
      }
    }

    $booking->status = 'cancelled';
    $booking->calendar_id = null;
    $booking->calendar_link = null;
    $booking->meet_link = null;
    $booking->save();

    // Queue notification to booker that booking was cancelled
    Notification::route('mail', [
      $booking->booker_email => $booking->booker_name,
    ])->notify(new BookingDeclinedNotification(
      $booking->event,
      $booking->booker_name,
      $booking->booked_at_date,
      $booking->booked_at_time
    ));

    return redirect()->back()->with([
      'alert_type' => 'success',
      'alert_message' => "Booking cancelled!"
    ]);
  }


  /**
   * @param Request $request
   * @param Booking $booking
   * @return \Illuminate\Http\RedirectResponse
   */
  public function reschedule(Request $request, Booking $booking)
  {
    $user = auth()->user();

    if ($booking->user_id !== $user->id) {
        abort(403);
    }



    // 🔹 Store old schedule
    $oldDate = $booking->booked_at_date;
    $oldTime = $booking->booked_at_time;

    $payload = $request->validate([
        'booked_at_date' => [
            'required',
            'date',
            function ($attribute, $value, $fail) use ($booking) {
                $event = $booking->event;
                if ($event && is_array($event->available_week_days)) {
                    $weekday = strtolower(Carbon::parse($value)->format('l'));
                    if (!in_array($weekday, $event->available_week_days)) {
                        $fail('Bookings are not allowed on ' . ucfirst($weekday));
                    }
                }
            },
        ],
        'booked_at_time' => 'required|date_format:H:i',
    ]);

    // delete old google event
    if ($booking->calendar_id && !app()->runningUnitTests()) {
        try {
            $this->deleteGoogleEvent($booking->event, $booking);
        } catch (Exception $ex) {
            Log::warning('Google delete failed: ' . $ex->getMessage());
        }
    }

    // update booking - only confirm if payment exists
    $booking->update([
        'booked_at_date' => $payload['booked_at_date'],
        'booked_at_time' => $payload['booked_at_time'],
        'status' => $booking->payment ? 'confirmed' : 'pending',
    ]);

    // recreate google event
    if (!app()->runningUnitTests()) {
        try {
            $calendarEvent = $this->createGoogleEvent(
                $booking->event,
                $booking->booked_at_date,
                $booking->booked_at_time,
                $booking->booker_name,
                $booking->booker_email
            );

            $booking->update([
                'calendar_id' => $calendarEvent['calendar_id'] ?? $booking->calendar_id,
                'calendar_link' => $calendarEvent['calendar_link'] ?? $booking->calendar_link,
                'meet_link' => $calendarEvent['meet_link'] ?? $booking->meet_link,
            ]);
        } catch (Exception $ex) {
            Log::warning('Google create failed: ' . $ex->getMessage());
        }
    }

    // 🔔 SEND RESCHEDULE EMAIL
    $notification = new BookingRescheduledNotification(
        $booking,
        $oldDate,
        $oldTime,
        $booking->booked_at_date,
        $booking->booked_at_time
    );

    // notify event owner
    $booking->event->user->notify($notification);

    // notify booker
    Notification::route('mail', [$booking->booker_email => $booking->booker_name])
        ->notify($notification);

    // Check if payment is required
    if (!$booking->payment && $booking->event && $booking->event->price > 0) {
        return redirect()->route('payment.page', $booking->id)->with([
            'alert_type' => 'info',
            'alert_message' => 'Booking rescheduled. Please complete payment to confirm.'
        ]);
    }

    return redirect()->route('user.bookings.index')->with([
        'alert_type' => 'success',
        'alert_message' => 'Booking rescheduled successfully.'
    ]);
  }


  /**
   * Add event to google calendar
   *
   * @param Event $event
   * @param string $booked_date
   * @param string $booked_time
   * @param string $booker_name
   * @param string $booker_email
   * @return array
   */
  public function createGoogleEvent(Event $event, $booked_date, $booked_time, $booker_name, $booker_email)
  {
    $client = new Client();

    if (Carbon::now()->greaterThan($event->user->google_auth_metadata['token_expiry'])) {
      $client->setClientId(config('services.google.client_id'));
      $client->setClientSecret(config('services.google.client_secret'));
      $newToken = $client->fetchAccessTokenWithRefreshToken($event->user->google_auth_metadata['refresh_token']);
      $event->user->setGoogleAuthMetadata(null, $newToken['access_token'], $newToken['refresh_token'], $newToken['expires_in']);
    }

    $client->setAccessToken($event->user->google_auth_metadata['token']);
    $service = new \Google\Service\Calendar($client);

    $parsed_booked_time = Carbon::parse($booked_time);
    $calendarEvent = new \Google\Service\Calendar\Event(array(
      'summary' => $event->title,
      'location' => 'Google Meet',
      // Limit visibility and guest permissions so attendees can't freely share/invite others
      // Note: This helps prevent guests from inviting others via the calendar UI,
      // but it cannot guarantee that someone with the raw Meet link cannot join.
      // True enforcement (only invited users allowed to join without request)
      // is controlled by Google Workspace account settings and cannot be overridden
      // per-event via the Calendar API for consumer accounts.
      'visibility' => 'private',
      'guestsCanInviteOthers' => false,
      'guestsCanSeeOtherGuests' => false,
      'guestsCanModify' => false,
      'start' => array(
        'dateTime' => Carbon::parse($booked_date)
          ->setTimeFrom($parsed_booked_time),
      ),
      'end' => array(
        'dateTime' => Carbon::parse($booked_date)
          ->setTimeFrom($parsed_booked_time)
          ->addMinutes($event->duration),
      ),
      'attendees' => [
        [
          'email' => $booker_email,
          'displayName' => $booker_name
        ],
        [
          'email' => $event->user->email,
          'displayName' => $event->user->name
        ]
      ],
      'reminders' => array(
        'useDefault' => FALSE,
        'overrides' => array(
          array('method' => 'email', 'minutes' => 60),
          array('method' => 'popup', 'minutes' => 10),
        ),
      ),
      'conferenceData' => [
        'createRequest' => [
          'conferenceSolutionKey' => [
            'type' => 'hangoutsMeet'
          ],
          'requestId' => Str::random(),
        ],
      ]
    ));

    $calendarId = 'primary';
    $calendarEvent = $service->events->insert($calendarId, $calendarEvent, [
      "conferenceDataVersion" => 1,
      'sendUpdates' => "all"
    ]);

    return [
      'calendar_id' => $calendarEvent->id,
      'calendar_link' => $calendarEvent->htmlLink,
      'meet_link' => $calendarEvent->hangoutLink
    ];
  }


  /**
   * Delete event from google calendar
   *
   * @param Event $event
   * @param Booking $booking
   * @return boolean
   */
  protected function deleteGoogleEvent(Event $event, Booking $booking)
  {
    $client = new Client();

    if (Carbon::now()->greaterThan($event->user->google_auth_metadata['token_expiry'])) {
      $client->setClientId(config('services.google.client_id'));
      $client->setClientSecret(config('services.google.client_secret'));
      $newToken = $client->fetchAccessTokenWithRefreshToken($event->user->google_auth_metadata['refresh_token']);
      $event->user->setGoogleAuthMetadata(null, $newToken['access_token'], $newToken['refresh_token'], $newToken['expires_in']);
    }

    $client->setAccessToken($event->user->google_auth_metadata['token']);
    $service = new \Google\Service\Calendar($client);
    return $service->events->delete('primary', $booking->calendar_id);
  }
}
