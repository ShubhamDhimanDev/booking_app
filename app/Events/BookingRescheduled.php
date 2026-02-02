<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingRescheduled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Booking $booking;
    public string $oldDate;
    public string $oldTime;

    public function __construct(Booking $booking, string $oldDate, string $oldTime)
    {
        $this->booking = $booking->load(['event.user', 'booker']);
        $this->oldDate = $oldDate;
        $this->oldTime = $oldTime;
    }
}
