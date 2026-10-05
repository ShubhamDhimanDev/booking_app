<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderRefundedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Order $order)
    {
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $order = $this->order;
        $amount = $order->currency_symbol . number_format($order->total, 2);

        return (new MailMessage)
            ->subject("Refund for order {$order->order_number}")
            ->greeting("Hello {$order->customer_name},")
            ->line("We have refunded {$amount} for order {$order->order_number} to your original payment method.")
            ->line('It usually reaches your account within 5-7 working days, depending on your bank.');
    }
}
