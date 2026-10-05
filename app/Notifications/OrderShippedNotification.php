<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderShippedNotification extends Notification implements ShouldQueue
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

        $mail = (new MailMessage)
            ->subject("Your order {$order->order_number} has shipped")
            ->greeting("Hello {$order->customer_name},")
            ->line("Good news: your order {$order->order_number} is on its way.");

        if ($order->carrier || $order->tracking_number) {
            $mail->line('Carrier: ' . ($order->carrier ?: '-'))
                ->line('Tracking number: ' . ($order->tracking_number ?: '-'));
        }

        return $mail->line('Thank you for shopping with us.');
    }
}
