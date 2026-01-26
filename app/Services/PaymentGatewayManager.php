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
     * Get a specific gateway by name.
     */
    public function getGateway(string $name): PaymentGatewayInterface
    {
        $service = $this->gateways[$name] ?? null;
        if (!$service) {
            throw new \Exception("Payment gateway '{$name}' not found");
        }
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
