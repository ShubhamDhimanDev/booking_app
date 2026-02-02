<?php

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Jobs\DeleteCalendarEvent;
use App\Jobs\SendBookingNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleBookingCancelled implements ShouldQueue
{
    public function handle(BookingCancelled $event): void
    {
        $booking = $event->booking;

        // Queue calendar event deletion
        if ($booking->calendar_id) {
            DeleteCalendarEvent::dispatch($booking)->onQueue('calendar');
        }

        // Queue notifications
        SendBookingNotifications::dispatch($booking, 'cancelled')->onQueue('notifications');
    }
}
