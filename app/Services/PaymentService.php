<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\FollowUpInvite;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * PaymentService - Handles all payment business logic
 *
 * Responsibilities:
 * - Payment gateway interactions
 * - Payment verification
 * - Promo code validation
 * - Free booking processing
 */
class PaymentService
{
    protected PaymentGatewayManager $gatewayManager;
    protected BookingService $bookingService;

    public function __construct(
        PaymentGatewayManager $gatewayManager,
        BookingService $bookingService
    ) {
        $this->gatewayManager = $gatewayManager;
        $this->bookingService = $bookingService;
    }

    /**
     * Create payment order
     *
     * @param float $amount
     * @param int|null $bookingId
     * @param string|null $promoCode
     * @param array $additionalData
     * @return array
     * @throws Exception
     */
    public function createPaymentOrder(
        float $amount,
        ?int $bookingId = null,
        ?string $promoCode = null,
        array $additionalData = []
    ): array {
        // Handle free bookings
        if ($amount == 0 && $bookingId) {
            return $this->processFreeBooking($bookingId, $promoCode);
        }

        $gateway = $this->gatewayManager->getActiveGateway();

        $paymentData = array_merge([
            'amount' => $amount,
            'receipt' => 'order_' . time(),
            'booking_id' => $bookingId,
            'promo_code' => $promoCode,
            'txn_id' => 'txn_' . time() . rand(1000, 9999),
        ], $additionalData);

        $response = $gateway->initiatePayment($paymentData);
        $response['gateway'] = strtolower($gateway->getName());

        return $response;
    }

