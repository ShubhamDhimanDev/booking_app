# Payment Gateway Architecture - Multi-Gateway Support

**Last Updated**: February 8, 2026  
**Status**: ✅ Production Ready  
**Architecture**: Gateway-Agnostic Design Pattern

---

## 🎯 Overview

MeetFlow's payment gateway architecture is designed to support multiple payment providers without code changes. The system uses an interface-based approach that allows seamless integration of new payment gateways like Razorpay, Stripe, PayPal, and others.

### Key Benefits
- ✅ **Gateway Agnostic** - Not tied to any single payment provider
- ✅ **Easy Integration** - Add new gateways by implementing a simple interface
- ✅ **Flexible** - Organizations can use different payment gateways
- ✅ **Scalable** - Handle multiple gateways simultaneously
- ✅ **Maintainable** - Centralized gateway logic with clear separation of concerns

---

## 🏗️ Architecture Components

### 1. PaymentGatewayInterface

**Location**: `app/Contracts/PaymentGatewayInterface.php`

Defines the contract that all payment gateway implementations must follow:

```php
interface PaymentGatewayInterface
{
    public function getGatewayName(): string;
    public function createCustomer(Organization $organization): string;
    public function createOrGetPlan(SubscriptionPlan $plan, string $billingCycle): string;
    public function createSubscription(...): array;
    public function updateSubscription(...): array;
    public function cancelSubscription(...): array;
    public function resumeSubscription(...): array;
    public function getSubscription(string $gatewaySubscriptionId): array;
    public function verifyWebhookSignature(...): bool;
    public function parseWebhookEvent(array $payload): array;
    public function createPaymentIntent(...): array;
    public function refundPayment(...): array;
    public function getPayment(string $gatewayPaymentId): array;
}
```

### 2. Gateway Implementations

#### RazorpayGateway (Current)
**Location**: `app/Services/PaymentGateways/RazorpayGateway.php`

Implements `PaymentGatewayInterface` for Razorpay:
- Customer creation and management
- Subscription plan creation
- Subscription lifecycle (create, update, cancel, resume)
- Webhook signature verification (HMAC SHA256)
- Payment intents and refunds

#### Future Implementations
- **StripeGateway** - For Stripe integration
- **PayPalGateway** - For PayPal Subscriptions
- **PayUGateway** - For PayU integration

### 3. PaymentGatewayFactory

**Location**: `app/Services/PaymentGatewayFactory.php`

Factory pattern for instantiating the correct gateway:

```php
// Get gateway instance
$gateway = PaymentGatewayFactory::make('razorpay');

// Check if gateway is supported
if (PaymentGatewayFactory::isSupported('stripe')) {
    // Use stripe
}

// Get list of all supported gateways
$gateways = PaymentGatewayFactory::getSupportedGateways();
```

### 4. SubscriptionService (Gateway-Agnostic)

**Location**: `app/Services/SubscriptionService.php`

Uses `PaymentGatewayInterface` instead of direct gateway APIs:

```php
class SubscriptionService
{
    protected PaymentGatewayInterface $gateway;

    public function __construct(?string $gatewayName = null)
    {
        $gatewayName = $gatewayName ?? PaymentGatewayFactory::getDefaultGateway();
        $this->gateway = PaymentGatewayFactory::make($gatewayName);
    }

    // All methods use $this->gateway instead of Razorpay API directly
}
```

---

## 📊 Database Schema Changes

### Gateway-Agnostic Fields

#### Organizations Table
```php
'default_payment_gateway' => 'razorpay', // Organization's preferred gateway
'gateway_customer_ids' => [              // JSON: Customer IDs for each gateway
    'razorpay' => 'cust_xxx',
    'stripe' => 'cus_yyy',
],
```

#### Subscription Plans Table
```php
'gateway_plan_ids' => [                  // JSON: Plan IDs for each gateway
    'razorpay' => [
        'monthly' => 'plan_xxx',
        'yearly' => 'plan_yyy',
    ],
    'stripe' => [
        'monthly' => 'price_xxx',
        'yearly' => 'price_yyy',
    ],
],
```

