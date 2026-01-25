<?php

namespace App\Notifications;

use App\Models\FollowUpInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FollowUpInviteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected FollowUpInvite $invite;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(FollowUpInvite $invite)
    {
        $this->invite = $invite;
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
        $bookingUrl = url("/followup/{$this->invite->token}");

        return (new MailMessage)
            ->subject("Follow-up Session Invitation - {$this->invite->event->title}")
            ->view('emails.follow-up-invite', [
                'userName' => $this->invite->user->name ?? $this->invite->booking->booker_name,
                'eventTitle' => $this->invite->event->title,
                'customPrice' => $this->invite->custom_price,
                'isFree' => $this->invite->custom_price == 0,
                'bookingUrl' => $bookingUrl,
                'organizerName' => $this->invite->event->user->name,
                'expiresAt' => $this->invite->expires_at,
                'originalBookingDate' => $this->invite->booking->booked_at_date,
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
