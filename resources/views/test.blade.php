<?php
function generateHash($params, $salt) {
    $key = $params['key'];
    $txnid = $params['txnid'];
    $amount = $params['amount'];
    $productinfo = $params['productinfo'];
    $firstname = $params['firstname'];
    $email = $params['email'];

    // PayU formula: key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT
    // That's 11 pipes between email and salt (5 UDF fields + 6 empty fields)
    $hashString = $key . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' .
                  $firstname . '|' . $email . '|||||||||||' . $salt;

    $hash_v1 = strtolower(hash('sha512', $hashString));

    return $hash_v1;
}

$key = "4w0ISl";
$salt = "OI8aVp5h0RywG0fnb9C5Zx2hmOsrZGtj";

$params = [
    'key' => $key,
    'txnid' => 'txn12345',
    'amount' => '100.00',
    'productinfo' => 'iphone',
    'firstname' => 'John',
    'email' => 'john@example.com',
];

$hash = generateHash($params, $salt);
?>

<form action="https://test.payu.in/_payment" method="POST">
    <input type="hidden" name="txnid" value="txn12345">
    <input type="hidden" name="amount" value="100.00">
    <input type="hidden" name="key" value="4w0ISl">
    <input type="hidden" name="productinfo" value="iphone">
    <input type="hidden" name="firstname" value="John">
    <input type="hidden" name="email" value="john@example.com">
    <input type="hidden" name="phone" value="9999999999">
    <input type="hidden" name="service_provider" value="payu_paisa">
    <input type="hidden" name="hash" value='<?= $hash ?>'>
    <input type="hidden" name="surl" value="{{ route('payment.thankyou',['booking'=>7]) }}">
    <input type="hidden" name="furl" value="https://yourdomain.com/payment/failure">

    <button type="submit">Submit</button>
</form>
