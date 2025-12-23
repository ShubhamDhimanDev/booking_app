<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;

class BookingReminderNotification extends Notification implements ShouldQueue
{
  use Queueable;


  protected Booking $booking;
  protected ?int $offsetMinutes;
  protected ?string $label;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(Booking $booking, ?int $offsetMinutes = null, ?string $label = null)
  {
    $this->booking = $booking;
    $this->offsetMinutes = $offsetMinutes;
    $this->label = $label;
  }

  protected function humanizeOffset(): string
  {
    if ($this->label) return $this->label;
    if (empty($this->offsetMinutes)) return 'soon';
    $m = (int) $this->offsetMinutes;
    if ($m % 1440 === 0) {
      $d = (int) ($m / 1440);
      return $d . ' day' . ($d > 1 ? 's' : '') . ' before';
    }
    if ($m % 60 === 0) {
      $h = (int) ($m / 60);
      return $h . ' hour' . ($h > 1 ? 's' : '') . ' before';
    }
    return $m . ' minute' . ($m > 1 ? 's' : '') . ' before';
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
    $when = $this->humanizeOffset();

    if ($notifiable instanceof AnonymousNotifiable) {
      return (new MailMessage)
        ->subject("Reminder: your booking in {$when}")
        ->greeting("Hello {$this->booking->booker_name}")
        ->line("This is a reminder that your booking for {$this->booking->event->user->name} is {$when}.")
        ->line("The booking is scheduled for {$this->booking->booked_at_date} at {$this->booking->booked_at_time}.")
        ->line("Click the following button to view the link in your calendar:")
        ->action("View event", $this->booking->calendar_link)
        ->line('Thank you for using our application!');
    }

    return (new MailMessage)
      ->subject("Reminder: meeting with {$this->booking->booker_name} in {$when}")
      ->greeting("Hello {$notifiable->name}")
      ->line("This is a reminder that you are meeting with {$this->booking->booker_name} on event {$this->booking->event->title} {$when}.")
      ->line("The booking is scheduled for {$this->booking->booked_at_date} at {$this->booking->booked_at_time}.")
      ->line("Click the following button to view the link in your calendar:")
      ->action("View event", $this->booking->calendar_link)
      ->line('Thank you for using our application!');
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
