<?php

namespace App\Jobs;

use App\Models\Booking;
use App\Models\Refund;
use App\Services\RefundGatewayManager;
use App\Notifications\RefundProcessedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Exception;

class ProcessRefundJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $refund;
    public $tries = 3;
    public $timeout = 120;

    /**
     * Create a new job instance.
     *
     * @param Refund $refund
     * @return void
     */
    public function __construct(Refund $refund)
    {
        $this->refund = $refund;
    }

    /**
     * Execute the job.
     *
     * @param RefundGatewayManager $refundManager
     * @return void
     */
    public function handle(RefundGatewayManager $refundManager)
    {
        try {
            // Load relationships
            $this->refund->load(['booking.event', 'booking.booker', 'payment']);

            $booking = $this->refund->booking;
            $payment = $this->refund->payment;

            if (!$payment) {
                throw new Exception('Payment not found for refund');
            }

            // Mark refund as processing
            $this->refund->markAsProcessing();

            // Get the appropriate gateway service
            $gateway = $refundManager->getGateway($payment->provider);

            if (!$gateway) {
                throw new Exception("Refund gateway '{$payment->provider}' not found");
            }

            // Calculate gateway charges
            $gatewayCharges = 0;
            if ($booking->event && $booking->event->deduct_gateway_charges) {
                $gatewayCharges = $gateway->calculateGatewayCharges($this->refund->amount);
            }

            // Calculate net refund amount
            $netRefundAmount = $this->refund->amount - $gatewayCharges;

            // Process the refund through the gateway
            $result = $gateway->processRefund(
                $payment->transaction_id,
                $netRefundAmount,
                [
                    'booking_id' => $booking->id,
                    'reason' => $booking->cancellation_reason ?? 'Booking cancelled by user',
                ]
            );

            if ($result['success']) {
                // Update refund record with success
                $this->refund->update([
                    'status' => 'completed',
                    'gateway_refund_id' => $result['refund_id'] ?? null,
                    'gateway_charges' => $gatewayCharges,
                    'net_refund_amount' => $netRefundAmount,
                    'gateway_response' => $result['response'] ?? [],
                    'processed_at' => now(),
                ]);

                // Update booking refund status
                $booking->update([
                    'refund_status' => 'completed',
                    'refund_amount' => $netRefundAmount,
                ]);

                // Send notification to booker
                if ($booking->booker) {
                    $booking->booker->notify(new RefundProcessedNotification($booking, $this->refund));
                }

                Log::info("Refund processed successfully", [
                    'refund_id' => $this->refund->id,
                    'booking_id' => $booking->id,
                    'amount' => $netRefundAmount,
                    'gateway' => $payment->provider,
                ]);

            } else {
                // Mark as failed
                $this->refund->markAsFailed($result['error'] ?? 'Unknown error');

                // Update booking refund status
                $booking->update([
                    'refund_status' => 'failed',
                ]);

                Log::error("Refund failed", [
                    'refund_id' => $this->refund->id,
                    'booking_id' => $booking->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                // Retry the job if attempts remain
                if ($this->attempts() < $this->tries) {
                    $this->release(60); // Retry after 60 seconds
                }
            }

        } catch (Exception $e) {
            Log::error("Refund job exception", [
                'refund_id' => $this->refund->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Mark refund as failed
            $this->refund->markAsFailed($e->getMessage());

            // Update booking refund status
            if ($this->refund->booking) {
                $this->refund->booking->update([
                    'refund_status' => 'failed',
                ]);
            }

            // Retry the job if attempts remain
            if ($this->attempts() < $this->tries) {
                $this->release(120); // Retry after 2 minutes
            } else {
                // Send notification to admin about failed refund
                Log::critical("Refund job failed after {$this->tries} attempts", [
                    'refund_id' => $this->refund->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Handle a job failure.
     *
     * @param Exception $exception
     * @return void
     */
    public function failed(Exception $exception)
    {
        Log::critical("Refund job permanently failed", [
            'refund_id' => $this->refund->id,
            'error' => $exception->getMessage(),
        ]);

        // Mark refund as failed
        $this->refund->markAsFailed("Job failed: {$exception->getMessage()}");

        // Update booking refund status
        if ($this->refund->booking) {
            $this->refund->booking->update([
                'refund_status' => 'failed',
            ]);
        }
    }
}
