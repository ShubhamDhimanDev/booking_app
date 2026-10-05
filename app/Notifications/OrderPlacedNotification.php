<?php

namespace App\Notifications;

use App\Models\FollowUpInvite;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Order $order, protected ?FollowUpInvite $invite = null)
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
            ->subject("Order {$order->order_number} confirmed")
            ->greeting("Hello {$order->customer_name},")
            ->line("Thank you for your order. We've received your payment of {$sym}" . number_format($order->total, 2) . '.');

        foreach ($order->items as $item) {
            $mail->line("{$item->quantity} x {$item->name} - {$sym}" . number_format($item->line_total, 2));
        }
        if ($order->discount > 0) {
            $mail->line("Discount ({$order->promo_code}): -{$sym}" . number_format($order->discount, 2));
        }

        $mail->line('Delivery address: ' . collect([
            $order->address_line1, $order->address_line2, $order->city, $order->state, $order->postal_code, $order->address_country,
        ])->filter()->implode(', '));

        if ($this->invite) {
            $mail->line('Your order includes a free session. Pick a time that suits you:')
                ->action('Book your free session', url("/followup/{$this->invite->token}"))
                ->line('This link works once and expires on ' . $this->invite->expires_at->format('j F Y') . '.');
        }

        return $mail->line('We will email you again when your order ships.');
    }
}
