<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\AppSetting;
use App\Services\PaymentGateways\RazorpayGateway;
use InvalidArgumentException;

/**
 * Payment Gateway Factory
 *
 * Factory class to instantiate the appropriate payment gateway
 * based on the gateway name.
 */
class PaymentGatewayFactory
{
    /**
     * Create a payment gateway instance
     *
     * @param string $gateway Gateway name (razorpay, stripe, paypal, etc.)
     * @return PaymentGatewayInterface
     * @throws InvalidArgumentException
     */
    public static function make(string $gateway): PaymentGatewayInterface
    {
        return match (strtolower($gateway)) {
            'razorpay' => new RazorpayGateway(),
            // 'stripe' => new StripeGateway(),      // Future implementation
            // 'paypal' => new PayPalGateway(),      // Future implementation
            // 'payu' => new PayUGateway(),          // Future implementation
            default => throw new InvalidArgumentException("Unsupported payment gateway: {$gateway}"),
        };
    }

    /**
     * Get list of supported gateways
     *
     * @return array
     */
    public static function getSupportedGateways(): array
    {
        return [
            'razorpay' => [
                'name' => 'Razorpay',
                'enabled' => !empty(config('services.razorpay.key')),
                'supported_currencies' => ['INR'],
                'features' => ['subscriptions', 'one_time_payments', 'refunds', 'webhooks'],
            ],
            // Future gateways
            // 'stripe' => [...],
            // 'paypal' => [...],
        ];
    }

    /**
     * Check if a gateway is supported
     *
     * @param string $gateway
     * @return bool
     */
    public static function isSupported(string $gateway): bool
    {
        return array_key_exists(strtolower($gateway), self::getSupportedGateways());
    }

    /**
     * Get default gateway from config
     *
     * @return string
     */
    public static function getDefaultGateway(): string
    {
        // Try to get from AppSetting first, fallback to config
        return AppSetting::get('default_payment_gateway', config('services.default_payment_gateway', 'razorpay'));
    }
}
