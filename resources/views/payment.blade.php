<!DOCTYPE html>
<html>
<head>
    <title>Razorpay Payment</title>
    <!-- Add CSRF Meta -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <button id="rzp-button1">Pay ₹500</button>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        document.getElementById('rzp-button1').onclick = async function(e){
            e.preventDefault();

            // Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            // Create order with CSRF token in headers
            const response = await fetch('/create-order', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ amount: 500 }) // you can also pass dynamic amount
            });

            const data = await response.json();

            const options = {
                "key": data.key,
                "amount": data.amount * 100,
                "currency": "INR",
                "name": "My App",
                "description": "Test Transaction",
                "order_id": data.order_id,
                "handler": async function (response){
                    const verifyRes = await fetch('/verify-payment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify(response)
                    });
                    const verifyData = await verifyRes.json();
                    alert(verifyData.success ? "Payment Success" : "Payment Verification Failed");
                },
                "theme": { "color": "#3399cc" }
            };

            const rzp1 = new Razorpay(options);
            rzp1.open();
        }
    </script>
</body>
</html>
