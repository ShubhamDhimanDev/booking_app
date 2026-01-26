<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PayUService;

class TestController extends Controller
{
  public function test()
  {
    return view('test.test');
  }

  public function initiateTestPayment(Request $request, PayUService $payuService)
  {
    $paymentData = $payuService->initiatePayment([
      'amount' => '100.00',
      'product_info' => 'Test Product - iPhone',
      'first_name' => 'John',
      'email' => 'john@example.com',
      'phone' => '9999999999',
      'txn_id' => 'TxnTest' . time(),
      'booking_id' => 7
    ]);

    return response()->json($paymentData);
  }

  public function verifyTestPayment(PayUService $payuService)
  {
    $payuResponse = [
      "country" => null,
      "udf10" => null,
      "mode" => "UPI",
      "error_Message" => "No Error",
      "state" => null,
      "bankcode" => null,
      "txnid" => "txn_17694416831862",
      "net_amount_debit" => "2.00",
      "lastname" => null,
      "zipcode" => null,
      "phone" => "918439275751",
      "productinfo" => "TEST EVENT",
      "hash" => "a47cc36a8ca37639c102949350eb0ec1ee34778dffacc5d001c12a16357085bd25003ba783843ff664deac04d28638eb121017e7a1644b41c8ddb015d1753197",
      "status" => "success",
      "firstname" => "Shubham Dhiman",
      "city" => null,
      "isConsentPayment" => null,
      "error" => "E000",
      "addedon" => "2026-01-26 21:04:43",
      "udf9" => null,
      "udf7" => null,
      "udf8" => null,
      "encryptedPaymentId" => "27017135229",
      "bank_ref_num" => "117738013396",
      "key" => "XMnsKF",
      "email" => "dhiman007shubham@gmail.com",
      "amount" => "2.00",
      "unmappedstatus" => "captured",
      "address2" => null,
      "payuMoneyId" => "27017135229",
      "address1" => null,
      "udf5" => null,
      "mihpayid" => "27017135229",
      "udf6" => null,
      "udf3" => null,
      "udf4" => null,
      "udf1" => "1",
      "udf2" => null,
      "giftCardIssued" => null,
      "field1" => null,
      "cardnum" => null,
      "field7" => "0|SUCCESS",
      "field6" => null,
      "field9" => "0|SUCCESS|Completed Using Callback",
      "field8" => "QR",
      "amount_split" => ["PAYU" => "2.00"],
      "field3" => "rockonshubh@okhdfcbank",
      "field2" => "PH601262144012279",
      "field5" => null,
      "PG_TYPE" => "UPI-PG",
      "field4" => null,
      "name_on_card" => null
    ];

    $isValid = $payuService->verifyPayment($payuResponse);

    return response()->json([
      'verification_result' => $isValid,
      'status' => $payuResponse['status'],
      'txnid' => $payuResponse['txnid'],
      'amount' => $payuResponse['amount'],
      'udf1' => $payuResponse['udf1'],
      'message' => $isValid ? 'Payment verified successfully' : 'Payment verification failed'
    ]);
  }
}

