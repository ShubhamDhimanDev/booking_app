# Complete Payment Flow Documentation

## System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                        NATEGA BOOKING SYSTEM                    │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌──────────────────────┐        ┌──────────────────────┐      │
│  │   PUBLIC BOOKING     │        │   ADMIN PANEL        │      │
│  │   (book-event.php)   │        │  (Payment Settings)  │      │
│  └──────────┬───────────┘        └──────────┬───────────┘      │
│             │                                │                  │
│             └────────────┬───────────────────┘                  │
│                          │                                      │
│                   ┌──────▼──────┐                               │
│                   │ PaymentCtrl  │                              │
│                   │ /create-order│                              │
│                   └──────┬───────┘                              │
│                          │                                      │
│              ┌───────────▼───────────┐                          │
│              │ PaymentGatewayManager │                          │
│              │ getActiveGateway()    │                          │
│              └───────────┬───────────┘                          │
│                          │                                      │
│         ┌────────────────┼────────────────┐                     │
│         │                │                │                     │
│    ┌────▼────┐      ┌───▼────┐      ┌───▼────┐                │
│    │Razorpay │      │ PayU   │      │ Stripe │  (future)      │
│    │Service  │      │Service │      │Service │                │
│    └────┬────┘      └────┬───┘      └───┬────┘                │
│         │                │                │                     │
│         └────────────────┼────────────────┘                     │
│                          │                                      │
│              ┌───────────▼───────────┐                          │
│              │  SETTINGS TABLE       │                          │
│              │ (Encrypted Creds)     │                          │
│              └───────────────────────┘                          │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

## Admin Configuration Flow

```
START
  │
  ├─→ Admin clicks Settings → Payment Gateway
  │
  ├─→ GET /admin/payment-gateway
  │     └─→ PaymentGatewayController@edit
  │         └─→ Load current gateway + settings from DB
  │             └─→ Display form with all gateway options
  │
  ├─→ Admin selects "PayU" + enters credentials
  │
  ├─→ Admin clicks "Save Settings"
  │
  └─→ PUT /admin/payment-gateway
        └─→ PaymentGatewayController@update
            ├─→ Validate input
            ├─→ Setting::setSetting('payment_gateway', 'payu', false)
            ├─→ Setting::setSetting('payu_merchant_key', 'xxxxx', true)
            │    └─→ Encrypts using Crypt::encrypt()
            ├─→ Setting::setSetting('payu_merchant_salt', 'xxxxx', true)
            │    └─→ Encrypts using Crypt::encrypt()
            └─→ Redirect with success message
                └─→ Credentials now stored encrypted in database
```

## Public Booking Payment Flow

