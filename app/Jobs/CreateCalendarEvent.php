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

class CreateCalendarEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Booking $booking;
    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking;
    }

    public function handle(GoogleCalendarService $calendarService): void
    {
        try {
            // Skip if in testing or no calendar link needed
            if (app()->runningUnitTests() || $this->booking->calendar_id) {
                return;
            }

            $booking = $this->booking->load('event.user');

            // Create calendar event using the scheduled_at accessor
            $startTime = $booking->scheduled_at;
            $endTime = $startTime->copy()->addMinutes($booking->event->duration);

            $result = $calendarService->createEvent(
                organizer: $booking->event->user,
                title: $booking->event->title,
                startDateTime: $startTime,
                endDateTime: $endTime,
                attendeeEmail: $booking->booker_email,
                description: "Booking with {$booking->booker_name}"
            );

            // Update booking with calendar details
            $booking->update([
                'calendar_id' => $result['calendar_id'],
                'calendar_link' => $result['calendar_link'],
                'meet_link' => $result['meet_link'],
            ]);

            Log::info('Calendar event created', ['booking_id' => $booking->id]);

        } catch (Exception $e) {
            Log::error('Failed to create calendar event', [
                'booking_id' => $this->booking->id,
                'error' => $e->getMessage()
            ]);

            // Don't fail the job - calendar creation is optional
            // The booking is still valid without calendar link
        }
    }
}
