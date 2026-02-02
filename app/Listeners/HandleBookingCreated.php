<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Jobs\SendBookingNotifications;
use App\Jobs\CreateCalendarEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleBookingCreated implements ShouldQueue
{
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;

        // Queue calendar event creation
        CreateCalendarEvent::dispatch($booking)->onQueue('calendar');

        // Queue notifications
        SendBookingNotifications::dispatch($booking, 'created')->onQueue('notifications');
    }
}
