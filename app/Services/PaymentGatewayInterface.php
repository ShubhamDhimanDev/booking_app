<?php

namespace App\Services;

interface PaymentGatewayInterface
{
    /**
     * Get gateway name (razorpay, payu, etc.)
     */
    public function getName(): string;

    /**
     * Get public configuration safe for frontend use
     */
    public function getPublicConfig(): array;

    /**
     * Initiate a payment and return the response needed for frontend.
     * @param array $data Should contain: amount, receipt, booking_id (optional), first_name, email, etc.
     */
    public function initiatePayment(array $data): array;

    /**
     * Verify payment callback/webhook.
     */
    public function verifyPayment(array $payload): bool;
}
