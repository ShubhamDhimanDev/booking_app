<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\GoogleCalendarService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class DeleteCalendarEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Booking $booking;
    public int $tries = 2;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function handle(GoogleCalendarService $calendarService): void
    {
        try {
            if (app()->runningUnitTests() || !$this->booking->calendar_id) {
                return;
            }

            $booking = $this->booking->load('event.user');

            $calendarService->deleteEvent($booking->event->user, $booking->calendar_id);

            // Clear calendar details from booking
            $booking->update([
                'calendar_id' => null,
                'calendar_link' => null,
                'meet_link' => null,
            ]);

            Log::info('Calendar event deleted', ['booking_id' => $booking->id]);

        } catch (Exception $e) {
            Log::warning('Failed to delete calendar event', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
