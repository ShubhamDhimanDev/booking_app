<?php

namespace App\Notifications;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;

class BookingCreatedNotification extends Notification implements ShouldQueue
{
  use Queueable;

  protected Booking $booking;

  /**
   * Create a new notification instance.
   *
   * @return void
   */
  public function __construct(Booking $booking)
  {
    $this->booking = $booking;
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
    // For booker (guest/anonymous notifiable)
    if ($notifiable instanceof AnonymousNotifiable) {
      $startTime = \Carbon\Carbon::parse($this->booking->booked_at_date->toDateString() . ' ' . $this->booking->booked_at_time);
      $endTime = $startTime->copy()->addMinutes($this->booking->event->duration);

      return (new MailMessage)
        ->subject("Booking Confirmation - {$this->booking->event->title}")
        ->view('emails.booking-confirmation', [
          'bookerName' => $this->booking->booker_name,
          'eventTitle' => $this->booking->event->title,
          'bookingDate' => $startTime->format('l, M d, Y'), // e.g., "Monday, Feb 25, 2026"
          'bookingTime' => $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A'), // e.g., "03:00 PM - 04:00 PM"
          'timezone' => $this->booking->timezone ?? '(GMT+5:30) India Standard Time',
          'meetingLink' => $this->booking->meet_link ?? $this->booking->calendar_link,
          'organizerName' => $this->booking->event->user->name,
        ]);
    }

    // For organizer (event owner)
    $startTime = \Carbon\Carbon::parse($this->booking->booked_at_date->toDateString() . ' ' . $this->booking->booked_at_time);
    $endTime = $startTime->copy()->addMinutes($this->booking->event->duration);

    return (new MailMessage)
      ->subject("New Booking: {$this->booking->booker_name} - {$this->booking->event->title}")
      ->view('emails.booking-created-organizer', [
        'bookerName' => $this->booking->booker_name,
        'bookerEmail' => $this->booking->booker_email,
        'eventTitle' => $this->booking->event->title,
        'bookingDate' => $startTime->format('l, M d, Y'),
        'bookingTime' => $startTime->format('h:i A') . ' - ' . $endTime->format('h:i A'),
        'confirmUrl' => url("/bookings"),
        'rescheduleUrl' => url("/bookings"),
        'declineUrl' => url("/bookings"),
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
