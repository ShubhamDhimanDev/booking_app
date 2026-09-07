# Phase 3 — USD Payments via PayU

## Goal

Route the US event's payments through PayU in USD, using PayU's existing "International/Cross-Border Payments" product rather than integrating a new gateway. Depends on [Phase 2](phase-2-currency-support.md) (needs `event.currency` to exist).

## Step 0 — Business prerequisite (not code, start this immediately)

PayU India supports 135+ currencies including USD for merchants who activate their **International/Cross-Border Payments** product on an *existing* merchant account ([payu.in/international-payments](https://payu.in/international-payments/), [docs.payu.in cross-border FAQ](https://docs.payu.in/docs/faqs-for-cross-border-payments)). This is **not** automatically available — it requires:

1. The astrologer (or whoever holds the PayU merchant account) raises a ticket at help.payu.in or emails care@payu.in requesting international/cross-border activation.
2. Activation is subject to approval from PayU's banking partners — PayU's docs describe this as "most businesses going live within a few days," but don't treat that as a guarantee.
3. **Before writing any code in this phase, confirm with PayU support**: does activation reuse the same `PAYU_MERCHANT_KEY`/`PAYU_MERCHANT_SALT` already configured (via the `settings` table, see [PayUService.php:18-20](../../app/Services/PayUService.php#L18)), or does it issue separate international credentials? PayU's public docs are marketing-level and don't specify this. If it's a separate key/salt pair, the `Setting::getSetting('payu_merchant_key', ...)` lookups in `PayUService` will need a currency-aware branch instead of the single flat key used today — get this answer from PayU before assuming the simpler path below is correct.
4. Settlement lands in INR by default (or non-INR if configured) at PayU's daily exchange rate, T+2 business days — this is a business/accounting detail for the astrologer, not something the app needs to handle.

The rest of this doc assumes the simpler case (same merchant key, `currency` becomes just another form field PayU checkout accepts) since that's what PayU's own hosted-checkout API (`_payment` endpoint) supports today. If PayU comes back requiring separate credentials, treat §2 below as needing a currency-keyed credential lookup inside `PayUService`'s constructor instead of a single one.

## 1. `PayUService` — thread currency into the payload

**[app/Services/PayUService.php:38-95](../../app/Services/PayUService.php#L38)** — `initiatePayment()` currently builds no `currency` field at all (PayU defaults to the merchant account's base currency, INR). The hash formula ([:60-61](../../app/Services/PayUService.php#L60)) is unaffected by currency per PayU's spec — this is additive, not a hash change:

```php
// before
return [
    'gateway' => 'payu',
    'success' => true,
    'txnid' => $txnId,
    'amount' => $amount,
    'key' => $this->merchantKey,
    'merchant_id' => $this->merchantId,
    'hash' => $hash_v1,
    'payu_url' => $payuUrl,
    'productinfo' => $productInfo,
    'firstname' => $firstName,
    'email' => $email,
    'phone' => $phone,
    'surl' => route('payment.payu.callback'),
    'furl' => route('payment.payu.callback'),
    'udf1' => $udf1,
    'udf2' => $udf2,
    'udf3' => $udf3,
    'udf4' => $udf4,
    'udf5' => $udf5,
    'service_provider' => 'payu_paisa',
];

// after
return [
    'gateway' => 'payu',
    'success' => true,
    'txnid' => $txnId,
    'amount' => $amount,
    'currency' => $data['currency'] ?? 'INR',
    'key' => $this->merchantKey,
    'merchant_id' => $this->merchantId,
    'hash' => $hash_v1,
    'payu_url' => $payuUrl,
    'productinfo' => $productInfo,
    'firstname' => $firstName,
    'email' => $email,
    'phone' => $phone,
    'surl' => route('payment.payu.callback'),
    'furl' => route('payment.payu.callback'),
    'udf1' => $udf1,
    'udf2' => $udf2,
    'udf3' => $udf3,
    'udf4' => $udf4,
    'udf5' => $udf5,
    'service_provider' => 'payu_paisa',
];
```

That's the entire `PayUService` change. No further Blade/JS change is needed to actually submit it: `handlePayUPayment(data)` in [resources/views/payments/show.blade.php:807-841](../../resources/views/payments/show.blade.php#L807) already loops over **every key** in the `initiatePayment()` response (except a small skip-list: `gateway`, `payu_url`, `success`, `udf1`, `udf2`) and adds each as a hidden form field before submitting to PayU:

```php
const skipKeys = new Set(['gateway', 'payu_url', 'success', 'udf1', 'udf2']);
for (const key in data) {
    if (!skipKeys.has(key) && data[key] !== null && data[key] !== undefined) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = data[key];
        form.appendChild(input);
    }
}
```

Once `currency` is present in the response object, it's automatically posted to PayU as `<input type="hidden" name="currency" value="USD">` — no changes needed here.

## 2. Route USD orders to PayU

**[app/Http/Controllers/PaymentController.php — `createOrder()`](../../app/Http/Controllers/PaymentController.php#L18)**, already updated in [Phase 2](phase-2-currency-support.md#4-paymentcontroller--stop-hardcoding-inr) to resolve `$currency` from the booking's event. Extend it to force PayU for USD, regardless of the astrologer's global `payment_gateway` admin setting (which controls Razorpay vs. PayU for the India event):

```php
// before (from Phase 2)
$gateway = $gatewayManager->getActiveGateway();
$gatewayName = $gateway->getName();

// after
$gateway = $currency === 'USD'
    ? $gatewayManager->getGateway('payu')
    : $gatewayManager->getActiveGateway();
$gatewayName = $gateway->getName();
```

This reuses `PaymentGatewayManager::getGateway(string $name)` ([app/Services/PaymentGatewayManager.php:27-34](../../app/Services/PaymentGatewayManager.php#L27)), which already exists and is already used the same way in `payuCallback()` ([PaymentController.php:331](../../app/Http/Controllers/PaymentController.php#L331)) — `$gatewayManager->getGateway('payu')`. No changes to `PaymentGatewayManager` itself are needed: the India event keeps using whatever gateway the admin has set globally (Razorpay or PayU), and the US event is hardcoded to PayU specifically, since that's the only gateway being activated for cross-border.

If the astrologer later wants Razorpay for USD too (Razorpay International is a separate product with its own activation), the same one-line pattern extends: branch on `$currency` to pick whichever gateway is actually cross-border-capable, rather than generalizing this into a currency→gateway settings table now. Not needed for the current scope.

## 3. `RazorpayService` — no change

Left hardcoded to `'currency' => 'INR'` (see [Phase 2, §5](phase-2-currency-support.md#5-razorpayservice--flag-dont-fix-here)) since USD orders never reach it after the routing change in §2 above.

## 4. Webhook/callback paths — no change needed

`payuCallback()` and `payuWebhook()` ([PaymentController.php:295-472](../../app/Http/Controllers/PaymentController.php#L295)) read `udf1`/`status`/`amount`/etc. from PayU's response payload — none of that logic is currency-specific, and PayU echoes back whatever `currency` was submitted without altering the hash fields being verified. The currency threading from Phase 2 (§4, `processSuccessfulPayuPayment()`) already handles recording the correct currency on the `payments` row from `$booking->event->currency`, independent of what PayU's callback payload says.

## Testing checklist (do this in PayU's **test** environment first — `PAYU_ENVIRONMENT=test`)

- [ ] Confirm with PayU support whether cross-border activation uses the same merchant key/salt (§0) before testing — if it doesn't, the credential-lookup change described in §0 must land first.
- [ ] Create a USD test event (per [Phase 4](phase-4-us-event-launch-runbook.md)) and complete a full test booking end-to-end in PayU's sandbox.
- [ ] Inspect the actual POST payload PayU's hosted checkout receives (browser dev tools → Network, or PayU's sandbox transaction log) — confirm `currency=USD` is present.
- [ ] Confirm the `payments` row created after a successful test payment has `currency = 'USD'` and a sane `amount`.
- [ ] Confirm an India-event (INR) test booking still completes correctly through whichever gateway the global `payment_gateway` setting points to — this phase must not change India-event behavior.
- [ ] Once sandbox tests pass, ask the astrologer to confirm PayU has approved production cross-border activation before flipping `PAYU_ENVIRONMENT` to `production` for real USD transactions.
