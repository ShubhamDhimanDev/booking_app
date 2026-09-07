<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
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
        // Sent to the booker — convert IST wall-clock to their local timezone captured at booking time.
        // The organizer branch below is left in plain IST, unchanged.
        if ($notifiable instanceof AnonymousNotifiable) {
            $visitorTz = $this->booking->timezone ?: 'Asia/Kolkata';

            $newIstMoment = \Carbon\Carbon::parse($this->newDate . ' ' . $this->newTime, 'Asia/Kolkata');
            $newLocalMoment = $newIstMoment->copy()->setTimezone($visitorTz);

            $oldIstMoment = \Carbon\Carbon::parse($this->oldDate . ' ' . $this->oldTime, 'Asia/Kolkata');
            $oldLocalMoment = $oldIstMoment->copy()->setTimezone($visitorTz);

            return (new MailMessage)
                ->subject('Booking Rescheduled - ' . $this->booking->event->title)
                ->view('emails.booking-rescheduled', [
                    'eventTitle' => $this->booking->event->title,
                    'newBookingDate' => $newLocalMoment->format('l, F j, Y'),
                    'newBookingTime' => $newLocalMoment->format('g:i A') . ' ' . $newLocalMoment->format('T'),
                    'istNewDate' => $newIstMoment->format('l, F j, Y'),
                    'istNewTime' => $newIstMoment->format('g:i A'),
                    'oldBookingDate' => $oldLocalMoment->format('l, F j, Y'),
                    'oldBookingTime' => $oldLocalMoment->format('g:i A'),
                    'meetingLink' => $this->booking->meet_link ?? $this->booking->calendar_link,
                ]);
        }

        return (new MailMessage)
            ->subject('Booking Rescheduled - ' . $this->booking->event->title)
            ->view('emails.booking-rescheduled', [
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