```
STEP 1: VISITOR BROWSING
  │
  ├─→ GET /e/{event:slug}
  │
  └─→ EventController@showPublic
        ├─→ Load event with available slots
        ├─→ $paymentGatewayManager = app(PaymentGatewayManager::class)
        ├─→ $gatewayConfig = $paymentGatewayManager->getActiveGatewayConfig()
        ├─→ Pass $activeGateway = 'payu' (or 'razorpay')
        │    └─→ Passes to view: book-event.blade.php
        └─→ Render page
              └─→ JavaScript receives: activeGateway = 'payu'
                   └─→ Loads PayU SDK


STEP 2: BOOKER FILLS DETAILS
  │
  ├─→ Booker selects date/time
  ├─→ Enters name, email, phone
  ├─→ Clicks "Schedule Meeting"
  │
  └─→ JavaScript event handler:
        ├─→ showLoader()
        ├─→ POST /e/{slug}/book
        │    └─→ BookingController@store
        │         ├─→ Create booking record (status: pending)
        │         ├─→ Return booking ID
        │         └─→ Response: { id: 123, ... }
        │
        └─→ Store booking ID for payment verification


STEP 3: PAYMENT ORDER CREATION
  │
  ├─→ JavaScript calls POST /create-order
  │
  └─→ PaymentController@createOrder(PaymentGatewayManager $manager)
        ├─→ $gateway = $manager->getActiveGateway()
        │    └─→ Queries Setting table for 'payment_gateway' key
        │         └─→ Returns PayUService instance
        │
        ├─→ $response = $gateway->initiatePayment([...])
        │    └─→ PayUService@initiatePayment:
        │         ├─→ Load credentials from encrypted settings
        │         │    - Crypt::decrypt(settings['payu_merchant_key'])
        │         │    - Crypt::decrypt(settings['payu_merchant_salt'])
        │         ├─→ Generate hash: sha512(merchantKey|txnid|amount|...)
        │         ├─→ Return PayU gateway data
        │         └─→ Response:
        │              {
        │                  gateway: "payu",
        │                  txn_id: "txn_1234567890",
        │                  merchant_key: "xxxxx",
        │                  hash: "sha512_hash",
        │                  payu_url: "https://test.payu.in/_payment"
        │              }
        │
        └─→ Return response to frontend


STEP 4: PAYMENT GATEWAY HANDLING
  │
  ├─→ JavaScript receives order response
  │
  ├─→ if (activeGateway === 'payu')
  │       └─→ handlePayUPayment(orderData, bookingId)
  │            ├─→ Create hidden form with PayU params
  │            ├─→ Include hash, merchant_key, txn_id, amount
  │            ├─→ Set surl (success URL) = /payu/success
  │            ├─→ Set furl (failure URL) = /payu/failure
  │            └─→ form.submit() → Redirects to PayU gateway
  │
  └─→ else if (activeGateway === 'razorpay')
        └─→ handleRazorpayPayment(orderData, bookingId)
             ├─→ Initialize Razorpay checkout modal
             ├─→ Pass order_id, key, amount
             ├─→ Modal appears for card/wallet entry
             └─→ On success → Call /verify-payment


STEP 5: PAYMENT VERIFICATION
  │
  ├─→ (Razorpay) User completes payment → verification via /verify-payment
  │   (PayU) User completes payment → redirected to /payu/success
  │
  └─→ PaymentController@verifyPayment
        ├─→ Check if payment already processed (prevent duplicates)
        ├─→ $gateway = $manager->getActiveGateway()
        │
        ├─→ $verified = $gateway->verifyPayment($payload)
        │    └─→ If PayU:
        │         ├─→ Load merchant salt from encrypted settings
        │         ├─→ Regenerate hash with same formula
        │         ├─→ Compare: sent hash === regenerated hash
        │         └─→ Return true/false
        │
        │    └─→ If Razorpay:
        │         ├─→ Use Razorpay SDK to verify signature
        │         └─→ Return true/false
        │
        ├─→ If verified:
        │    ├─→ PaymentModel::create([
        │    │      booking_id: 123,
        │    │      gateway: 'payu',
        │    │      status: 'success'
        │    │   ])
        │    ├─→ Booking::update([status: 'confirmed'])
        │    └─→ Return { success: true }
        │
        └─→ If verification failed:
             └─→ Return { success: false }


STEP 6: CONFIRMATION
  │
  ├─→ Frontend receives verification response
  │
  ├─→ If success:
  │    ├─→ Hide payment UI
  │    ├─→ Show confirmation screen
  │    └─→ Display: "Meeting scheduled for 2025-12-25 at 2:00 PM"
  │
  └─→ If failed:
       └─→ Alert: "Payment verification failed. Please try again."

END
```

## Data Flow Diagram: Encrypted Credentials

