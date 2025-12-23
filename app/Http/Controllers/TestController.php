<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingReminderLog;
use App\Models\EventReminder;
use App\Notifications\BookingReminderNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class TestController extends Controller
{
  public function index()
  {
    $now = Carbon::now();

    // Determine the maximum configured offset (minutes) so we know how far ahead to look for bookings.
    // Use a default horizon of 120 minutes (2 hours) so events without reminders still receive the legacy reminder.
    $configuredMax = (int) EventReminder::query()->max('offset_minutes');
    $maxOffset = max(120, $configuredMax);

    // We'll consider bookings up to $maxOffset minutes into the future; compute an end date window
    $daysAhead = (int) ceil($maxOffset / 1440);
    $endDate = Carbon::today()->copy()->addDays(max(1, $daysAhead))->toDateString();

    // Candidate bookings: confirmed/pending bookings between today and endDate
    $candidates = Booking::whereIn('status', ['confirmed', 'pending'])
      ->whereBetween('booked_at_date', [Carbon::today()->toDateString(), $endDate])
      ->get();

    Log::info('BookingReminderJob running (combined)', ['candidates' => $candidates->count(), 'maxOffset' => $maxOffset]);

    // small tolerance window (scheduler should run each minute) — we'll trigger reminders whose scheduled time
    // falls between now and now + 59 seconds
    $windowStart = $now->copy();
    $windowEnd = $now->copy()->addSeconds(59);

    // Legacy window used when an event has no configured reminders
    $legacyWindowStart = $now->copy()->addHour();
    $legacyWindowEnd = $now->copy()->addHours(2);

    foreach ($candidates as $booking) {
      try {
        if (! $booking->event) continue;

        // booking datetime (server timezone)
        $bookingDateTime = Carbon::createFromFormat('Y-m-d H:i', $booking->booked_at_date . ' ' . $booking->booked_at_time);

        $reminders = $booking->event->reminders()->where('enabled', true)->get();

        if ($reminders->isNotEmpty()) {
          // process per-event configured reminders
          foreach ($reminders as $reminder) {
            $reminderTime = $bookingDateTime->copy()->subMinutes((int) $reminder->offset_minutes);
            dd($reminderTime->toDateTimeString(), $windowStart->toDateTimeString(), $windowEnd->toDateTimeString());
            if ($reminderTime->between($windowStart, $windowEnd)) {
              // dedupe by event_reminder id
              $reminderKey = 'event_reminder:' . $reminder->id;
              $exists = BookingReminderLog::where('booking_id', $booking->id)->where('reminder_key', $reminderKey)->exists();
              if ($exists) {
                Log::info('Skipping already-sent reminder', ['booking_id' => $booking->id, 'key' => $reminderKey]);
                continue;
              }

              // send notifications
              Notification::route('mail', [$booking->booker_email => $booking->booker_name])->notify(new BookingReminderNotification($booking));
              if ($booking->event && $booking->event->user) {
                $booking->event->user->notify(new BookingReminderNotification($booking));
              }

              BookingReminderLog::create(['booking_id' => $booking->id, 'reminder_key' => $reminderKey, 'sent_at' => now()]);

              Log::info('Booking reminder sent', ['booking_id' => $booking->id, 'reminder_id' => $reminder->id, 'offset_minutes' => $reminder->offset_minutes]);
            }
          }
        } else {
          // fallback to legacy behaviour for events without configured reminders
          if ($bookingDateTime->between($legacyWindowStart, $legacyWindowEnd)) {
            $reminderKey = 'legacy_default:' . $bookingDateTime->format('Y-m-d H:i');
            $exists = BookingReminderLog::where('booking_id', $booking->id)->where('reminder_key', $reminderKey)->exists();
            if ($exists) {
              Log::info('Skipping already-sent legacy reminder', ['booking_id' => $booking->id, 'key' => $reminderKey]);
            } else {
              Notification::route('mail', [$booking->booker_email => $booking->booker_name])->notify(new BookingReminderNotification($booking));
              if ($booking->event && $booking->event->user) {
                $booking->event->user->notify(new BookingReminderNotification($booking));
              }
              BookingReminderLog::create(['booking_id' => $booking->id, 'reminder_key' => $reminderKey, 'sent_at' => now()]);
              Log::info('Booking reminder sent (legacy)', ['booking_id' => $booking->id]);
            }
          }
        }
      } catch (\Throwable $ex) {
        Log::error('Booking reminder failed', ['booking_id' => $booking->id ?? null, 'error' => $ex->getMessage()]);
      }
    }
  }
}
