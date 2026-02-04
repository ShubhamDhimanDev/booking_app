<?php

namespace App\Listeners;

use App\Events\PaymentProcessed;
use App\Jobs\SendBookingNotifications;
use Illuminate\Contracts\Queue\ShouldQueue;

class HandlePaymentProcessed implements ShouldQueue
{
    public function handle(PaymentProcessed $event): void
    {
        $booking = $event->booking;

        // Calendar is now created synchronously in PaymentService
        // Only queue notifications here
        SendBookingNotifications::dispatch($booking, 'confirmed')->onQueue('notifications');
    }
}
