# Payment Gateway Integration Setup

## Overview
This document outlines the scalable multi-gateway payment integration for Natega. Admins can select and configure payment gateways (Razorpay or PayU) with encrypted credential storage.

## Architecture

### Services
- **PaymentGatewayInterface**: Contract for all payment gateways
- **PaymentGatewayManager**: Resolves and loads the active gateway at runtime
- **RazorpayService**: Razorpay implementation (supports API key/secret from encrypted settings or env)
- **PayUService**: PayU implementation (supports merchant key/salt from encrypted settings or env)

### Models
- **Setting**: Key-value store with encryption support for sensitive data
  - `key`: Setting identifier
  - `value`: Setting value (encrypted if marked)
  - `is_encrypted`: Boolean flag for encrypted values
  - Methods: `setSetting()`, `getSetting()` handle encryption/decryption automatically

### Admin Panel
- **Route**: `/admin/payment-gateway` (GET to view, PUT to update)
- **Controller**: `PaymentGatewayController` handles selection and credential storage
- **View**: `admin.payment_gateway.edit` - Form to select gateway and enter credentials
- **Features**:
  - Select active gateway (Razorpay or PayU)
  - Store encrypted Razorpay credentials (Key ID, Key Secret)
  - Store encrypted PayU credentials (Merchant Key, Merchant Salt)
  - Credentials encrypted at rest; only decrypted when services need them

### Public Booking Flow
- **Route**: `/e/{event:slug}` - Public booking page
- **Updated**: Event page now detects active gateway and passes to frontend
- **Frontend**: Dynamically loads appropriate payment SDK (Razorpay or PayU)
- **Payment Handling**:
  - `handleRazorpayPayment()` - Opens Razorpay checkout
  - `handlePayUPayment()` - Submits form to PayU gateway

### Payment Processing
- **Endpoint**: `/create-order` (POST)
  - Detects active gateway via `PaymentGatewayManager`
  - Returns gateway-specific response (Razorpay order or PayU hash)
- **Verification**: `/verify-payment` (POST)
  - Verifies signature with active gateway service
  - Marks booking as confirmed on success

## Setup Steps

### 1. Database Setup
```bash
php artisan migrate
```
This creates the `settings` table with encryption support.

### 2. Environment Variables (Optional)
You can set default credentials in `.env` as fallback:
```env
RAZORPAY_KEY_ID=your_key_id
RAZORPAY_KEY_SECRET=your_key_secret
PAYU_MERCHANT_KEY=your_merchant_key
PAYU_MERCHANT_SALT=your_merchant_salt
PAYU_ENVIRONMENT=test  # or 'production'
```

### 3. Admin Configuration
1. Log in to admin panel
2. Navigate to Settings → Payment Gateway
3. Select active gateway (Razorpay or PayU)
4. Enter credentials (automatically encrypted in database)
5. Save

### 4. Frontend Integration
The public booking page now automatically:
- Detects active gateway from database
- Loads appropriate payment SDK
- Routes to correct payment handler
- Shows PayU form or Razorpay modal based on selection

## Adding New Gateways

1. **Create Service Class** (e.g., `app/Services/StripeService.php`):
```php
namespace App\Services;

class StripeService implements PaymentGatewayInterface
{
    public function getName(): string { return 'stripe'; }
    public function getPublicConfig(): array { /* ... */ }
    public function initiatePayment(array $data): array { /* ... */ }
    public function verifyPayment(array $payload): bool { /* ... */ }
}
```

2. **Register in PaymentGatewayManager**:
```php
protected $gateways = [
    'razorpay' => RazorpayService::class,
    'payu' => PayUService::class,
    'stripe' => StripeService::class,  // Add here
];
```

3. **Update Admin Form** (`admin.payment_gateway.edit`):
   - Add credential input fields for new gateway
   - Add payment handler function in booking page

4. **Update `.env` with new gateway credentials** (optional fallback)

## Security Notes

- **Encryption**: All sensitive credentials are encrypted using Laravel's `Crypt` facade
- **Database**: Never display encrypted values in logs or UI
- **Environment Fallback**: Settings take precedence over `.env` for flexibility
- **Validation**: Admin routes require authentication and authorization

## API Response Format

### Razorpay Response
```json
{
    "gateway": "razorpay",
    "success": true,
    "order_id": "order_xxx",
    "amount": 500,
    "key": "key_xxx"
}
```

### PayU Response
```json
{
    "gateway": "payu",
    "success": true,
    "txn_id": "txn_xxx",
    "amount": 500,
    "merchant_key": "merchant_key",
    "hash": "hash_xxx",
    "payu_url": "https://test.payu.in/_payment",
    "product_info": "Booking Payment",
    "first_name": "John",
    "email": "john@example.com"
}
```

## Files Modified

1. **Models**: `Setting.php` - Added encryption support
2. **Services**: `PaymentGatewayInterface.php`, `RazorpayService.php`, `PayUService.php`, `PaymentGatewayManager.php`
3. **Controllers**: `PaymentController.php`, `PaymentGatewayController.php`, `EventController.php`
4. **Views**: `admin/payment_gateway/edit.blade.php`, `book-event.blade.php`
5. **Migrations**: `create_settings_table.php`

## Testing

```bash
# Test with Razorpay
1. Set RAZORPAY_KEY_ID and RAZORPAY_KEY_SECRET in .env
2. Navigate to admin/payment-gateway, select Razorpay
3. Book an event - should show Razorpay checkout

# Test with PayU
1. Set PAYU_MERCHANT_KEY and PAYU_MERCHANT_SALT in .env
2. Navigate to admin/payment-gateway, select PayU
3. Book an event - should redirect to PayU form
```
