<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BookingDeclinedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  protected $event;
  protected $booker_name;
  protected $booked_at_date;
  protected $booked_at_time;
  protected $decline_reason;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(Event $event, string $booker_name, string $booked_at_date, string $booked_at_time, ?string $decline_reason = null)
  {
    $this->event = $event;
    $this->booker_name = $booker_name;
    $this->booked_at_date = $booked_at_date;
    $this->booked_at_time = $booked_at_time;
    $this->decline_reason = $decline_reason;
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
      ->subject('Booking Declined - ' . $this->event->title)
      ->view('emails.booking-declined', [
        'organizerName' => $this->event->user->name,
        'eventTitle' => $this->event->title,
        'bookingDate' => $this->booked_at_date,
        'bookingTime' => $this->booked_at_time,
        'declineReason' => $this->decline_reason,
        'browseEventsUrl' => url('/e/' . $this->event->slug),
      ]);
  }
}
