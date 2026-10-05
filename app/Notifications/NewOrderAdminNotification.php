<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderAdminNotification extends Notification implements ShouldQueue
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
        $order = $this->order->loadMissing('items');
        $sym = $order->currency_symbol;

        $mail = (new MailMessage)
            ->subject("New order {$order->order_number} ({$sym}" . number_format($order->total, 2) . ')')
            ->line("{$order->customer_name} <{$order->customer_email}> placed an order.");

        foreach ($order->items as $item) {
            $mail->line("{$item->quantity} x {$item->name}");
        }

        return $mail->line('Ship to: ' . collect([
            $order->address_line1, $order->address_line2, $order->city, $order->state, $order->postal_code, $order->address_country,
        ])->filter()->implode(', '));
    }
}
