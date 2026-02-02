<?php

namespace App\Http\Controllers;

use Exception;
use Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Event;
use App\Models\Booking;
use App\Models\BookingTracking;
use App\Models\FollowUpInvite;
use App\Http\Requests\StoreBookingRequest;
use App\Notifications\BookingDeclinedNotification;
use App\Notifications\FollowUpInviteNotification;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Services\BookingService;

/**
 * BookingController - Thin HTTP layer
 *
 * Delegates all business logic to BookingService
 * Handles only HTTP concerns: requests, responses, authorization
 */
class BookingController extends Controller
{
  protected BookingService $bookingService;

  public function __construct(BookingService $bookingService)
  {
    $this->bookingService = $bookingService;
  }
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

    $bookings = $query->orderBy('created_at', 'DESC')->paginate(10);

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
      switch ($request->status) {
        case Booking::STATUS_CONFIRMED:
          $query->confirmed();
          break;
        case Booking::STATUS_PENDING:
          $query->pending();
          break;
        case Booking::STATUS_CANCELLED:
          $query->cancelled();
          break;
        default:
          $query->where('status', $request->status);
      }
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
    $confirmedCount = Booking::forUser($user->id)->confirmed()->count();
    $pendingCount = Booking::forUser($user->id)->pending()->count();
    $cancelledCount = Booking::forUser($user->id)->cancelled()->count();

