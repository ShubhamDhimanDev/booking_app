<?php

namespace App\Services;

use Exception;
use App\Models\Setting;

class PayUService implements PaymentGatewayInterface
{
    protected $merchantKey;
    protected $merchantSalt;
    protected $environment;

    public function __construct()
    {
        $this->merchantKey = Setting::getSetting('payu_merchant_key') ?: env('PAYU_MERCHANT_KEY');
        $this->merchantSalt = Setting::getSetting('payu_merchant_salt') ?: env('PAYU_MERCHANT_SALT');
        $this->environment = env('PAYU_ENVIRONMENT', 'test');
    }

    public function getName(): string
    {
        return 'payu';
    }

    public function getPublicConfig(): array
    {
        return ['merchant_key' => $this->merchantKey, 'environment' => $this->environment];
    }

    public function initiatePayment(array $data): array
    {
        try {
            $amount = floatval($data['amount']);
            $productInfo = $data['product_info'] ?? 'Booking Payment';
            $firstName = $data['first_name'] ?? 'Customer';
            $email = $data['email'] ?? '';
            $txnId = $data['txn_id'] ?? 'txn_' . time() . rand(1000, 9999);

            $hashSequence = $this->merchantKey . '|' . $txnId . '|' . $amount . '|' . $productInfo . '|' . $firstName . '|' . $email . '|||||||||||' . $this->merchantSalt;
            $hash = hash('sha512', $hashSequence);

            $payuUrl = $this->environment === 'production'
                ? 'https://secure.payu.in/_payment'
                : 'https://test.payu.in/_payment';

            return [
                'gateway' => 'payu',
                'success' => true,
                'txn_id' => $txnId,
                'amount' => $amount,
                'merchant_key' => $this->merchantKey,
                'hash' => $hash,
                'payu_url' => $payuUrl,
                'product_info' => $productInfo,
                'first_name' => $firstName,
                'email' => $email,
            ];
        } catch (Exception $e) {
            return ['gateway' => 'payu', 'success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyPayment(array $payload): bool
    {
        try {
            $txnId = $payload['txnid'] ?? null;
            $amount = $payload['amount'] ?? null;
            $productInfo = $payload['productinfo'] ?? null;
            $firstName = $payload['firstname'] ?? null;
            $email = $payload['email'] ?? null;
            $status = $payload['status'] ?? null;
            $hash = $payload['hash'] ?? null;

            if (!$txnId || !$hash) {
                return false;
            }

            $hashSequence = $this->merchantSalt . '|' . $status . '|' . $productInfo . '|' . $firstName . '|' . $email . '|||||||||||' . $this->merchantKey . '|' . $txnId . '|' . $amount;
            $verifyHash = hash('sha512', $hashSequence);

            return $hash === $verifyHash && $status === 'success';
        } catch (Exception $e) {
            return false;
        }
    }
}
