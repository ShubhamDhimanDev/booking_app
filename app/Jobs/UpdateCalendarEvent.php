<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Services\GoogleCalendarService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class UpdateCalendarEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Booking $booking;
    public string $oldDate;
    public string $oldTime;
    public int $tries = 3;

    public function __construct(Booking $booking, string $oldDate, string $oldTime)
    {
        $this->booking = $booking;
        $this->oldDate = $oldDate;
        $this->oldTime = $oldTime;
    }

    public function handle(GoogleCalendarService $calendarService): void
    {
        try {
            if (app()->runningUnitTests() || !$this->booking->calendar_id) {
                return;
            }

            $booking = $this->booking->load('event.user');

            $newStartTime = Carbon::parse($booking->booked_at_date->toDateString() . ' ' . $booking->booked_at_time);
            $newEndTime = $newStartTime->copy()->addMinutes($booking->event->duration);

            $calendarService->updateEvent(
                organizer: $booking->event->user,
                eventId: $booking->calendar_id,
                newStartDateTime: $newStartTime,
                newEndDateTime: $newEndTime
            );

            Log::info('Calendar event updated', ['booking_id' => $booking->id]);

        } catch (Exception $e) {
            Log::error('Failed to update calendar event', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage()
            ]);

            // If update fails, try to delete and recreate
            try {
                DeleteCalendarEvent::dispatch($booking);
                CreateCalendarEvent::dispatch($booking);
            } catch (Exception $recreateError) {
                Log::error('Failed to recreate calendar event', [
                    'booking_id' => $booking->id,
                    'error' => $recreateError->getMessage()
                ]);
            }
        }
    }
}