    /**
     * Process free booking (100% discount)
     *
     * @param int $bookingId
     * @param string|null $promoCode
     * @return array
     * @throws Exception
     */
    public function processFreeBooking(int $bookingId, ?string $promoCode = null): array
    {
        $booking = Booking::with(['event.user'])->findOrFail($bookingId);

        // Check if already processed
        if ($booking->payment()->successful()->exists()) {
            return [
                'success' => true,
                'free_booking' => true,
                'already_processed' => true,
                'booking_id' => $bookingId
            ];
        }

        $booking = DB::transaction(function () use ($booking, $promoCode) {
            // Create payment record
            Payment::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'provider' => 'free',
                'transaction_id' => 'FREE_' . time() . rand(1000, 9999),
                'status' => Payment::STATUS_COMPLETED,
                'amount' => 0,
                'currency' => 'INR',
                'promo_code' => $promoCode,
                'metadata' => json_encode(['type' => 'free_booking', 'promo_code' => $promoCode]),
            ]);

            $booking->update(['status' => Booking::STATUS_CONFIRMED]);

            // Mark follow-up invite as accepted
            if ($booking->is_followup && $booking->followUpInvite) {
                $booking->followUpInvite->update(['status' => FollowUpInvite::STATUS_ACCEPTED]);
            }

            return $booking->fresh();
        });

        // Create calendar synchronously (outside transaction)
        $this->bookingService->createCalendarEventForBooking($booking);

        // Dispatch payment processed event for notifications
        event(new \App\Events\PaymentProcessed($booking));

        Log::info('Free booking processed', ['booking_id' => $booking->id]);

        return [
            'success' => true,
            'free_booking' => true,
            'booking_id' => $booking->id,
            'message' => 'Booking confirmed successfully!'
        ];
    }

    /**
     * Verify payment and confirm booking
     *
     * @param array $paymentData
     * @param int $bookingId
     * @return array
     * @throws Exception
     */
    public function verifyAndConfirmPayment(array $paymentData, int $bookingId): array
    {
        $booking = Booking::with(['event.user'])->findOrFail($bookingId);

        // Check if already processed
        if ($booking->payment()->successful()->exists()) {
            return ['success' => true, 'message' => 'already_processed'];
        }

        $gateway = $this->gatewayManager->getActiveGateway();
        $verified = $gateway->verifyPayment($paymentData);

        if (!$verified) {
            throw new Exception('Payment verification failed');
        }

        $transactionResult = DB::transaction(function () use ($booking, $paymentData, $gateway) {
            // Extract transaction ID based on gateway
            $transactionId = $paymentData['razorpay_payment_id']
                ?? $paymentData['mihpayid']
                ?? $paymentData['order_id']
                ?? null;

            // Create payment record
            Payment::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'provider' => $gateway->getName(),
                'transaction_id' => $transactionId,
                'status' => Payment::STATUS_COMPLETED,
                'amount' => $paymentData['amount'] ?? 0,
                'currency' => 'INR',
                'promo_code' => $paymentData['promo_code'] ?? null,
                'metadata' => json_encode($paymentData),
            ]);

            $booking->update(['status' => Booking::STATUS_CONFIRMED]);

            // Mark follow-up invite as accepted
            if ($booking->is_followup && $booking->followUpInvite) {
                $booking->followUpInvite->update(['status' => FollowUpInvite::STATUS_ACCEPTED]);
            }

            return [
                'booking' => $booking->fresh(),
                'transaction_id' => $transactionId
            ];
        });

        // Get the result from transaction
        $result = $transactionResult;
        $booking = $result['booking'];
        $transactionId = $result['transaction_id'];

        // Create calendar synchronously (outside transaction)
        $this->bookingService->createCalendarEventForBooking($booking);

        // Dispatch payment processed event for notifications
        event(new \App\Events\PaymentProcessed($booking));

        Log::info('Payment verified and booking confirmed', [
            'booking_id' => $booking->id,
            'transaction_id' => $transactionId
        ]);

        return [
            'success' => true,
            'booking' => $booking,
            'transaction_id' => $transactionId
        ];
    }

    /**
     * Validate promo code
     *
     * @param string $code
     * @param int $bookingId
     * @param float $amount
     * @return array
     * @throws Exception
     */
    public function validatePromoCode(string $code, int $bookingId, float $amount): array
    {
        $promoCode = PromoCode::byCode($code)->first();

        if (!$promoCode) {
            throw new Exception('Invalid promo code');
        }

        if (!$promoCode->isValid()) {
            throw new Exception('This promo code is no longer active or has expired');
        }

        // Check minimum booking amount
        if ($promoCode->min_booking_amount && $amount < $promoCode->min_booking_amount) {
            throw new Exception("Minimum booking amount of ₹{$promoCode->min_booking_amount} required");
        }

        // Check usage limit
        if ($promoCode->usage_limit) {
            $usageCount = Payment::where('promo_code', strtoupper($code))
                ->successful()
                ->count();

            if ($usageCount >= $promoCode->usage_limit) {
                throw new Exception('This promo code has reached its usage limit');
            }
        }

        // Check if booking already used a promo code
        $booking = Booking::findOrFail($bookingId);
        if ($booking->payment && $booking->payment->promo_code) {
            throw new Exception('A promo code has already been applied to this booking');
        }

        // Calculate discount
        $discountValue = $this->calculateDiscount($promoCode, $amount);
        $discountedAmount = max(0, $amount - $discountValue);

        return [
            'success' => true,
            'message' => "Promo code applied successfully! You saved ₹{$discountValue}",
            'promo_code' => strtoupper($code),
            'discount_type' => $promoCode->discount_type,
            'discount_value' => round($discountValue, 2),
            'original_amount' => round($amount, 2),
            'discounted_amount' => round($discountedAmount, 2)
        ];
    }

    /**
     * Calculate discount amount
     *
     * @param PromoCode $promoCode
     * @param float $amount
     * @return float
     */
    protected function calculateDiscount(PromoCode $promoCode, float $amount): float
    {
        if ($promoCode->discount_type === 'percentage') {
            $discount = ($amount * $promoCode->discount_value) / 100;

            // Apply max discount cap if set
            if ($promoCode->max_discount_amount && $discount > $promoCode->max_discount_amount) {
                $discount = $promoCode->max_discount_amount;
            }

            return $discount;
        }

        // Fixed discount
        return min($promoCode->discount_value, $amount);
    }

    /**
     * Record failed payment
     *
     * @param int $bookingId
     * @param array $paymentData
     * @param string $errorMessage
     */
    public function recordFailedPayment(int $bookingId, array $paymentData, string $errorMessage): void
    {
        try {
            $booking = Booking::findOrFail($bookingId);

            Payment::create([
                'user_id' => $booking->user_id,
                'booking_id' => $booking->id,
                'provider' => $paymentData['provider'] ?? 'unknown',
                'transaction_id' => $paymentData['transaction_id'] ?? null,
                'status' => Payment::STATUS_FAILED,
                'amount' => $paymentData['amount'] ?? 0,
                'currency' => 'INR',
                'metadata' => json_encode(array_merge($paymentData, ['error' => $errorMessage])),
            ]);

            Log::warning('Payment failed recorded', [
                'booking_id' => $bookingId,
                'error' => $errorMessage
            ]);
        } catch (Exception $e) {
            Log::error('Failed to record failed payment', [
                'booking_id' => $bookingId,
                'exception' => $e->getMessage()
            ]);
        }
    }
}
