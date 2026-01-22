<?php

namespace App\Services;

use App\Models\Setting;
use Exception;

class PayURefundService implements RefundServiceInterface
{
    protected string $merchantKey;
    protected string $merchantSalt;
    protected string $baseUrl;

    public function __construct()
    {
        $this->merchantKey = Setting::getSetting('payu_merchant_key') ?? env('PAYU_MERCHANT_KEY');
        $this->merchantSalt = Setting::getSetting('payu_merchant_salt') ?? env('PAYU_MERCHANT_SALT');
        $this->baseUrl = env('PAYU_BASE_URL', 'https://info.payu.in/merchant');
    }

    /**
     * Get the refund service name
     */
    public function getName(): string
    {
        return 'PayU';
    }

    /**
     * Process a refund
     *
     * @param string $paymentId PayU payment/transaction ID
     * @param float $amount Amount to refund
     * @param array $options Additional options (token, refund_amount)
     * @return array Response from PayU
     */
    public function processRefund(string $paymentId, float $amount, array $options = []): array
    {
        try {
            $refundUrl = $this->baseUrl . '/refundPayment';

            $postData = [
                'key' => $this->merchantKey,
                'command' => 'cancel_refund_transaction',
                'var1' => $paymentId, // PayU transaction ID
                'var2' => $options['token'] ?? '', // Bank reference number
                'var3' => $amount,
                'hash' => $this->generateRefundHash($paymentId, $amount),
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $refundUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $responseData = json_decode($response, true);

            if ($httpCode == 200 && isset($responseData['status']) && $responseData['status'] == 1) {
                return [
                    'success' => true,
                    'refund_id' => $responseData['request_id'] ?? uniqid('payu_'),
                    'amount' => $amount,
                    'status' => 'initiated',
                    'message' => $responseData['msg'] ?? 'Refund initiated successfully',
                    'raw_response' => $responseData,
                ];
            }

            return [
                'success' => false,
                'error' => $responseData['msg'] ?? 'Refund failed',
                'raw_response' => $responseData,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get refund status
     *
     * @param string $refundId PayU refund request ID
     * @return array Refund details
     */
    public function getRefundStatus(string $refundId): array
    {
        try {
            $statusUrl = $this->baseUrl . '/refundStatus';

            $postData = [
                'key' => $this->merchantKey,
                'command' => 'check_refund_status',
                'var1' => $refundId,
                'hash' => $this->generateStatusHash($refundId),
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $statusUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            curl_close($ch);

            $responseData = json_decode($response, true);

            return [
                'success' => isset($responseData['status']) && $responseData['status'] == 1,
                'refund_id' => $refundId,
                'status' => $responseData['refund_status'] ?? 'unknown',
                'raw_response' => $responseData,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Calculate gateway charges for refund
     * PayU typically doesn't refund gateway charges
     *
     * @param float $amount Refund amount
     * @return float Gateway charges (2% typical)
     */
    public function calculateGatewayCharges(float $amount): float
    {
        // Typically 2% + GST, but check with PayU agreement
        return round($amount * 0.02, 2);
    }

    /**
     * Generate hash for refund request
     */
    protected function generateRefundHash(string $paymentId, float $amount): string
    {
        $hashString = $this->merchantKey . '|cancel_refund_transaction|' . $paymentId . '|' . $amount . '|' . $this->merchantSalt;
        return hash('sha512', $hashString);
    }

    /**
     * Generate hash for status check
     */
    protected function generateStatusHash(string $refundId): string
    {
        $hashString = $this->merchantKey . '|check_refund_status|' . $refundId . '|' . $this->merchantSalt;
        return hash('sha512', $hashString);
    }
}
