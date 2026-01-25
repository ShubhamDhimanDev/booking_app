<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRescheduledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(public Booking $booking,
        public string $oldDate,
        public string $oldTime,
        public string $newDate,
        public string $newTime)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Booking Rescheduled - ' . $this->booking->event->title)
            ->view('emails.tests.booking-rescheduled', [
                'eventTitle' => $this->booking->event->title,
                'newBookingDate' => $this->newDate,
                'newBookingTime' => $this->newTime,
                'oldBookingDate' => $this->oldDate,
                'oldBookingTime' => $this->oldTime,
                'meetingLink' => $this->booking->meet_link ?? $this->booking->calendar_link,
            ]);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
