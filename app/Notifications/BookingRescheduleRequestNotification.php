<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRescheduleRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Booking $booking, public ?string $note = null)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $link = route('user.bookings.reschedule.form', $this->booking->id);

        return (new MailMessage)
            ->subject('Please re-schedule your meeting: ' . $this->booking->event->title)
            ->view('emails.booking-reschedule-request', [
                'eventTitle' => $this->booking->event->title,
                'bookerName' => $this->booking->booker_name,
                'rescheduleLink' => $link,
                'note' => $this->note,
            ]);
    }

    public function toArray($notifiable)
    {
        return [];
    }
}