```
STORING CREDENTIALS:
  │
  ├─→ Admin enters: "payu_merchant_key" = "8765432100"
  │
  └─→ Form submitted to PaymentGatewayController@update
       │
       ├─→ Setting::setSetting('payu_merchant_key', '8765432100', true)
       │    └─→ Parameters: key='payu_merchant_key', value='8765432100', encrypt=true
       │
       ├─→ if (encrypt):
       │    └─→ $encryptedValue = Crypt::encrypt('8765432100')
       │         └─→ Results in: "eyJpdiI6IkdjUkl1RE5vKzlSRVdsR1..."  (random each time)
       │
       └─→ Database SETTINGS table:
            ┌─────────────────────────────────────────┐
            │ id │ key                  │ value        │ is_encrypted │
            ├─────────────────────────────────────────┤
            │ 1  │ payu_merchant_key    │ eyJpdiI6I... │ 1            │
            └─────────────────────────────────────────┘


RETRIEVING CREDENTIALS:
  │
  ├─→ PayUService@__construct()
  │    │
  │    ├─→ Setting::getSetting('payu_merchant_key')
  │    │
  │    └─→ Query database:
  │         SELECT value, is_encrypted FROM settings WHERE key='payu_merchant_key'
  │
  ├─→ if (is_encrypted):
  │    └─→ $decryptedValue = Crypt::decrypt('eyJpdiI6IkdjUkl1RE5vKzlSRVdsR1...')
  │         └─→ Returns: "8765432100"
  │
  └─→ PayUService now has credentials ready to use
       └─→ Generate hash with the key → Complete payment processing
```

## Environment Variable Fallback

```
SCENARIO 1: Credentials in Database (Preferred)
  │
  ├─→ PayUService@__construct()
  │    │
  │    ├─→ $key = Setting::getSetting('payu_merchant_key')
  │    ├─→ if (!$key):
  │    │    └─→ $key = env('PAYU_MERCHANT_KEY')
  │    │
  │    └─→ Use $key for operations
  │

SCENARIO 2: Credentials in .env (Fallback)
  │
  ├─→ Setting::getSetting('payu_merchant_key') returns null
  │
  └─→ Fall back to env('PAYU_MERCHANT_KEY') from .env file
       └─→ Use for operations (unencrypted, warning in docs)


PRIORITY ORDER:
  1. Database Settings (encrypted) - PREFERRED
  2. Environment Variables (.env) - FALLBACK
  3. NULL (error if not found)
```

## Gateway Addition Process

```
To add Stripe:

STEP 1: Create Service
  └─→ app/Services/StripeService.php
       implements PaymentGatewayInterface
       {
           getName(): string { return 'stripe'; }
           getPublicConfig(): array { return [...]; }
           initiatePayment(array $data): array { return [...]; }
           verifyPayment(array $payload): bool { return [...]; }
       }

STEP 2: Register Gateway
  └─→ app/Services/PaymentGatewayManager.php
       protected $gateways = [
           'razorpay' => RazorpayService::class,
           'payu' => PayUService::class,
           'stripe' => StripeService::class,  // ADD THIS
       ];

STEP 3: Update Admin Form
  └─→ resources/views/admin/payment_gateway/edit.blade.php
       Add section:
       <div class="mb-8 p-4 bg-gray-50 rounded border border-gray-200">
           <h3>Stripe Configuration</h3>
           <input name="stripe_api_key" ... />
           <input name="stripe_secret_key" ... />
       </div>

STEP 4: Update Admin Controller
  └─→ app/Http/Controllers/Admin/PaymentGatewayController.php
       Add validation:
       'stripe_api_key' => 'nullable|string',
       'stripe_secret_key' => 'nullable|string',

       Add storage:
       if ($request->filled('stripe_api_key')) {
           Setting::setSetting('stripe_api_key', $request->stripe_api_key, true);
       }
       if ($request->filled('stripe_secret_key')) {
           Setting::setSetting('stripe_secret_key', $request->stripe_secret_key, true);
       }

STEP 5: Update Frontend
  └─→ resources/views/book-event.blade.php
       Add SDK: <script src="https://js.stripe.com/v3/"></script>
       Add handler:
       async function handleStripePayment(orderData, bookingId) {
           // Initialize Stripe and show payment element
       }
       Add routing:
       else if (activeGateway === 'stripe') {
           handleStripePayment(orderData, bookingId);
       }

DONE! Stripe is now available.
```

This documentation provides complete understanding of how the system works, from admin configuration through to payment verification.
