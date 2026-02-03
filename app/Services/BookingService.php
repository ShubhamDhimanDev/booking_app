<?php

namespace App\Services;

use App\Events\BookingCreated;
use App\Events\BookingCancelled;
use App\Events\BookingRescheduled;
use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use App\Models\FollowUpInvite;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

/**
 * BookingService - Handles all booking business logic
 *
 * Responsibilities:
 * - Booking creation with validation
 * - Availability checking
 * - Booking cancellation and refunds
 * - Rescheduling logic
 */
class BookingService
{
    protected GoogleCalendarService $calendarService;

    public function __construct(GoogleCalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Create a new booking with full validation
     *
     * @param Event $event
     * @param array $data
     * @return Booking
     * @throws Exception
     */
    public function createBooking(Event $event, array $data): Booking
    {
        // Validate availability
        $this->validateBookingAvailability($event, $data['booked_at_date'], $data['booked_at_time']);

        // Handle follow-up invite if provided
        $followUpInvite = null;
        if (!empty($data['followup_token'])) {
            $followUpInvite = $this->validateFollowUpToken($data['followup_token']);
        }

        // Get or create booker user
        $bookerUser = $this->getOrCreateBooker($data['booker_email'], $data['booker_name']);

        // Determine price (follow-up custom price or event price)
        $price = $followUpInvite ? $followUpInvite->custom_price : $event->price;

        // Create booking within transaction
        return DB::transaction(function () use ($event, $data, $bookerUser, $followUpInvite, $price) {
            $booking = $event->bookings()->create([
                'booker_name' => $data['booker_name'],
                'booker_email' => $data['booker_email'],
                'phone' => $data['phone'] ?? null,
                'booked_at_date' => $data['booked_at_date'],
                'booked_at_time' => $data['booked_at_time'],
                'user_id' => $bookerUser->id,
                'status' => $price == 0 ? 'confirmed' : 'pending',
                'is_followup' => $followUpInvite ? true : false,
                'followup_invite_id' => $followUpInvite?->id,
            ]);

            // Create tracking record
            $booking->tracking()->create([
                'utm_source' => session('tracking_utm_source'),
                'utm_medium' => session('tracking_utm_medium'),
                'utm_campaign' => session('tracking_utm_campaign'),
                'utm_content' => session('tracking_utm_content'),
                'utm_term' => session('tracking_utm_term'),
                'fbclid' => session('tracking_fbclid'),
                'gclid' => session('tracking_gclid'),
            ]);

            // If free booking, confirm immediately and dispatch event
            // For paid bookings, event will be dispatched after payment confirmation
            if ($price == 0) {
                event(new BookingCreated($booking));
            }

            return $booking;
        });
    }

    /**
     * Validate booking availability with comprehensive checks
     *
     * @param Event $event
     * @param string $date
     * @param string $time
     * @throws Exception
     */
    public function validateBookingAvailability(Event $event, string $date, string $time): void
    {
        $bookingDate = Carbon::parse($date);

        // 1. Check date range
        if ($event->available_from_date && $bookingDate->lt(Carbon::parse($event->available_from_date))) {
            throw new Exception('Selected date is before the event availability period.');
        }

        if ($event->available_to_date && $bookingDate->gt(Carbon::parse($event->available_to_date))) {
            throw new Exception('Selected date is after the event availability period.');
        }

        // 2. Check weekday
        if (!empty($event->available_week_days) && is_array($event->available_week_days)) {
            $dayOfWeek = strtolower($bookingDate->format('l'));
            if (!in_array($dayOfWeek, $event->available_week_days)) {
                throw new Exception('Selected day of week is not available for this event.');
            }
        }

        // 3. Check timeslot exists
        $event->append('timeslots');
        $slotFound = collect($event->timeslots)->contains(fn($slot) => $slot['start'] === $time);

        if (!$slotFound) {
            throw new Exception('Selected time slot is not available.');
        }

        // 4. Check exclusions
        $exclusion = $event->exclusions()->whereDate('date', $date)->first();
        if ($exclusion) {
            if ($exclusion->exclude_all ||
                (!empty($exclusion->times) && in_array($time, $exclusion->times))) {
                throw new Exception('Selected date/time is excluded.');
            }
        }

        // 5. Check slot not already booked
        $exists = $event->bookings()
            ->where('booked_at_date', $date)
            ->where('booked_at_time', $time)
            ->confirmed()
            ->exists();

        if ($exists) {
            throw new Exception('This time slot is already booked.');
        }

        // 6. Check organizer availability across all their events
        $ownerHasBooking = Booking::whereHas('event', fn($q) => $q->where('user_id', $event->user_id))
            ->where('booked_at_date', $date)
            ->where('booked_at_time', $time)
            ->confirmed()
            ->exists();

        if ($ownerHasBooking) {
            throw new Exception('The event owner is not available at this time.');
        }
    }

    /**
     * Get or create booker user
     *
     * @param string $email
     * @param string $name
     * @return User
     */
    protected function getOrCreateBooker(string $email, string $name): User
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $randomPassword = Str::random(12);
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'username' => Str::slug(explode('@', $email)[0]) . '-' . Str::random(4),
                'password' => bcrypt($randomPassword),
            ]);

            $user->assignRole('user');

            // Queue new user credentials email
            \App\Jobs\SendNewUserCredentials::dispatch($user, $randomPassword);
        }

        return $user;
    }

    /**
     * Validate follow-up invite token
     *
     * @param string $token
     * @return FollowUpInvite|null
     * @throws Exception
     */
    protected function validateFollowUpToken(string $token): ?FollowUpInvite
    {
        $invite = FollowUpInvite::where('token', $token)
            ->pending()
            ->with('event', 'booking')
            ->first();

        if (!$invite) {
            throw new Exception('Invalid follow-up invite token.');
        }

        if (!$invite->isValid()) {
            throw new Exception('Follow-up invite has expired.');
        }

        return $invite;
    }

    /**
     * Cancel booking and process refund
     *
     * @param Booking $booking
     * @param string $reason
     * @param int $cancelledBy
     * @param bool $force
     * @return array
     * @throws Exception
     */
    public function cancelBooking(
        Booking $booking,
        string $reason,
        int $cancelledBy,
        bool $force = false
    ): array {
        if (!$force && !$booking->canCancel()) {
            throw new Exception('This booking cannot be cancelled according to the refund policy.');
        }

        // Calculate refund before cancelling
        $refundDetails = $booking->getRefundAmount();

        // Cancel within transaction
        return DB::transaction(function () use ($booking, $reason, $cancelledBy, $refundDetails) {
            $booking->cancel($reason, $cancelledBy);

            // Queue refund processing if applicable
            if ($booking->payment && $refundDetails['amount'] > 0) {
                \App\Jobs\ProcessRefundJob::dispatch($booking, $refundDetails);
            }

            // Dispatch cancellation event
            event(new BookingCancelled($booking, $refundDetails));

            return [
                'success' => true,
                'refund_amount' => $refundDetails['amount'],
                'refund_percentage' => $refundDetails['percentage'],
            ];
        });
    }

    /**
     * Reschedule booking to new date/time
     *
     * @param Booking $booking
     * @param string $newDate
     * @param string $newTime
     * @return Booking
     * @throws Exception
     */
    public function rescheduleBooking(Booking $booking, string $newDate, string $newTime): Booking
    {
        // Validate new slot availability
        $this->validateBookingAvailability($booking->event, $newDate, $newTime);

        $oldDate = $booking->booked_at_date;
        $oldTime = $booking->booked_at_time;

        return DB::transaction(function () use ($booking, $newDate, $newTime, $oldDate, $oldTime) {
            // Update booking
            $booking->update([
                'booked_at_date' => $newDate,
                'booked_at_time' => $newTime,
                'status' => $booking->payment ? 'confirmed' : 'pending',
            ]);

            // Dispatch rescheduled event
            event(new BookingRescheduled($booking, $oldDate, $oldTime));

            return $booking->fresh();
        });
    }

    /**
     * Get available slots for an event
     * Uses caching for performance
     *
     * @param Event $event
     * @return array
     */
    public function getAvailableSlots(Event $event): array
    {
        // Cache key based on event and last booking
        $cacheKey = "event_slots_{$event->id}_" . $event->bookings()->max('updated_at');

        return cache()->remember($cacheKey, now()->addMinutes(5), function () use ($event) {
            $event->append('timeslots');

            // Get all confirmed bookings for this event
            $bookedSlots = $event->bookings()
                ->confirmed()
                ->get()
                ->groupBy('booked_at_date')
                ->map(fn($slots) => $slots->pluck('booked_at_time')->toArray())
                ->toArray();

            // Get organizer's bookings from other events
            $ownerBookings = Booking::whereHas('event', function($q) use ($event) {
                    $q->where('user_id', $event->user_id)->where('id', '!=', $event->id);
                })
                ->confirmed()
                ->get(['booked_at_date', 'booked_at_time'])
                ->groupBy('booked_at_date')
                ->map(fn($slots) => $slots->pluck('booked_at_time')->toArray())
                ->toArray();

            // Merge booked slots
            foreach ($ownerBookings as $date => $times) {
                $bookedSlots[$date] = array_unique(array_merge($bookedSlots[$date] ?? [], $times));
            }

            // Build available slots
            $startDate = Carbon::parse($event->available_from_date);
            $endDate = Carbon::parse($event->available_to_date);
            $availableSlots = [];

            for ($date = $startDate->copy(); $date->lessThanOrEqualTo($endDate); $date->addDay()) {
                $dateStr = $date->toDateString();
                $freeSlots = [];

                foreach ($event->timeslots as $slot) {
                    $isBooked = isset($bookedSlots[$dateStr]) && in_array($slot['start'], $bookedSlots[$dateStr]);
                    if (!$isBooked) {
                        $freeSlots[] = $slot;
                    }
                }

                // Sort by time
                usort($freeSlots, fn($a, $b) => strtotime($a['start']) <=> strtotime($b['start']));

                if (count($freeSlots) > 0) {
                    $availableSlots[] = [
                        'date' => $dateStr,
                        'timeslots' => $freeSlots,
                    ];
                }
            }

            return [
                'availableSlots' => $availableSlots,
                'bookedSlots' => $bookedSlots,
            ];
        });
    }

    /**
     * Clear availability cache for event
     *
     * @param Event $event
     */
    public function clearAvailabilityCache(Event $event): void
    {
        cache()->forget("event_slots_{$event->id}_*");
    }
}
