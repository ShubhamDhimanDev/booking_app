<?php

namespace App\Listeners;

use App\Events\BookingRescheduled;
use App\Jobs\SendBookingNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleBookingRescheduled implements ShouldQueue
{
    public function handle(BookingRescheduled $event): void
    {
        $booking = $event->booking;

        // Calendar is now updated synchronously in BookingService
        // Only queue notifications here with old date/time info
        SendBookingNotifications::dispatch($booking, 'rescheduled', $event->oldDate, $event->oldTime)
            ->onQueue('notifications');
    }
}
