<?php

namespace App\Services;

interface RefundServiceInterface
{
    /**
     * Get the refund service name
     */
    public function getName(): string;

    /**
     * Process a refund
     *
     * @param string $paymentId Gateway payment ID
     * @param float $amount Amount to refund
     * @param array $options Additional options
     * @return array Response from gateway
     */
    public function processRefund(string $paymentId, float $amount, array $options = []): array;

    /**
     * Get refund status
     *
     * @param string $refundId Gateway refund ID
     * @return array Refund details
     */
    public function getRefundStatus(string $refundId): array;

    /**
     * Calculate gateway charges for refund
     *
     * @param float $amount Refund amount
     * @return float Gateway charges
     */
    public function calculateGatewayCharges(float $amount): float;
}
