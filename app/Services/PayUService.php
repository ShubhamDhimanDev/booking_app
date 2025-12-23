<?php

namespace App\Services;

use Exception;
use App\Models\Setting;

class PayUService implements PaymentGatewayInterface
{
    protected $merchantKey;
    protected $merchantId;
    protected $merchantSalt;
    protected $environment;

    public function __construct()
    {
        $this->merchantKey = Setting::getSetting('payu_merchant_key') ?: env('PAYU_MERCHANT_KEY', '');
        $this->merchantId = Setting::getSetting('payu_merchant_id') ?: env('PAYU_MERCHANT_ID', '');
        $this->merchantSalt = Setting::getSetting('payu_merchant_salt') ?: env('PAYU_MERCHANT_SALT', '');
        $this->environment = env('PAYU_ENVIRONMENT', 'test');
    }

    public function getName(): string
    {
        return 'payu';
    }

    public function getPublicConfig(): array
    {
        return [
            'merchant_key' => $this->merchantKey,
            'merchant_id' => $this->merchantId,
            'environment' => $this->environment
        ];
    }

    public function initiatePayment(array $data): array
    {
        try {
            $amount = floatval($data['amount']);
            $productInfo = $data['product_info'] ?? 'Booking Payment';
            $firstName = $data['first_name'] ?? 'Customer';
            $email = $data['email'] ?? '';
            $phone = $data['phone'] ?? '';
            $txnId = $data['txn_id'] ?? 'Txn' . uniqid();

            // UDF fields (optional)
            $udf1 = $data['udf1'] ?? '';
            $udf2 = $data['udf2'] ?? '';
            $udf3 = $data['udf3'] ?? '';
            $udf4 = $data['udf4'] ?? '';
            $udf5 = $data['udf5'] ?? '';

            // Generate hash as per PayU documentation
            $hashSequence = $this->merchantKey . '|' . $txnId . '|' . $amount . '|' . $productInfo . '|' .
                           $firstName . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' .
                           $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $this->merchantSalt;
            $hash = strtolower(hash('sha512', $hashSequence));

            $payuUrl = $this->environment === 'production'
                ? 'https://secure.payu.in/_payment'
                : 'https://test.payu.in/_payment';

            return [
                'gateway' => 'payu',
                'success' => true,
                'txn_id' => $txnId,
                'amount' => $amount,
                'merchant_key' => $this->merchantKey,
                'merchant_id' => $this->merchantId,
                'hash' => $hash,
                'payu_url' => $payuUrl,
                'product_info' => $productInfo,
                'first_name' => $firstName,
                'email' => $email,
                'phone' => $phone,
                'service_provider' => 'payu_paisa',
                'udf1' => $udf1,
                'udf2' => $udf2,
                'udf3' => $udf3,
                'udf4' => $udf4,
                'udf5' => $udf5,
            ];
        } catch (Exception $e) {
            return ['gateway' => 'payu', 'success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyPayment(array $payload): bool
    {
        try {
            $status = $payload['status'] ?? null;
            $txnId = $payload['txnid'] ?? null;
            $amount = $payload['amount'] ?? null;
            $productInfo = $payload['productinfo'] ?? null;
            $firstName = $payload['firstname'] ?? null;
            $email = $payload['email'] ?? null;
            $hash = $payload['hash'] ?? null;

            // UDF fields
            $udf1 = $payload['udf1'] ?? '';
            $udf2 = $payload['udf2'] ?? '';
            $udf3 = $payload['udf3'] ?? '';
            $udf4 = $payload['udf4'] ?? '';
            $udf5 = $payload['udf5'] ?? '';

            if (!$txnId || !$hash || !$status) {
                return false;
            }

            // Generate reverse hash for verification (response hash format)
            $hashSequence = $this->merchantSalt . '|' . $status . '|||||||' .
                           $udf5 . '|' . $udf4 . '|' . $udf3 . '|' . $udf2 . '|' . $udf1 . '|' .
                           $email . '|' . $firstName . '|' . $productInfo . '|' . $amount . '|' .
                           $txnId . '|' . $this->merchantKey;
            $verifyHash = strtolower(hash('sha512', $hashSequence));

            return $hash === $verifyHash && strtolower($status) === 'success';
        } catch (Exception $e) {
            return false;
        }
    }
}
