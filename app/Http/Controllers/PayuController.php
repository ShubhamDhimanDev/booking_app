<?php


namespace App\Http\Controllers;


use Illuminate\Http\Request;


class PayuController extends Controller
{
    public function paymentForm()
    {
        $url = "https://test.payu.in/_payment";


        $posted = [
            'key' => env('MERCHANT_KEY'),
            'txnid' => 'Txn' . uniqid(),
            'amount' => '1000',
            'firstname' => 'PayU User',
            'email' => 'test@gmail.com',
            'phone' => '9876543210',
            'productinfo' => 'iPhone',
            'surl' => route('payu.success'),
            'furl' => route('payu.failure'),
            'service_provider' => 'payu_paisa',
            'mid' => env('MERCHANT_ID'),
        ];



        $salt = env('MERCHANT_SALT');
        $posted['hash'] = $this->generateHash($posted, $salt);
        $posted['action'] = $url;


        return view('payu.redirect', compact('posted'));
    }


    public function paymentSuccess(Request $request)
    {
        return "✅ Payment Successful! <br> Transaction ID: " . $request->txnid;
    }


    public function paymentFailure(Request $request)
    {
        return "❌ Payment Failed!";
    }


    public function generateHash($params, $salt)
    {
        $key = $params['key'];
        $txnid = $params['txnid'];
        $amount = $params['amount'];
        $productinfo = $params['productinfo'];
        $firstname = $params['firstname'];
        $email = $params['email'];
        $udf1 = $params['udf1'] ?? '';
        $udf2 = $params['udf2'] ?? '';
        $udf3 = $params['udf3'] ?? '';
        $udf4 = $params['udf4'] ?? '';
        $udf5 = $params['udf5'] ?? '';


        $hashString = $key . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' .
            $firstname . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' .
            $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $salt;


        return strtolower(hash('sha512', $hashString));
    }
}