#### Subscriptions Table
```php
'gateway' => 'razorpay',                 // Which gateway is used
'gateway_subscription_id' => 'sub_xxx',  // Gateway subscription ID
'gateway_customer_id' => 'cust_xxx',     // Gateway customer ID
'gateway_plan_id' => 'plan_xxx',         // Gateway plan ID
'gateway_metadata' => [...],             // Gateway-specific data (JSON)
```

#### Invoices Table
```php
'gateway' => 'razorpay',                 // Payment gateway used
'gateway_payment_id' => 'pay_xxx',       // Gateway payment ID
'gateway_order_id' => 'order_xxx',       // Gateway order ID
'gateway_invoice_id' => 'inv_xxx',       // Gateway invoice ID (if supported)
```

#### Payment Attempts Table
```php
'gateway' => 'razorpay',                 // Gateway used for attempt
'gateway_payment_id' => 'pay_xxx',       // Gateway payment ID
```

---

## 🔧 Configuration

### services.php

```php
'default_payment_gateway' => env('DEFAULT_PAYMENT_GATEWAY', 'razorpay'),

'razorpay' => [
    'key' => env('RAZORPAY_KEY_ID'),
    'secret' => env('RAZORPAY_KEY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
],

// Future gateways
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],
```

### Environment Variables

```env
# Default Gateway
DEFAULT_PAYMENT_GATEWAY=razorpay

# Razorpay
RAZORPAY_KEY_ID=your_key_id
RAZORPAY_KEY_SECRET=your_secret
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret

# Stripe (Future)
# STRIPE_KEY=your_stripe_key
# STRIPE_SECRET=your_stripe_secret
# STRIPE_WEBHOOK_SECRET=your_stripe_webhook_secret
```

---

## 🚀 Usage Examples

### Creating a Subscription with Specific Gateway

```php
use App\Services\SubscriptionService;

$subscriptionService = new SubscriptionService('razorpay');

$subscription = $subscriptionService->createSubscription(
    $organization,
    $plan,
    'monthly'
);
```

### Using Organization's Preferred Gateway

```php
// Automatically uses organization's default_payment_gateway
$subscriptionService = new SubscriptionService();
$subscription = $subscriptionService->createSubscription($organization, $plan);
```

### Switching Gateways During Processing

```php
$subscriptionService = new SubscriptionService();

// Use Razorpay
$subscriptionService->setGateway('razorpay');
$subscription = $subscriptionService->createSubscription(...);

// Switch to Stripe for a different organization
$subscriptionService->setGateway('stripe');
$subscription2 = $subscriptionService->createSubscription(...);
```

### Checking Gateway Support

```php
use App\Services\PaymentGatewayFactory;

if (PaymentGatewayFactory::isSupported('stripe')) {
    // Stripe is available
    $gateway = PaymentGatewayFactory::make('stripe');
}

// Get all supported gateways
$gateways = PaymentGatewayFactory::getSupportedGateways();
// Returns:
// [
//     'razorpay' => [
//         'name' => 'Razorpay',
//         'enabled' => true,
//         'supported_currencies' => ['INR'],
//         'features' => ['subscriptions', 'one_time_payments', 'refunds', 'webhooks'],
//     ],
// ]
```

---

## 📝 Adding a New Payment Gateway

### Step 1: Create Gateway Implementation

Create `app/Services/PaymentGateways/StripeGateway.php`:

```php
<?php

namespace App\Services\PaymentGateways;

use App\Contracts\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    public function getGatewayName(): string
    {
        return 'stripe';
    }

    // Implement all interface methods...
}
```

### Step 2: Register in Factory

Update `app/Services/PaymentGatewayFactory.php`:

