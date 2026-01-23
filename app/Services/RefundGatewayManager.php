<?php

namespace App\Services;

use App\Models\Setting;
use Exception;

class RefundGatewayManager
{
    protected array $gateways = [];

    public function __construct()
    {
        $this->registerGateways();
    }

    /**
     * Register available refund gateways
     */
    protected function registerGateways(): void
    {
        $this->gateways['razorpay'] = new RazorpayRefundService();
        $this->gateways['payu'] = new PayURefundService();
    }

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

        return $this->gateways[$gateway];
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
