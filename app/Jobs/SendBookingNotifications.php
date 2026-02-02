<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Notifications\BookingCreatedNotification;
use App\Notifications\BookingDeclinedNotification;
use App\Notifications\BookingRescheduledNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;

class SendBookingNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Booking $booking;
    public string $type;
    public int $tries = 3;

    public function __construct(Booking $booking, string $type)
    {
        $this->booking = $booking;
        $this->type = $type;
    }

    public function handle(): void
    {
        $booking = $this->booking->load(['event.user', 'booker']);

        try {
            switch ($this->type) {
                case 'created':
                case 'confirmed':
                    $this->sendCreatedNotifications($booking);
                    break;

                case 'cancelled':
                    $this->sendCancelledNotifications($booking);
                    break;

                case 'rescheduled':
                    $this->sendRescheduledNotifications($booking);
                    break;
            }

            Log::info("Booking {$this->type} notifications sent", [
                'booking_id' => $booking->id,
                'type' => $this->type
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to send {$this->type} notifications", [
                'booking_id' => $booking->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    protected function sendCreatedNotifications(Booking $booking): void
    {
        // Notify organizer
        $booking->event->user->notify(new BookingCreatedNotification($booking));

        // Notify booker
        Notification::route('mail', [$booking->booker_email => $booking->booker_name])
            ->notify(new BookingCreatedNotification($booking));
    }

    protected function sendCancelledNotifications(Booking $booking): void
    {
        // Notify booker
        Notification::route('mail', [$booking->booker_email => $booking->booker_name])
            ->notify(new BookingDeclinedNotification(
                $booking->event->title,
                $booking->booker_name,
                $booking->booked_at_date,
                $booking->booked_at_time
            ));

        // Could also notify organizer here if needed
    }

    protected function sendRescheduledNotifications(Booking $booking): void
    {
        $notification = new BookingRescheduledNotification(
            $booking->event->title,
            $booking->booker_name,
            $booking->booked_at_date,
            $booking->booked_at_time,
            $booking->booked_at_date, // old date
            $booking->booked_at_time  // old time
        );

        // Notify organizer
        $booking->event->user->notify($notification);

        // Notify booker
        Notification::route('mail', [$booking->booker_email => $booking->booker_name])
            ->notify($notification);
    }
}
