<?php

namespace App\Jobs;

use App\Models\Refund;
use App\Services\OrderService;
use App\Services\RefundGatewayManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

/** Sends a store-order refund to the payment gateway, then closes the order. Safe to retry. */
class ProcessOrderRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 120;
    public $backoff = 60;

    public function __construct(public Refund $refund)
    {
    }

    public function handle(RefundGatewayManager $gateways, OrderService $orders)
    {
        $refund = $this->refund->fresh(['order', 'payment']);

        if (! $refund || $refund->isCompleted()) {
            return;
        }

        $order = $refund->order;
        $payment = $refund->payment;
        if (! $order || ! $payment || ! $payment->transaction_id) {
            throw new RuntimeException('Order or gateway payment not found for this refund.');
        }

        $refund->markAsProcessing();

        $result = $gateways->getGateway($payment->provider)->processRefund(
            $payment->transaction_id,
            (float) $refund->net_refund_amount,
            ['notes' => ['order' => $order->order_number]]
        );

        if (empty($result['success'])) {
            throw new RuntimeException($result['error'] ?? 'The gateway rejected the refund.');
        }

        $refund->markAsCompleted((string) ($result['refund_id'] ?? ''), $result['raw_response'] ?? []);
        $orders->completeRefund($order);

        Log::info('Order refund completed', ['refund_id' => $refund->id, 'order' => $order->order_number]);
    }

    /** Called after the last failed attempt (and for any exception, so the admin sees the reason right away). */
    public function failed(Throwable $e)
    {
        $this->refund->fresh()?->markAsFailed($e->getMessage());

        Log::critical('Order refund failed', ['refund_id' => $this->refund->id, 'error' => $e->getMessage()]);
    }
}
