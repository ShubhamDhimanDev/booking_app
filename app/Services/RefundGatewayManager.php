<?php

namespace App\Services;

use App\Models\Setting;
use Exception;

class RefundGatewayManager
{
    protected array $gateways = [
        'razorpay' => RazorpayRefundService::class,
        'payu'     => PayURefundService::class,
    ];

    /**
     * Get refund service for a specific gateway
     *
     * @param string $gateway Gateway name (razorpay or payu)
     * @return RefundServiceInterface
     * @throws Exception
     */
    public function getGateway(string $gateway): RefundServiceInterface
    {
        if (!isset($this->gateways[$gateway])) {
            throw new Exception("Refund gateway '{$gateway}' is not supported.");
        }

        return new $this->gateways[$gateway]();
    }

    /**
     * Get all available gateways
     *
     * @return array
     */
    public function getAvailableGateways(): array
    {
        return array_keys($this->gateways);
    }
}
