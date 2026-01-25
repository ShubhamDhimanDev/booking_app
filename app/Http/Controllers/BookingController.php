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
use App\Models\BookingTracking;
use App\Models\FollowUpInvite;
use App\Http\Requests\StoreBookingRequest;
use App\Models\User;
use App\Notifications\BookingDeclinedNotification;
use App\Notifications\BookingRescheduledNotification;
use App\Notifications\FollowUpInviteNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Services\PaymentGatewayManager;
use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Jobs\ProcessRefundJob;

class BookingController extends Controller
{
  /**
   * Shows all bookings
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
   */
  public function index(Request $request)
  {
    /** @var \App\Models\User */
    $user = auth()->user();

    $query = Booking::whereHas('event', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })
    ->with(['event', 'booker', 'tracking'])
    ->where('user_id', '!=', NULL);

    // Apply UTM filters if provided (using relationship)
    if ($request->filled('utm_source')) {
      $query->whereHas('tracking', function ($q) use ($request) {
        $q->where('utm_source', $request->utm_source);
      });
    }
    if ($request->filled('utm_campaign')) {
      $query->whereHas('tracking', function ($q) use ($request) {
        $q->where('utm_campaign', $request->utm_campaign);
      });
    }
    if ($request->filled('utm_medium')) {
      $query->whereHas('tracking', function ($q) use ($request) {
        $q->where('utm_medium', $request->utm_medium);
      });
    }

    $bookings = $query->orderBy('booked_at_date', 'asc')->paginate(10);

