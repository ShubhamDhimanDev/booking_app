<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BookingRescheduledNotification extends Notification
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
            ->subject('Booking Rescheduled')
            ->greeting('Hello!')
            ->line('Your booking has been rescheduled.')
            ->line('🕒 **Previous Schedule**')
            ->line($this->oldDate . ' at ' . $this->oldTime)
            ->line('🕒 **New Schedule**')
            ->line($this->newDate . ' at ' . $this->newTime)
            ->when($this->booking->meet_link, function ($mail) {
                $mail->action('Join Meeting', $this->booking->meet_link);
            })
            ->line('If you have any questions, please contact us.')
            ->salutation('Thank you');
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
