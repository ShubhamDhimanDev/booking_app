<?php

namespace App\Notifications;

use App\Models\Booking;
use App\Models\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RefundProcessedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $booking;
    public $refund;

    /**
     * Create a new notification instance.
     *
     * @param Booking $booking
     * @param Refund $refund
     * @return void
     */
    public function __construct(Booking $booking, Refund $refund)
    {
        $this->booking = $booking;
        $this->refund = $refund;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $eventName = $this->booking->event->title ?? 'Event';
        $refundAmount = '₹' . number_format($this->refund->net_refund_amount, 2);
        $gateway = ucfirst($this->refund->gateway);

        return (new MailMessage)
            ->subject('Refund Processed - ' . $eventName)
            ->view('emails.tests.refund-processed', [
                'refundAmount' => $refundAmount,
                'transactionId' => $this->refund->transaction_id ?? 'N/A',
                'processedDate' => $this->refund->created_at->format('M d, Y'),
                'refundMethod' => $gateway,
                'eventTitle' => $eventName,
                'transactionHistoryUrl' => url('/user/transactions'),
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
            'booking_id' => $this->booking->id,
            'refund_id' => $this->refund->id,
            'event_name' => $this->booking->event->name ?? 'Event',
            'refund_amount' => $this->refund->net_refund_amount,
            'gateway' => $this->refund->gateway,
            'message' => 'Your refund of ₹' . number_format($this->refund->net_refund_amount, 2) . ' has been processed.',
        ];
    }
}
