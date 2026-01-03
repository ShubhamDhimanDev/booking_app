<!DOCTYPE html>
<html>
<head>
    <title>Razorpay Refund Test</title>
    <style>
        body { font-family: Arial; padding: 40px; }
        input, button { padding: 10px; margin: 5px 0; width: 300px; }
        pre { background: #f4f4f4; padding: 15px; }
    </style>
</head>
<body>

<h2>Razorpay Refund Test</h2>

@if(session('success'))
    <p style="color:green">{{ session('success') }}</p>
@endif

@if($errors->any())
    <p style="color:red">{{ $errors->first() }}</p>
@endif

<form method="POST" action="{{ route('razorpay.refund') }}">
    @csrf

    <label>Payment ID</label><br>
    <input type="text" name="payment_id" placeholder="pay_xxxxxxxxxx" required>

    <br>

    <label>Refund Amount (INR)</label><br>
    <input type="number" name="amount" placeholder="Leave empty for full refund">

    <br><br>

    <button type="submit">Process Refund</button>
</form>

@if(session('refund'))
    <h3>Refund Response</h3>
    <pre>{{ session('refund') }}</pre>
@endif

</body>
</html>
