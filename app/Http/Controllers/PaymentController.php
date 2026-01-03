<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PaymentGatewayManager;
use Exception;
use Notification;
use App\Models\Booking;
use App\Models\Payment as PaymentModel;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BookingController;

class PaymentController extends Controller
{
    public function createOrder(Request $request, PaymentGatewayManager $gatewayManager)
    {
        $amount = floatval($request->amount ?? 500);
        $bookingId = $request->booking_id ?? null;

        try {
            $gateway = $gatewayManager->getActiveGateway();
            $paymentData = [
                'amount' => $amount,
                'receipt' => 'order_' . time(),
                'booking_id' => $bookingId,
                'product_info' => $request->product_info ?? 'Booking Payment',
                'first_name' => $request->first_name ?? '',
                'email' => $request->email ?? '',
                'txn_id' => 'txn_' . time() . rand(1000, 9999),
            ];

            $response = $gateway->initiatePayment($paymentData);
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
                $booking = Booking::find($bookingId);
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
        $bookingModel = Booking::with('event.user')->findOrFail($booking);

        return view('payments.show', [
            'booking' => $bookingModel,
        ]);
    }

    public function thankYouPage($booking)
    {
        $bookingModel = Booking::with('event.user')->findOrFail($booking);

        return view('payments.thankyou', [
            'booking' => $bookingModel,
        ]);
    }
}
