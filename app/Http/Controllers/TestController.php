<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PayURefundService;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
  public function test(PayURefundService $refundService)
  {
    // Dummy refund details - UPDATE THESE WITH ACTUAL TRANSACTION DATA
    $transactionId = '27017446449'; // PayU mihpayid/transaction ID from successful payment
    $bankRefNum = '117738981194'; // bank_ref_num from payment response
    $refundAmount = 2.00; // Amount to refund (must be <= original payment amount)
    
    Log::info('Testing PayU Refund', [
      'transaction_id' => $transactionId,
      'bank_ref_num' => $bankRefNum,
      'amount' => $refundAmount
    ]);

    try {
      // Process refund
      $result = $refundService->processRefund($transactionId, $refundAmount, [
        'token' => $bankRefNum, // Bank reference number
      ]);

      Log::info('PayU Refund Result', $result);

      if ($result['success']) {
        return response()->json([
          'success' => true,
          'message' => 'Refund initiated successfully',
          'refund_id' => $result['refund_id'] ?? null,
          'amount' => $result['amount'] ?? $refundAmount,
          'status' => $result['status'] ?? 'initiated',
          'raw_response' => $result['raw_response'] ?? null,
        ]);
      } else {
        return response()->json([
          'success' => false,
          'message' => 'Refund failed',
          'error' => $result['error'] ?? 'Unknown error',
          'raw_response' => $result['raw_response'] ?? null,
        ], 400);
      }
    } catch (\Exception $e) {
      Log::error('PayU Refund Test Exception', [
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Exception occurred',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}
