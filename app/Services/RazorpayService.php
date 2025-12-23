<?php

namespace App\Services;

use Razorpay\Api\Api;
use Exception;
use App\Models\Setting;

class RazorpayService implements PaymentGatewayInterface
{
    protected $api;
    protected $keyId;
    protected $keySecret;

    public function __construct()
    {
        $this->keyId = Setting::getSetting('razorpay_key_id') ?: env('RAZORPAY_KEY_ID');
        $this->keySecret = Setting::getSetting('razorpay_key_secret') ?: env('RAZORPAY_KEY_SECRET');
        $this->api = new Api($this->keyId, $this->keySecret);
    }

    public function getName(): string
    {
        return 'razorpay';
    }

    public function getPublicConfig(): array
    {
        return ['key_id' => $this->keyId];
    }

    public function initiatePayment(array $data): array
    {
        try {
            $order = $this->api->order->create([
                'receipt' => $data['receipt'] ?? 'order_' . time(),
                'amount' => intval($data['amount'] * 100),
                'currency' => 'INR',
            ]);
            return [
                'gateway' => 'razorpay',
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $data['amount'],
                'key' => $this->keyId,
            ];
        } catch (Exception $e) {
            return ['gateway' => 'razorpay', 'success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyPayment(array $payload): bool
    {
        try {
            $this->api->utility->verifyPaymentSignature($payload);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