    // Get unique values for filter dropdowns from tracking table
    $utmSources = BookingTracking::whereHas('booking.event', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })
    ->whereNotNull('utm_source')
    ->distinct()
    ->pluck('utm_source');

    $utmCampaigns = BookingTracking::whereHas('booking.event', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })
    ->whereNotNull('utm_campaign')
    ->distinct()
    ->pluck('utm_campaign');

    $utmMediums = BookingTracking::whereHas('booking.event', function ($q) use ($user) {
        $q->where('user_id', $user->id);
    })
    ->whereNotNull('utm_medium')
    ->distinct()
    ->pluck('utm_medium');

    return view('admin.bookings.index', compact('bookings', 'utmSources', 'utmCampaigns', 'utmMediums'));
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
    // Check if this is a follow-up booking
    $followUpToken = $request->input('followup_token');
    $followUpInvite = null;

    if ($followUpToken) {
      $followUpInvite = FollowUpInvite::where('token', $followUpToken)->first();

      if (!$followUpInvite || !$followUpInvite->isValid()) {
        return response()->json(['error' => 'Invalid or expired follow-up invitation.'], 422);
      }
    }

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
      'is_followup' => $followUpInvite ? true : false,
      'followup_invite_id' => $followUpInvite ? $followUpInvite->id : null,
    ]);

    // Create tracking record with UTM parameters from session
    $booking->tracking()->create([
      'utm_source' => session('tracking_utm_source'),
      'utm_medium' => session('tracking_utm_medium'),
      'utm_campaign' => session('tracking_utm_campaign'),
      'utm_content' => session('tracking_utm_content'),
      'utm_term' => session('tracking_utm_term'),
      'fbclid' => session('tracking_fbclid'),
      'gclid' => session('tracking_gclid'),
    ]);

    // Determine the price (custom price for follow-up, event price otherwise)
    $price = $followUpInvite ? $followUpInvite->custom_price : $event->price;

    // If the session is free (price = 0), confirm booking immediately without payment
    if ($price == 0) {
      try {
        // Mark follow-up invite as accepted if this is a follow-up booking
        if ($followUpInvite) {
          $followUpInvite->update(['status' => 'accepted']);
        }

        // Confirm the booking
        $booking->update(['status' => 'confirmed']);

        // Create Google Calendar event
        if (!app()->runningUnitTests()) {
          $calendarEvent = $this->createGoogleEvent(
            $event,
            $booking->booked_at_date,
            $booking->booked_at_time,
            $booking->booker_name,
            $booking->booker_email
          );

          $booking->update([
            'calendar_id' => $calendarEvent['calendar_id'] ?? null,
            'calendar_link' => $calendarEvent['calendar_link'] ?? null,
            'meet_link' => $calendarEvent['meet_link'] ?? null,
          ]);
        }

        // Refresh booking with relationships for notification
        $booking->refresh();
        $booking->load(['event.user']);

        // Notify owner and booker
        $event->user->notify(new \App\Notifications\BookingCreatedNotification($booking));
        Notification::route('mail', [$booking->booker_email => $booking->booker_name])
          ->notify(new \App\Notifications\BookingCreatedNotification($booking));

        // Redirect to thank you page
        return redirect()->route('payment.thankyou', $booking->id);

      } catch (Exception $e) {
        Log::error('Free booking confirmation failed: ' . $e->getMessage(), [
          'booking_id' => $booking->id,
          'exception' => $e
        ]);

        return redirect()->back()->with([
          'alert_type' => 'error',
          'alert_message' => 'Failed to confirm booking. Please try again.',
        ]);
      }
    }

    // If follow-up, pass custom price to payment page
    if ($followUpInvite) {
      session(['followup_custom_price' => $followUpInvite->custom_price]);
    }

    // Redirect to payment page for paid sessions
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

  /**
   * Cancel a booking (user-initiated)
   *
   * @param Request $request
   * @param Booking $booking
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
   */
  public function cancelBooking(Request $request, Booking $booking)
  {
    /** @var \App\Models\User */
    $user = auth()->user();

    // Authorize: only the booking owner can cancel
    if ($booking->user_id !== $user->id) {
      abort(403, 'Unauthorized action.');
    }

    // Load event relationship
    $booking->load('event', 'payment');

    // Check if booking can be cancelled
    if (!$booking->canCancel()) {
      return back()->with([
        'alert_type' => 'error',
        'alert_message' => 'This booking cannot be cancelled. Either refunds are not enabled, the booking is already cancelled, or the minimum cancellation notice has passed.',
      ]);
    }

    // Validate cancellation reason
    $request->validate([
      'reason' => 'required|string|max:500',
    ]);

    // Calculate refund amount BEFORE cancelling (status changes from confirmed to declined)
    $refundDetails = $booking->getRefundAmount();

    // Cancel the booking
    $success = $booking->cancel($request->reason, $user->id);

    if (!$success) {
      return back()->with([
        'alert_type' => 'error',
        'alert_message' => 'Failed to cancel booking. Please try again.',
      ]);
    }

    // Reload payment relationship (cancel() method may have refreshed the model)
    $booking->load('payment');

    // Create refund record if there's a payment and refund is applicable
    if ($booking->payment && $refundDetails['amount'] > 0) {

      $refund = Refund::create([
        'booking_id' => $booking->id,
        'payment_id' => $booking->payment->id,
        'amount' => $refundDetails['amount'],
        'gateway_charges' => 0, // Will be calculated by job
        'net_refund_amount' => $refundDetails['amount'],
        'status' => 'pending',
        'gateway' => $booking->payment->provider,
        'initiated_by' => 'user',
        'initiated_by_user_id' => $user->id,
      ]);

      // Dispatch refund processing job
      ProcessRefundJob::dispatch($refund);

      $message = "Booking cancelled successfully. Your refund of ₹" . number_format($refundDetails['amount'], 2) . " ({$refundDetails['percentage']}%) is being processed and will be credited within 5-7 business days.";
    } else {
      $message = "Booking cancelled successfully.";
    }

    // Delete Google Calendar event if exists
    if ($booking->calendar_id && $booking->event && $booking->event->user) {
      try {
        $this->deleteGoogleEvent($booking->event, $booking);
      } catch (Exception $e) {
        Log::error('Failed to delete Google Calendar event', [
          'booking_id' => $booking->id,
          'error' => $e->getMessage(),
        ]);
      }
    }

    return back()->with([
      'alert_type' => 'success',
      'alert_message' => $message,
    ]);
  }

  /**
   * Admin cancel booking (for admin use)
   *
   * @param Request $request
   * @param Booking $booking
   * @return \Illuminate\Http\RedirectResponse
   */
  public function adminCancelBooking(Request $request, Booking $booking)
  {
    /** @var \App\Models\User */
    $admin = auth()->user();

    // Load event relationship
    $booking->load('event', 'payment', 'booker');

    // Validate cancellation reason
    $request->validate([
      'reason' => 'required|string|max:500',
      'force' => 'nullable|boolean', // Allow admin to force cancel even if canCancel() returns false
    ]);

    $force = $request->input('force', false);

    // Check if booking can be cancelled (admin can override)
    if (!$force && !$booking->canCancel()) {
      return back()->with([
        'alert_type' => 'warning',
        'alert_message' => 'This booking does not meet the standard cancellation criteria. Use "Force Cancel" to proceed.',
      ]);
    }

    // Calculate refund amount BEFORE cancelling (admin can issue full refund regardless of policy)
    $refundDetails = $booking->getRefundAmount();

    // Cancel the booking
    if ($force || $booking->canCancel()) {
      // Directly update if forcing
      $booking->update([
        'status' => 'declined',
        'cancelled_at' => now(),
        'cancelled_by' => $admin->id,
        'cancellation_reason' => $request->reason,
        'refund_status' => 'pending',
      ]);
    } else {
      $success = $booking->cancel($request->reason, $admin->id);
      if (!$success) {
        return back()->with([
          'alert_type' => 'error',
          'alert_message' => 'Failed to cancel booking.',
        ]);
      }
    }
    $refundAmount = $force && $request->has('refund_percentage')
      ? ($booking->payment->amount * $request->refund_percentage) / 100
      : $refundDetails['amount'];

    // Create refund record if there's a payment and refund is requested
    if ($booking->payment && $refundAmount > 0) {
      $refund = Refund::create([
        'booking_id' => $booking->id,
        'payment_id' => $booking->payment->id,
        'amount' => $refundAmount,
        'gateway_charges' => 0,
        'net_refund_amount' => $refundAmount,
        'status' => 'pending',
        'gateway' => $booking->payment->provider,
        'initiated_by' => 'admin',
        'initiated_by_user_id' => $admin->id,
      ]);

      // Dispatch refund processing job
      ProcessRefundJob::dispatch($refund);

      $message = "Booking cancelled by admin. Refund of ₹" . number_format($refundAmount, 2) . " is being processed.";
    } else {
      $message = "Booking cancelled by admin.";
    }

    // Notify booker
    if ($booking->booker) {
      $booking->booker->notify(new BookingDeclinedNotification($booking));
    }

    // Delete Google Calendar event
    if ($booking->calendar_id && $booking->event && $booking->event->user) {
      try {
        $this->deleteGoogleEvent($booking->event, $booking);
      } catch (Exception $e) {
        Log::error('Admin: Failed to delete Google Calendar event', [
          'booking_id' => $booking->id,
          'error' => $e->getMessage(),
        ]);
      }
    }

    return back()->with([
      'alert_type' => 'success',
      'alert_message' => $message,
    ]);
  }

  /**
   * Send a follow-up session invite to the booker
   *
   * @param Request $request
   * @param Booking $booking
   * @return \Illuminate\Http\RedirectResponse
   */
  public function sendFollowUpInvite(Request $request, Booking $booking)
  {
    // Validate that booking is completed
    if (!$booking->isCompleted()) {
      return back()->with([
        'alert_type' => 'error',
        'alert_message' => 'Follow-up invites can only be sent for completed sessions.',
      ]);
    }

    // Validate request
    $request->validate([
      'custom_price' => 'required|numeric|min:0',
      'expires_days' => 'nullable|integer|min:1|max:90',
    ]);

    // Check if invite already exists and is pending
    $existingInvite = FollowUpInvite::where('booking_id', $booking->id)
      ->where('status', 'pending')
      ->first();

    if ($existingInvite && $existingInvite->isValid()) {
      return back()->with([
        'alert_type' => 'warning',
        'alert_message' => 'A follow-up invite has already been sent for this booking and is still valid.',
      ]);
    }

    // Create follow-up invite
    $expiresAt = $request->expires_days
      ? now()->addDays($request->expires_days)
      : now()->addDays(30); // Default 30 days

    $invite = FollowUpInvite::create([
      'booking_id' => $booking->id,
      'event_id' => $booking->event_id,
      'user_id' => $booking->user_id ?? null,
      'custom_price' => $request->custom_price,
      'token' => FollowUpInvite::generateUniqueToken(),
      'status' => 'pending',
      'expires_at' => $expiresAt,
      'sent_at' => now(),
    ]);

    // Send notification
    try {
      if ($booking->booker) {
        $booking->booker->notify(new FollowUpInviteNotification($invite));
      } else {
        // Send to email if no user account
        Notification::route('mail', $booking->booker_email)
          ->notify(new FollowUpInviteNotification($invite));
      }

      return back()->with([
        'alert_type' => 'success',
        'alert_message' => 'Follow-up invitation sent successfully!',
      ]);
    } catch (Exception $e) {
      Log::error('Failed to send follow-up invite', [
        'booking_id' => $booking->id,
        'error' => $e->getMessage(),
      ]);

      return back()->with([
        'alert_type' => 'error',
        'alert_message' => 'Failed to send follow-up invitation. Please try again.',
      ]);
    }
  }

  /**
   * Show the follow-up booking page
   *
   * @param string $token
   * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
   */
  public function showFollowUpBooking($token)
  {
    $invite = FollowUpInvite::where('token', $token)
      ->with(['event', 'booking', 'user'])
      ->first();

    if (!$invite) {
      abort(404, 'Follow-up invitation not found.');
    }

    if (!$invite->isValid()) {
      $message = $invite->status === 'accepted'
        ? 'This follow-up invitation has already been used.'
        : 'This follow-up invitation has expired.';

      return view('bookings.followup-expired', compact('message'));
    }

    // Pass the event and invite details to the booking view
    $event = $invite->event;
    $customPrice = $invite->custom_price;
    $isFollowUp = true;

    // Build available slots for the event
    $event->append('timeslots');

    // Get confirmed bookings to exclude from available slots
    $confirmedBookings = $event->bookings()
      ->where('status', 'confirmed')
      ->get()
      ->groupBy('booked_at_date');

    // Build bookedSlots mapping date => [times]
    $bookedSlots = [];
    foreach ($confirmedBookings as $date => $collection) {
      $bookedSlots[$date] = $collection->pluck('booked_at_time')->values()->all();
    }

    $startDate = Carbon::parse($event->available_from_date);
    $endDate = Carbon::parse($event->available_to_date);

    $availableSlots = [];

    for ($date = $startDate->copy(); $date->lessThanOrEqualTo($endDate); $date->addDay()) {
      $dateStr = $date->toDateString();

      // Skip dates that are not allowed by event available_week_days
      if (!empty($event->available_week_days) && is_array($event->available_week_days)) {
        $weekday = strtolower($date->format('l'));
        if (!in_array($weekday, $event->available_week_days)) {
          continue;
        }
      }

      // Check for event exclusions
      $exclusion = $event->exclusions()->whereDate('date', $dateStr)->first();
      if ($exclusion && $exclusion->exclude_all) {
        continue;
      }

      $free = [];

      foreach ($event->timeslots as $ts) {
        $startTime = $ts['start'];

        // Check if time is in exclusion
        if ($exclusion && is_array($exclusion->times) && in_array($startTime, $exclusion->times)) {
          continue;
        }

        // Check if time is booked
        $isBooked = isset($bookedSlots[$dateStr]) && in_array($startTime, $bookedSlots[$dateStr]);

        if (!$isBooked) {
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

    return view('bookings.slot-selection', compact('event', 'customPrice', 'isFollowUp', 'invite', 'availableSlots', 'bookedSlots'));
  }
}