```php
public static function make(string $gateway): PaymentGatewayInterface
{
    return match (strtolower($gateway)) {
        'razorpay' => new RazorpayGateway(),
        'stripe' => new StripeGateway(),  // Add new gateway
        default => throw new InvalidArgumentException("Unsupported gateway: {$gateway}"),
    };
}

public static function getSupportedGateways(): array
{
    return [
        'razorpay' => [...],
        'stripe' => [                    // Add gateway info
            'name' => 'Stripe',
            'enabled' => !empty(config('services.stripe.key')),
            'supported_currencies' => ['USD', 'EUR', 'INR', 'GBP', ...],
            'features' => ['subscriptions', 'one_time_payments', 'refunds', 'webhooks'],
        ],
    ];
}
```

### Step 3: Add Configuration

Update `config/services.php`:

```php
'stripe' => [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
],
```

### Step 4: Create Webhook Controller (Optional)

Create `app/Http/Controllers/StripeWebhookController.php`:

```php
class StripeWebhookController extends Controller
{
    protected StripeGateway $gateway;

    public function handle(Request $request)
    {
        // Verify signature
        $isValid = $this->gateway->verifyWebhookSignature(
            $request->getContent(),
            $request->header('Stripe-Signature'),
            config('services.stripe.webhook_secret')
        );

        if (!$isValid) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Parse event
        $event = $this->gateway->parseWebhookEvent($request->all());

        // Handle event...
    }
}
```

### Step 5: Register Routes

Update `routes/api.php`:

```php
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
```

### Step 6: Update Organization Plans

Organizations can now create subscriptions with Stripe:

```php
$organization->update(['default_payment_gateway' => 'stripe']);

$subscriptionService = new SubscriptionService();
$subscription = $subscriptionService->createSubscription($organization, $plan);
// Automatically uses Stripe
```

---

## 🔒 Security Considerations

### Webhook Signature Verification

Each gateway implementation must verify webhook signatures:

```php
public function verifyWebhookSignature(string $payload, string $signature, string $secret): bool
{
    // Razorpay uses HMAC SHA256
    $expectedSignature = hash_hmac('sha256', $payload, $secret);
    return hash_equals($expectedSignature, $signature);

    // Stripe uses different method
    // Implement accordingly
}
```

### Gateway Isolation

- Gateway credentials stored in separate config keys
- Each organization can use different gateways
- Gateway failures don't affect other gateways

---

## 📊 Migration Strategy

### Migrating from Razorpay-only to Multi-Gateway

1. **Run Migrations** - Update database schema with gateway-agnostic fields
2. **Migrate Data** - Convert existing Razorpay-specific fields:
   ```sql
   -- Example migration
   UPDATE subscriptions SET
       gateway = 'razorpay',
       gateway_subscription_id = razorpay_subscription_id,
       gateway_customer_id = razorpay_customer_id,
       gateway_plan_id = razorpay_plan_id;
   ```
3. **Update Application** - Use new SubscriptionService with gateway parameter
4. **Test Thoroughly** - Verify all subscription operations work
5. **Add New Gateways** - Implement additional gateways as needed

---

## ✅ Testing Checklist

### Per Gateway
- [ ] Customer creation
- [ ] Plan creation/retrieval
- [ ] Subscription creation
- [ ] Plan upgrade/downgrade
- [ ] Subscription cancellation
- [ ] Subscription resumption
- [ ] Webhook signature verification
- [ ] Payment processing
- [ ] Refund processing

### Cross-Gateway
- [ ] Switch between gateways
- [ ] Handle multiple gateways simultaneously
- [ ] Gateway failover scenarios
- [ ] Data consistency across gateways

---

## 📈 Future Enhancements

- **Gateway Health Monitoring** - Track gateway uptime and performance
- **Automatic Failover** - Switch to backup gateway on failure
- **Smart Gateway Selection** - Choose gateway based on currency, region, or cost
- **Gateway Analytics** - Compare gateway performance and costs
- **Multi-Gateway Billing** - Allow organizations to offer multiple payment options

---

**Generated**: February 8, 2026  
**Version**: 1.0  
**Maintainer**: MeetFlow Engineering Team
