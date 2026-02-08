<!DOCTYPE html>











<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>PayU Payment Test</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 40px;
      max-width: 600px;
      margin: 0 auto;
    }
    button {
      background: #007bff;
      color: white;
      padding: 12px 24px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 16px;
    }
    button:hover {
      background: #0056b3;
    }
    button:disabled {
      background: #ccc;
      cursor: not-allowed;
    }
    #status {
      margin-top: 20px;
      padding: 10px;
      border-radius: 4px;
    }
    .loading { background: #fff3cd; color: #856404; }
    .error { background: #f8d7da; color: #721c24; }
  </style>
</head>
<body>
  <h1>PayU Payment Test</h1>
  <p>This will initiate a test payment using PayUService</p>
  <button id="payBtn" onclick="submitPayment()">Submit Payment</button>
  <div id="status"></div>
</body>
<script>
    // PayU Handler
    function handlePayUPayment(data) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = data.payu_url || 'https://test.payu.in/_payment';

        for (const key in data) {
            if (key !== 'gateway' && key !== 'payu_url' && key !== 'success' && data[key] !== null) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = data[key];
                form.appendChild(input);
            }
        }

        document.body.appendChild(form);
        form.submit();
    }

    // Submit payment function
    async function submitPayment() {
        const payBtn = document.getElementById('payBtn');
        const statusDiv = document.getElementById('status');

        payBtn.disabled = true;
        statusDiv.className = 'loading';
        statusDiv.textContent = 'Initiating payment...';

        try {
            const response = await fetch('{{ route('test.payu.initiate') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            const data = await response.json();

            if (data.success) {
                statusDiv.textContent = 'Redirecting to PayU...';
                console.log('Payment Data:', data);
                handlePayUPayment(data);
            } else {
                statusDiv.className = 'error';
                statusDiv.textContent = 'Error: ' + (data.error || 'Failed to initiate payment');
                payBtn.disabled = false;
            }
        } catch (error) {
            statusDiv.className = 'error';
            statusDiv.textContent = 'Error: ' + error.message;
            payBtn.disabled = false;
            console.error('Payment initiation error:', error);
        }
    }
</script>
</html>
