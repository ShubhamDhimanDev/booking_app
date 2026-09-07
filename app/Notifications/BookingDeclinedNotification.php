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
  protected $timezone;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(Event $event, string $booker_name, string $booked_at_date, string $booked_at_time, ?string $decline_reason = null, ?string $timezone = null)
  {
    $this->event = $event;
    $this->booker_name = $booker_name;
    $this->booked_at_date = $booked_at_date;
    $this->booked_at_time = $booked_at_time;
    $this->decline_reason = $decline_reason;
    $this->timezone = $timezone;
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
    $visitorTz = $this->timezone ?: 'Asia/Kolkata';
    $istMoment = \Carbon\Carbon::parse(
        $this->booked_at_date . ' ' . $this->booked_at_time,
        'Asia/Kolkata'
    );
    $localMoment = $istMoment->copy()->setTimezone($visitorTz);

    return (new MailMessage)
      ->subject('Booking Declined - ' . $this->event->title)
      ->view('emails.booking-declined', [
        'organizerName' => $this->event->user->name,
        'eventTitle' => $this->event->title,
        'bookingDate' => $localMoment->format('l, F j, Y'),
        'bookingTime' => $localMoment->format('g:i A') . ' ' . $localMoment->format('T'),
        'istDate' => $istMoment->format('l, F j, Y'),
        'istTime' => $istMoment->format('g:i A'),
        'declineReason' => $this->decline_reason,
        'browseEventsUrl' => url('/e/' . $this->event->slug),
      ]);
  }
}