    return view('user.bookings.index', compact('bookings', 'totalCount', 'confirmedCount', 'pendingCount', 'cancelledCount'));
    }

    /**
     * Show reschedule form
     *
     * @param Booking $booking
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showRescheduleForm(Booking $booking)
    {
    $user = auth()->user();

    if ($booking->user_id !== $user->id) {
      abort(403);
    }

    // Check if booking has expired
    $bookingDateTime = Carbon::parse($booking->booked_at_date . ' ' . $booking->booked_at_time);
    if ($bookingDateTime->isPast()) {
      return back()->with([
        'alert_type' => 'error',
        'alert_message' => 'Cannot reschedule a past booking.'
      ]);
    }

    $booking->load(['event', 'payment']);

    // Get available slots using service (cached)
    $slots = $this->bookingService->getAvailableSlots($booking->event);

    return view('user.bookings.reschedule', [
      'booking' => $booking,
      'availableSlots' => $slots['availableSlots'],
      'bookedSlots' => $slots['bookedSlots'],
    ]);
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
    if (!Carbon::hasFormat($date, 'Y-m-d') || !Carbon::hasFormat($time, 'H:i')) {
      return redirect()->route('events.show.public', $event->slug)
        ->withErrors(['error' => 'Invalid date or time format.']);
    }

    return view('bookings.details', compact('event', 'date', 'time'));
  }

  /**
   * Store new booking
   *
   * @param StoreBookingRequest $request
   * @param Event $event
   * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
   */
  public function store(StoreBookingRequest $request, Event $event)
  {
    try {
      $booking = $this->bookingService->createBooking($event, $request->validated());

      // Determine price
      $price = $booking->is_followup && $booking->followUpInvite
        ? $booking->followUpInvite->custom_price
        : $event->price;

      // Free booking - already confirmed, redirect to thank you
      if ($price == 0) {
        return redirect()->route('payment.thankyou', $booking->id);
      }

      // Save custom price for follow-up in session
      if ($booking->is_followup) {
        session(['followup_custom_price' => $price]);
      }

      // Redirect to payment
      return redirect()->route('payment.page', $booking->id);

    } catch (Exception $e) {
      Log::error('Booking creation failed', [
        'event_id' => $event->id,
        'error' => $e->getMessage()
      ]);

      return response()->json(['error' => $e->getMessage()], 409);
    }
  }

  /**
   * Admin decline booking
   *
   * @param Booking $booking
   * @return \Illuminate\Http\RedirectResponse
   */
  public function destroy(Booking $booking)
  {
    $this->authorize('delete', $booking);

    try {
      /** @var \App\Models\User */
      $admin = auth()->user();

      $this->bookingService->cancelBooking(
        $booking,
        'Declined by admin',
        $admin->id,
        true // force
      );

      return redirect()->back()->with([
        'alert_type' => 'success',
        'alert_message' => 'Booking cancelled!'
      ]);

    } catch (Exception $e) {
      return redirect()->back()->with([
        'alert_type' => 'error',
        'alert_message' => 'Failed to cancel booking: ' . $e->getMessage()
      ]);
    }
  }


  /**
   * Reschedule booking
   *
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

    $validated = $request->validate([
      'booked_at_date' => 'required|date|after_or_equal:today',
      'booked_at_time' => 'required|date_format:H:i',
    ]);

    try {
      $this->bookingService->rescheduleBooking(
        $booking,
        $validated['booked_at_date'],
        $validated['booked_at_time']
      );

      // Check if payment required
      if (!$booking->payment && $booking->event && $booking->event->price > 0) {
        return redirect()->route('payment.page', $booking->id);
      }

      return redirect()->route('user.bookings.index')->with([
        'alert_type' => 'success',
        'alert_message' => 'Booking rescheduled successfully.'
      ]);

    } catch (Exception $e) {
      return back()->with([
        'alert_type' => 'error',
        'alert_message' => $e->getMessage()
      ]);
    }
  }


  // ========================================================================
  // DEPRECATED METHODS - Kept for backward compatibility only
  // These methods have been replaced by queue jobs dispatched via events
  // ========================================================================

  /**
   * @deprecated Use CreateCalendarEvent job dispatched via BookingCreated event
   *
   * Add event to google calendar
   * Kept for backward compatibility only
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
    $googleCalendar = app(\App\Services\GoogleCalendarService::class);
    $service = $googleCalendar->getCalendarService($event->user);

    $parsed_booked_time = Carbon::parse($booked_time);
    $calendarEvent = new \Google\Service\Calendar\Event([
      'summary' => $event->title,
      'location' => 'Google Meet',
      'visibility' => 'private',
      'guestsCanInviteOthers' => false,
      'guestsCanSeeOtherGuests' => false,
      'guestsCanModify' => false,
      'start' => [
        'dateTime' => Carbon::parse($booked_date)->setTimeFrom($parsed_booked_time),
      ],
      'end' => [
        'dateTime' => Carbon::parse($booked_date)
          ->setTimeFrom($parsed_booked_time)
          ->addMinutes($event->duration),
      ],
      'attendees' => [
        ['email' => $booker_email, 'displayName' => $booker_name],
        ['email' => $event->user->email, 'displayName' => $event->user->name]
      ],
      'reminders' => [
        'useDefault' => false,
        'overrides' => [
          ['method' => 'email', 'minutes' => 60],
          ['method' => 'popup', 'minutes' => 10],
        ],
      ],
      'conferenceData' => [
        'createRequest' => [
          'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
          'requestId' => \Illuminate\Support\Str::random(),
        ],
      ]
    ]);

    $calendarEvent = $service->events->insert('primary', $calendarEvent, [
      'conferenceDataVersion' => 1,
      'sendUpdates' => 'all'
    ]);

    return [
      'calendar_id' => $calendarEvent->id,
      'calendar_link' => $calendarEvent->htmlLink,
      'meet_link' => $calendarEvent->hangoutLink
    ];
  }

  /**
   * @deprecated Use DeleteCalendarEvent job dispatched via BookingCancelled event
   *
   * Delete event from google calendar
   * Kept for backward compatibility only
   *
   * @param Event $event
   * @param Booking $booking
   * @return boolean
   */
  protected function deleteGoogleEvent(Event $event, Booking $booking)
  {
    $googleCalendar = app(\App\Services\GoogleCalendarService::class);
    return $googleCalendar->deleteEvent($event->user, $booking->calendar_id);
  }

  /**
   * Cancel booking (user-initiated)
   *
   * @param Request $request
   * @param Booking $booking
   * @return \Illuminate\Http\RedirectResponse
   */
  public function cancelBooking(Request $request, Booking $booking)
  {
    /** @var \App\Models\User */
    $user = auth()->user();

    if ($booking->user_id !== $user->id) {
      abort(403);
    }

    $request->validate(['reason' => 'required|string|max:500']);

    try {
      $result = $this->bookingService->cancelBooking(
        $booking,
        $request->reason,
        $user->id
      );

      $message = $result['refund_amount'] > 0
        ? "Booking cancelled successfully. Your refund of ₹" . number_format($result['refund_amount'], 2)
          . " ({$result['refund_percentage']}%) is being processed."
        : 'Booking cancelled successfully.';

      return back()->with([
        'alert_type' => 'success',
        'alert_message' => $message,
      ]);

    } catch (Exception $e) {
      return back()->with([
        'alert_type' => 'error',
        'alert_message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Admin cancel booking
   *
   * @param Request $request
   * @param Booking $booking
   * @return \Illuminate\Http\RedirectResponse
   */
  public function adminCancelBooking(Request $request, Booking $booking)
  {
    /** @var \App\Models\User */
    $admin = auth()->user();

    $booking->load('event', 'payment', 'booker');

    $request->validate([
      'reason' => 'required|string|max:500',
      'force' => 'nullable|boolean',
    ]);

    $force = $request->input('force', false);

    try {
      $result = $this->bookingService->cancelBooking(
        $booking,
        $request->reason,
        $admin->id,
        $force
      );

      $message = $result['refund_amount'] > 0
        ? "Booking cancelled by admin. Refund of ₹" . number_format($result['refund_amount'], 2) . " is being processed."
        : 'Booking cancelled by admin.';

      return back()->with([
        'alert_type' => 'success',
        'alert_message' => $message,
      ]);

    } catch (Exception $e) {
      return back()->with([
        'alert_type' => 'error',
        'alert_message' => $e->getMessage(),
      ]);
    }
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
    // if (!$booking->isCompleted()) {
    //   return back()->with([
    //     'alert_type' => 'error',
    //     'alert_message' => 'Follow-up invites can only be sent for completed sessions.',
    //   ]);
    // }

    // Validate request
    $request->validate([
      'custom_price' => 'required|numeric|min:0',
      'expires_days' => 'nullable|integer|min:1|max:90',
    ]);

    // Check if invite already exists and is pending
    $existingInvite = FollowUpInvite::where('booking_id', $booking->id)
      ->pending()
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
      'is_normal_invite' => $request->boolean('is_normal_invite', false),
      'token' => FollowUpInvite::generateUniqueToken(),
      'status' => FollowUpInvite::STATUS_PENDING,
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
   * Show follow-up booking page
   *
   * @param string $token
   * @return \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
   */
  public function showFollowUpBooking($token)
  {
    $invite = FollowUpInvite::where('token', $token)
      ->with(['event', 'booking'])
      ->first();

    if (!$invite) {
      abort(404, 'Follow-up invite not found.');
    }

    if (!$invite->isValid()) {
      return view('bookings.followup-expired', [
        'event' => $invite->event,
        'reason' => $invite->status === 'accepted' ? 'already_used' : 'expired'
      ]);
    }

    $event = $invite->event;
    $customPrice = $invite->custom_price;
    $isFollowUp = true;

    // Get available slots using service (cached)
    $slots = $this->bookingService->getAvailableSlots($event);

    return view('bookings.slot-selection', [
      'event' => $event,
      'customPrice' => $customPrice,
      'isFollowUp' => $isFollowUp,
      'invite' => $invite,
      'availableSlots' => $slots['availableSlots'],
      'bookedSlots' => $slots['bookedSlots'],
    ]);
  }
}

