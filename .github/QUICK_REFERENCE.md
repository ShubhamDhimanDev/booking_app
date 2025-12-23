# Quick Reference: Multi-Gateway Payment System

## Admin Panel URL
```
http://localhost:8000/admin/payment-gateway
```

## Key Files to Know

### Services
- `app/Services/PaymentGatewayInterface.php` - Gateway contract
- `app/Services/PaymentGatewayManager.php` - Gateway resolver
- `app/Services/RazorpayService.php` - Razorpay implementation
- `app/Services/PayUService.php` - PayU implementation

### Models & Database
- `app/Models/Setting.php` - Encrypted settings storage
- Migration: `database/migrations/2025_12_22_000001_create_settings_table.php`

### Controllers
- `app/Http/Controllers/PaymentController.php` - Payment processing
- `app/Http/Controllers/Admin/PaymentGatewayController.php` - Admin settings

### Views
- `resources/views/admin/payment_gateway/edit.blade.php` - Admin form
- `resources/views/book-event.blade.php` - Public booking page

## Common Tasks

### Use Active Payment Gateway in Code
```php
use App\Services\PaymentGatewayManager;

// In controller or service
$manager = app(PaymentGatewayManager::class);
$gateway = $manager->getActiveGateway();
$response = $gateway->initiatePayment(['amount' => 500]);
```

### Access Encrypted Setting
```php
use App\Models\Setting;

// Store encrypted
Setting::setSetting('razorpay_key_secret', 'secret_value', true);

// Retrieve (auto-decrypts)
$secret = Setting::getSetting('razorpay_key_secret');
```

### Add New Payment Gateway

1. **Create Service**:
   ```php
   class StripeService implements PaymentGatewayInterface { ... }
   ```

2. **Register**:
   Edit `app/Services/PaymentGatewayManager.php`:
   ```php
   protected $gateways = [
       'razorpay' => RazorpayService::class,
       'payu' => PayUService::class,
       'stripe' => StripeService::class,  // Add
   ];
   ```

3. **Admin Form**:
   Edit `resources/views/admin/payment_gateway/edit.blade.php` to add Stripe fields

4. **Frontend**:
   Edit `resources/views/book-event.blade.php` to add Stripe handler

## API Endpoints

| Endpoint | Method | Purpose |
|----------|--------|---------|
| `/admin/payment-gateway` | GET | View settings form |
| `/admin/payment-gateway` | PUT | Update settings |
| `/create-order` | POST | Create payment order (gateway-agnostic) |
| `/verify-payment` | POST | Verify payment (gateway-agnostic) |
| `/e/{slug}` | GET | Public booking page (auto-detects gateway) |

## Database Queries

### Get Active Gateway
```php
$active = Setting::getSetting('payment_gateway', 'razorpay');
```

### Get All Settings
```php
$all = Setting::all();
```

### Update Setting
```php
Setting::setSetting('key', 'value', $encrypt = false);
```

## Frontend Integration

### Detect Active Gateway
```javascript
const activeGateway = @json($activeGateway ?? 'razorpay');
```

### Call Payment Handler
```javascript
if (activeGateway === 'razorpay') {
    handleRazorpayPayment(orderData, bookingId);
} else if (activeGateway === 'payu') {
    handlePayUPayment(orderData, bookingId);
}
```

## Testing Commands

```bash
# Migrate settings table
php artisan migrate

# Test in Tinker
php artisan tinker
> Setting::setSetting('payment_gateway', 'razorpay')
> Setting::getSetting('payment_gateway')
> Setting::setSetting('razorpay_key_id', 'key_123', true)
> Setting::getSetting('razorpay_key_id')  // Returns decrypted value
```

## Troubleshooting

### Payment Gateway Not Switching
1. Check `/admin/payment-gateway` - is selection saved?
2. Verify database: `select * from settings where key='payment_gateway'`
3. Clear cache: `php artisan config:clear`

### Credentials Not Working
1. Check Settings table - are credentials stored and marked encrypted?
2. Verify encryption key in `.env`: `APP_KEY` must be set
3. Try env vars fallback: set in `.env` and test

### Frontend Not Detecting Gateway
1. Check browser console for errors
2. Verify `@json($activeGateway)` renders in HTML
3. Check EventController passes gateway config

## Environment File Example

```env
APP_KEY=base64:xxxxxx  # IMPORTANT: Must be set for encryption

# Optional fallback credentials
RAZORPAY_KEY_ID=rzp_test_xxxxx
RAZORPAY_KEY_SECRET=xxxxxx

PAYU_MERCHANT_KEY=xxxxx
PAYU_MERCHANT_SALT=xxxxx
PAYU_ENVIRONMENT=test
```

## Performance Notes

- Gateway is loaded once per request via `PaymentGatewayManager`
- Credentials decrypted on demand (minimal overhead)
- Settings cached if needed (not cached by default)
- Frontend SDKs (Razorpay, PayU) loaded conditionally
