<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentService;
use App\Services\PaymentGatewayManager;
use Exception;
use App\Models\Booking;
use App\Models\Payment as PaymentModel;
use Illuminate\Support\Facades\Log;

/**
 * PaymentController - Thin HTTP layer
 *
 * Delegates all business logic to PaymentService
 * Handles only HTTP concerns: requests, responses
 */
class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }
    /**
     * Create payment order
     */
    public function createOrder(Request $request, PaymentGatewayManager $gatewayManager)
    {
        $amount = floatval($request->amount ?? 500);
        $bookingId = $request->booking_id ?? null;
        $promoCode = $request->promo_code ?? null;

        try {
            // Free booking - use service
            if ($amount == 0 && $bookingId) {
                $result = $this->paymentService->processFreeBooking($bookingId, $promoCode);
                return response()->json($result);
            }

            // Create payment order
            $response = $this->paymentService->createPaymentOrder(
                $amount,
                $bookingId,
                $promoCode,
                $request->all()
            );

            return response()->json($response);

        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verify payment and confirm booking
     */
    public function verifyPayment(Request $request, PaymentGatewayManager $gatewayManager)
    {
        $bookingId = $request->booking_id ?? null;

        try {
            $result = $this->paymentService->verifyAndConfirmPayment($request->all(), $bookingId);
            return response()->json($result);

        } catch (Exception $e) {
            Log::error('verifyPayment error: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'exception' => $e
            ]);
            return response()->json(['success' => false]);
        }
    }

    public function showPaymentPage(Request $request, $booking)
    {
        $bookingModel = Booking::with(['event.user', 'booker', 'payment', 'followUpInvite'])->findOrFail($booking);

        // Determine the price - use custom price for follow-up bookings
        $price = $bookingModel->event->price ?? 500;

        if ($bookingModel->is_followup && $bookingModel->followUpInvite) {
            $price = $bookingModel->followUpInvite->custom_price;
        }

        return view('payments.show', [
            'booking' => $bookingModel,
            'customPrice' => $price,
        ]);
    }

    public function thankYouPage($booking)
    {
        $bookingModel = Booking::with(['event.user', 'booker', 'payment'])->findOrFail($booking);

        return view('payments.thankyou', [
            'booking' => $bookingModel,
        ]);
    }

    /**
     * Handle PayU payment callback
     */
    public function payuCallback(Request $request, PaymentGatewayManager $gatewayManager)
    {
        $bookingId = $request->udf1 ?? null;
        $status = $request->status ?? null;

        Log::info('PayU Callback Received', [
            'booking_id' => $bookingId,
            'status' => $status,
            'txnid' => $request->txnid ?? null,
            'amount' => $request->amount ?? null,
        ]);

        if (!$bookingId) {
            Log::error('PayU callback: No booking ID in UDF1');
            return redirect()->route('payment.failed')->with('error', 'Invalid payment data');
        }

        $booking = Booking::find($bookingId);
        if (!$booking) {
            Log::error('PayU callback: Booking not found', ['booking_id' => $bookingId]);
            return redirect()->route('payment.failed')->with('error', 'Booking not found');
        }

        // Check if already processed
        $existingPayment = PaymentModel::where('booking_id', $booking->id)
            ->where('status', Payment::STATUS_COMPLETED)
            ->exists();

        if ($existingPayment) {
            Log::info('PayU callback: Payment already processed', ['booking_id' => $bookingId]);
            return redirect()->route('payment.thankyou', ['booking' => $bookingId]);
        }

        try {
            $result = $this->paymentService->verifyAndConfirmPayment($request->all(), $bookingId);

            if ($result['success'] && strtolower($status) === 'success') {
                Log::info('PayU callback: Payment processed successfully', ['booking_id' => $bookingId]);
                return redirect()->route('payment.thankyou', ['booking' => $bookingId]);
            } else {
                Log::warning('PayU callback: Payment failed or verification failed', [
                    'booking_id' => $bookingId,
                    'status' => $status,
                    'message' => $request->error_Message ?? $request->field9 ?? 'Unknown error'
                ]);

                return redirect()->route('payment.failed', ['booking' => $bookingId])
                    ->with('error', $request->error_Message ?? $request->field9 ?? 'Payment verification failed');
            }
        } catch (Exception $e) {
            Log::error('PayU callback exception: ' . $e->getMessage(), [
                'booking_id' => $bookingId,
                'exception' => $e
            ]);
            return redirect()->route('payment.failed', ['booking' => $bookingId])
                ->with('error', 'An error occurred while processing your payment');
        }
    }

    public function paymentFailedPage(Request $request, $booking = null)
    {
        $bookingModel = null;
        if ($booking) {
            $bookingModel = Booking::with(['event.user', 'booker'])->find($booking);
        }

        $errorMessage = $request->session()->get('error', 'Payment was not successful');

        return view('payments.failed', [
            'booking' => $bookingModel,
            'errorMessage' => $errorMessage,
        ]);
    }

    /**
     * Validate promo code
     */
    public function validatePromoCode(Request $request)
    {
        $request->validate([
            'promo_code' => 'required|string',
            'booking_id' => 'required|exists:bookings,id',
            'amount' => 'required|numeric|min:0'
        ]);

        try {
            $result = $this->paymentService->validatePromoCode(
                $request->promo_code,
                $request->booking_id,
                floatval($request->amount)
            );

            return response()->json($result);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], $e->getCode() ?: 400);
        }
    }
}
