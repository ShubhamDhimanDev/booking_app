<?php

namespace App\Services;

use App\Models\Setting;
use Exception;
use Razorpay\Api\Api;

class RazorpayRefundService implements RefundServiceInterface
{
    protected Api $razorpay;

    public function __construct()
    {
        $keyId = Setting::getSetting('razorpay_key_id') ?? env('RAZORPAY_KEY_ID');
        $keySecret = Setting::getSetting('razorpay_key_secret') ?? env('RAZORPAY_KEY_SECRET');

        $this->razorpay = new Api($keyId, $keySecret);
    }

    /**
     * Get the refund service name
     */
    public function getName(): string
    {
        return 'Razorpay';
    }

    /**
     * Process a refund
     *
     * @param string $paymentId Razorpay payment ID
     * @param float $amount Amount to refund (in rupees)
     * @param array $options Additional options
     * @return array Response from Razorpay
     */
    public function processRefund(string $paymentId, float $amount, array $options = []): array
    {
        try {
            $payment = $this->razorpay->payment->fetch($paymentId);

            // Convert to paise (Razorpay uses paise)
            $amountInPaise = (int)($amount * 100);

            $refundData = [
                'amount' => $amountInPaise,
            ];

            // Add notes if provided
            if (isset($options['notes'])) {
                $refundData['notes'] = $options['notes'];
            }

            // Add speed if provided (normal or optimum)
            if (isset($options['speed'])) {
                $refundData['speed'] = $options['speed'];
            }

            $refund = $payment->refund($refundData);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100, // Convert back to rupees
                'status' => $refund->status,
                'payment_id' => $refund->payment_id,
                'created_at' => $refund->created_at,
                'raw_response' => $refund->toArray(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    /**
     * Get refund status
     *
     * @param string $refundId Razorpay refund ID
     * @return array Refund details
     */
    public function getRefundStatus(string $refundId): array
    {
        try {
            $refund = $this->razorpay->refund->fetch($refundId);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
                'status' => $refund->status,
                'payment_id' => $refund->payment_id,
                'created_at' => $refund->created_at,
                'raw_response' => $refund->toArray(),
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate gateway charges for refund
     * Razorpay doesn't charge for refunds (they refund the gateway charges)
     *
     * @param float $amount Refund amount
     * @return float Gateway charges (0 for Razorpay)
     */
    public function calculateGatewayCharges(float $amount): float
    {
        return 0.00;
    }
}
