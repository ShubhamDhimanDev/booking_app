<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentGatewayManager;
use Exception;
use Notification;
use App\Models\Booking;
use App\Models\Payment as PaymentModel;
use App\Models\PromoCode;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BookingController;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function createOrder(Request $request, PaymentGatewayManager $gatewayManager)
    {
        $amount = floatval($request->amount ?? 500);
        $bookingId = $request->booking_id ?? null;

        try {
            $gateway = $gatewayManager->getActiveGateway();
            $gatewayName = $gateway->getName();

            $paymentData = [
                'amount' => $amount,
                'receipt' => 'order_' . time(),
                'booking_id' => $bookingId,
                'product_info' => $request->product_info ?? 'Booking Payment',
                'first_name' => $request->first_name ?? '',
                'email' => $request->email ?? '',
                'txn_id' => 'txn_' . time() . rand(1000, 9999),
                'phone' => $request->phone ?? '',
            ];

            $response = $gateway->initiatePayment($paymentData);

            // Add gateway name to response for frontend to determine which handler to use
            $response['gateway'] = strtolower($gatewayName);

            return response()->json($response);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function verifyPayment(Request $request, PaymentGatewayManager $gatewayManager)
    {
        $bookingId = $request->booking_id ?? null;
        $signatureStatus = false;

        if ($bookingId) {
            $already = PaymentModel::where('booking_id', $bookingId)->where('status', 'success')->exists();
            if ($already) {
                return response()->json(['success' => true, 'message' => 'already_processed']);
            }
        }

        try {
            $gateway = $gatewayManager->getActiveGateway();
            $gatewayName = $gateway->getName();

            // Verify payment signature with the gateway
            $signatureStatus = $gateway->verifyPayment($request->all());

            if ($signatureStatus && $bookingId) {
                $booking = Booking::with(['event.user'])->find($bookingId);
                if (!$booking) {
                    Log::error('Booking not found for ID: ' . $bookingId);
                    return response()->json(['success' => false, 'message' => 'booking_not_found'], 404);
                }

                $already = PaymentModel::where('booking_id', $booking->id)->where('status', 'success')->exists();
                if ($already) {
                    return response()->json(['success' => true, 'message' => 'already_processed']);
                }

                try {
                    // Extract transaction ID based on gateway
                    // Razorpay: razorpay_payment_id, PayU: txnid
                    $transactionId = $request->razorpay_payment_id ?? $request->txnid ?? $request->order_id ?? null;

                    if (!$transactionId) {
                        Log::warning('No transaction ID found in verify-payment request for booking: ' . $bookingId);
                    }

                    PaymentModel::create([
                        'user_id' => $booking->user_id,
                        'booking_id' => $booking->id,
                        'provider' => $gatewayName,
                        'transaction_id' => $transactionId,
                        'status' => 'success',
                        'amount' => $request->amount ?? 0,
                        'currency' => 'INR',
                        'promo_code' => $request->promo_code ?? null,
                        'metadata' => json_encode($request->all()),
                    ]);

                    $booking->update(['status' => 'confirmed']);

                    // Create google calendar event and update booking
                    try {
                        $bookingController = app(BookingController::class);
                        $calendarEvent = $bookingController->createGoogleEvent(
                            $booking->event,
                            $booking->booked_at_date,
                            $booking->booked_at_time,
                            $booking->booker_name,
                            $booking->booker_email
                        );

                        $booking->update([
                            'calendar_id' => $calendarEvent['calendar_id'] ?? null,
                            'calendar_link' => $calendarEvent['calendar_link'] ?? null,
                            'meet_link' => $calendarEvent['meet_link'] ?? null,
                            'status' => 'confirmed',
                        ]);

                        // Refresh booking with relationships for notification
                        $booking->refresh();
                        $booking->load(['event.user']);

                        // Notify owner and booker
                        $booking->event->user->notify(new \App\Notifications\BookingCreatedNotification($booking));
                        Notification::route('mail', [$booking->booker_email => $booking->booker_name])
                            ->notify(new \App\Notifications\BookingCreatedNotification($booking));
                    } catch (Exception $e) {
                        Log::error('Finalize booking google event failed: ' . $e->getMessage(), ['booking_id' => $bookingId, 'exception' => $e]);
                        // Don't fail the payment verification if calendar event creation fails
                    }
                } catch (Exception $e) {
                    Log::error('Persist payment failed: ' . $e->getMessage(), ['booking_id' => $bookingId, 'exception' => $e]);
                    return response()->json(['success' => false, 'message' => 'payment_persist_failed', 'error' => $e->getMessage()], 500);
                }
            }

        } catch (Exception $e) {
            Log::error('verifyPayment error: ' . $e->getMessage(), ['booking_id' => $bookingId, 'exception' => $e]);
            $signatureStatus = false;
        }

        return response()->json(['success' => $signatureStatus]);
    }

    public function showPaymentPage(Request $request, $booking)
    {
        $bookingModel = Booking::with(['event.user', 'booker', 'payment'])->findOrFail($booking);

        return view('payments.show', [
            'booking' => $bookingModel,
        ]);
    }

    public function thankYouPage($booking)
    {
        $bookingModel = Booking::with(['event.user', 'booker', 'payment'])->findOrFail($booking);

        return view('payments.thankyou', [
            'booking' => $bookingModel,
        ]);
    }

    public function validatePromoCode(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string',
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:0'
        ]);

        $code = strtoupper($request->promo_code);
        $bookingId = $request->booking_id;
        $originalAmount = floatval($request->amount);

        // Find the promo code
        $promoCode = PromoCode::where('code', $code)->first();

        if (!$promoCode) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid promo code'
            ], 404);
        }

        // Check if active
        if (!$promoCode->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This promo code is no longer active'
            ], 400);
        }

        // Check validity dates
        $now = Carbon::now();

        if ($promoCode->valid_from && $now->lt(Carbon::parse($promoCode->valid_from))) {
            return response()->json([
                'success' => false,
                'message' => 'This promo code is not yet valid'
            ], 400);
        }

        if ($promoCode->valid_until && $now->gt(Carbon::parse($promoCode->valid_until))) {
            return response()->json([
                'success' => false,
                'message' => 'This promo code has expired'
            ], 400);
        }

        // Check minimum booking amount
        if ($promoCode->min_booking_amount && $originalAmount < $promoCode->min_booking_amount) {
            return response()->json([
                'success' => false,
                'message' => "Minimum booking amount of ₹{$promoCode->min_booking_amount} required"
            ], 400);
        }

        // Check usage limit
        if ($promoCode->usage_limit) {
            $usageCount = PaymentModel::where('promo_code', $code)
                ->where('status', 'success')
                ->count();

            if ($usageCount >= $promoCode->usage_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'This promo code has reached its usage limit'
                ], 400);
            }
        }

        // Check if this booking already used a promo code
        $booking = Booking::findOrFail($bookingId);
        if ($booking->payment && $booking->payment->promo_code) {
            return response()->json([
                'success' => false,
                'message' => 'A promo code has already been applied to this booking'
            ], 400);
        }

        // Calculate discount
        $discountValue = 0;

        if ($promoCode->discount_type === 'percentage') {
            $discountValue = ($originalAmount * $promoCode->discount_value) / 100;

            // Apply max discount cap if set
            if ($promoCode->max_discount_amount && $discountValue > $promoCode->max_discount_amount) {
                $discountValue = $promoCode->max_discount_amount;
            }
        } else {
            // Fixed discount
            $discountValue = min($promoCode->discount_value, $originalAmount);
        }

        $discountedAmount = max(0, $originalAmount - $discountValue);

        return response()->json([
            'success' => true,
            'message' => "Promo code applied successfully! You saved ₹{$discountValue}",
            'promo_code' => $code,
            'discount_type' => $promoCode->discount_type,
            'discount_value' => round($discountValue, 2),
            'original_amount' => round($originalAmount, 2),
            'discounted_amount' => round($discountedAmount, 2)
        ]);
    }
}
