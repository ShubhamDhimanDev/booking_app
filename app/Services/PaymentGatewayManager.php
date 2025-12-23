<?php

namespace App\Services;

use App\Models\Setting;

class PaymentGatewayManager
{
    protected $gateways = [
        'razorpay' => RazorpayService::class,
        'payu' => PayUService::class,
    ];

    /**
     * Resolve the currently active gateway instance based on settings.
     */
    public function getActiveGateway(): PaymentGatewayInterface
    {
        $key = Setting::getSetting('payment_gateway', 'razorpay');
        $service = $this->gateways[$key] ?? $this->gateways['razorpay'];
        return app($service);
    }

    /**
     * Expose public-safe config for the active gateway (used by frontend).
     */
    public function getActiveGatewayConfig(): array
    {
        $gateway = $this->getActiveGateway();
        return array_merge(['name' => $gateway->getName()], $gateway->getPublicConfig());
    }

    public function getAvailableGateways(): array
    {
        return array_keys($this->gateways);
    }
}