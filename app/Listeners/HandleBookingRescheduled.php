<?php

namespace App\Listeners;

use App\Events\BookingRescheduled;
use App\Jobs\UpdateCalendarEvent;
use App\Jobs\SendBookingNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleBookingRescheduled implements ShouldQueue
{
    public function handle(BookingRescheduled $event): void
    {
        $booking = $event->booking;

        // Queue calendar event update
        UpdateCalendarEvent::dispatch(
            $booking,
            $event->oldDate,
            $event->oldTime
        )->onQueue('calendar');

        // Queue notifications
        SendBookingNotifications::dispatch($booking, 'rescheduled')->onQueue('notifications');
    }
}
