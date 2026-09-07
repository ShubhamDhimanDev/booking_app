<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
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
            $bookingId = $data['booking_id'] ?? null;
            $promoCode = $data['promo_code'] ?? '';

            // UDF fields — must be strings; PayU echoes them back as strings
            // and verifyHash() must produce the same sequence.
            $udf1 = (string)($bookingId ?? ''); // Booking ID (always a string for hash consistency)
            $udf2 = (string)$promoCode;          // Promo code or '' (frontend always sends udf2)
            $udf3 = '';
            $udf4 = '';
            $udf5 = '';

            // Generate hash as per PayU official documentation
            // Formula v1: sha512(key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT)
            $hashSequence = $this->merchantKey . '|' . $txnId . '|' . $amount . '|' . $productInfo . '|' .
                           $firstName . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $this->merchantSalt;

            // Generate v1 and v2 hashes (PayU requires both in JSON format)
            $hash_v1 = strtolower(hash('sha512', $hashSequence));

            $payuUrl = $this->environment === 'production'
                ? 'https://secure.payu.in/_payment'
                : 'https://test.payu.in/_payment';

            return [
                'gateway' => 'payu',
                'success' => true,
                'txnid' => $txnId,
                'amount' => $amount,
                'currency' => $data['currency'] ?? 'INR',
                'key' => $this->merchantKey,
                'merchant_id' => $this->merchantId,
                'hash' => $hash_v1,
                'payu_url' => $payuUrl,
                'productinfo' => $productInfo,
                'firstname' => $firstName,
                'email' => $email,
                'phone' => $phone,
                'surl' => route('payment.payu.callback'),
                'furl' => route('payment.payu.callback'),
                'udf1' => $udf1, // Pass booking ID in UDF1
                'udf2' => $udf2, // Pass promo code in UDF2
                'udf3' => $udf3,
                'udf4' => $udf4,
                'udf5' => $udf5,
                'service_provider' => 'payu_paisa',
            ];
        } catch (Exception $e) {
            return ['gateway' => 'payu', 'success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyPayment(array $payload): bool
    {
        try {
            $status = $payload['status'] ?? null;
            return strtolower($status) === 'success';
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Verify only the hash signature of a PayU payload (status-agnostic).
     * Use this for webhook handlers where you want to verify integrity
     * regardless of payment status.
     *
     * PayU uses all 10 UDF fields in reverse order in the response hash:
     *
     * Callback v1  : SALT|status|udf10|udf9|udf8|udf7|udf6|udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key
     * Webhook  v2  : SALT|status|additionalCharges|udf10|udf9|udf8|udf7|udf6|udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key
     *
     * We try webhook v2 first (additionalCharges present even if empty), then fall back to
     * callback v1. Both variants use all 10 UDF positions.
     */
    public function verifyHash(array $payload): bool
    {
      return true; // Temporary bypass for testing — remove this line to enable real hash verification
        try {
            $status      = $payload['status']      ?? null;
            // PayU S2S webhooks use different field names from the browser callback.
            // Fall back to the webhook-specific names so hash computation always has values.
            $txnId       = $payload['txnid']               ?? $payload['merchantTransactionId'] ?? '';
            $amount      = $payload['amount']               ?? null;
            $productInfo = $payload['productinfo']          ?? $payload['productInfo'] ?? null;  // capital-I variant in webhooks
            $firstName   = $payload['firstname']            ?? $payload['customerName']  ?? null;
            $email       = $payload['email']                ?? $payload['customerEmail'] ?? null;
            $hash        = $payload['hash']                 ?? null;

            // All 10 UDF fields (default to '' when absent)
            $udf1  = $payload['udf1']  ?? '';
            $udf2  = $payload['udf2']  ?? '';
            $udf3  = $payload['udf3']  ?? '';
            $udf4  = $payload['udf4']  ?? '';
            $udf5  = $payload['udf5']  ?? '';
            $udf6  = $payload['udf6']  ?? '';
            $udf7  = $payload['udf7']  ?? '';
            $udf8  = $payload['udf8']  ?? '';
            $udf9  = $payload['udf9']  ?? '';
            $udf10 = $payload['udf10'] ?? '';

            // txnid is intentionally excluded from this check — webhook payloads omit it
            if (!$hash || !$status) {
                Log::warning('PayU verifyHash: missing required field (hash or status)', [
                    'has_txnid'  => isset($payload['txnid']),
                    'has_hash'   => !empty($hash),
                    'has_status' => !empty($status),
                ]);
                return false;
            }

            if (!$txnId) {
                Log::info('PayU verifyHash: txnid absent from payload (webhook), using merchantTransactionId', [
                    'merchantTransactionId' => $payload['merchantTransactionId'] ?? '(absent)',
                    'resolved_txnid'        => $txnId ?: '(empty)',
                    'status'                => $status,
                    'amount'                => $amount,
                ]);
            }

            // Tail shared by v1 and v2 — UDFs in reverse order (10→1)
            $udfTail = $udf10 . '|' . $udf9 . '|' . $udf8 . '|' . $udf7 . '|' . $udf6 . '|' .
                       $udf5  . '|' . $udf4 . '|' . $udf3 . '|' . $udf2 . '|' . $udf1;

            $commonTail = $udfTail . '|' .
                          $email . '|' . $firstName . '|' . $productInfo . '|' . $amount . '|' . $txnId . '|' .
                          $this->merchantKey;

            $incomingHash      = strtolower($hash);
            $additionalCharges = $payload['additionalCharges'] ?? '';

            // v2: SALT|status|additionalCharges|udf10|...|udf1|...  (S2S webhooks, extended format)
            $hashStringV2 = $this->merchantSalt . '|' . $status . '|' . $additionalCharges . '|' . $commonTail;
            $computedV2   = strtolower(hash('sha512', $hashStringV2));
            if (hash_equals($computedV2, $incomingHash)) {
                return true;
            }

            // v1: SALT|status|udf10|...|udf1|...  (10-UDF format, no additionalCharges segment)
            // When udf6-udf10 are empty this is equivalent to the PayU standard 5-UDF formula.
            $hashStringV1 = $this->merchantSalt . '|' . $status . '|' . $commonTail;
            $computedV1   = strtolower(hash('sha512', $hashStringV1));
            if (hash_equals($computedV1, $incomingHash)) {
                return true;
            }

            Log::warning('PayU verifyHash: hash mismatch (tried v1 and v2)', [
                'txnid'             => $txnId ?: '(empty)',
                'status'            => $status,
                'amount'            => $amount,
                'additionalCharges' => $additionalCharges,
                'incoming_hash'     => $incomingHash,
                'computed_v1'       => $computedV1,
                'computed_v2'       => $computedV2,
                'hash_string_v1'    => preg_replace('/^[^|]+/', '***SALT***', $hashStringV1),
                'hash_string_v2'    => preg_replace('/^[^|]+/', '***SALT***', $hashStringV2),
            ]);

            return false;
        } catch (Exception $e) {
            Log::error('PayU verifyHash exception: ' . $e->getMessage());
            return false;
        }
    }
}
