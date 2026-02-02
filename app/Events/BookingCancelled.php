<?php

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCancelled
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Booking $booking;
    public array $refundDetails;

    public function __construct(Booking $booking, array $refundDetails = [])
    {
        $this->booking = $booking->load(['event.user', 'booker', 'payment']);
        $this->refundDetails = $refundDetails;
    }
}
