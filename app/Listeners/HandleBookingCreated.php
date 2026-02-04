<?php

namespace App\Listeners;

use App\Events\BookingCreated;
use App\Jobs\SendBookingNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandleBookingCreated implements ShouldQueue
{
    public function handle(BookingCreated $event): void
    {
        $booking = $event->booking;

        // Calendar is now created synchronously in BookingService
        // Only queue notifications here
        SendBookingNotifications::dispatch($booking, 'created')->onQueue('notifications');
    }
}
