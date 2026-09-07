# Phase 2 — Currency Support (Schema + Display)

## Goal

Give each `Event` its own currency (India event → `INR`, US event → `USD`), and stop hardcoding `INR`/`₹` throughout the booking and payment flow. Purely additive: the India event will default to `INR` and behave exactly as it does today.

Depends on nothing from Phase 1 — can be built in parallel or before it.

## 1. Schema: add `currency` to `events`

**New migration** `database/migrations/2026_02_02_000001_add_currency_to_events_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('currency', 3)->default('INR')->after('price');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('currency');
        });
    }
};
```

`payments.currency` already exists ([database/migrations/2025_11_15_000001_create_payments_table.php:18](../../database/migrations/2025_11_15_000001_create_payments_table.php#L18)) with a default of `'INR'` — no migration needed there, it just needs to actually be populated dynamically (§4) instead of the hardcoded literal it gets today.

## 2. `Event` model — currency symbol helper

**[app/Models/Event.php](../../app/Models/Event.php)** — add an accessor next to the other computed attributes (after `getTimeslotsAttribute()`, ~line 90):

```php
/**
 * Display symbol for this event's currency.
 */
public function getCurrencySymbolAttribute()
{
    return match ($this->currency) {
        'USD' => '$',
        default => '₹',
    };
}
```

`Event::$guarded = []` ([Event.php:14](../../app/Models/Event.php#L14)) means `currency` is already mass-assignable once it's a real column — no other model change needed.

## 3. Admin event form — validation + UI

**[app/Http/Requests/StoreEventRequest.php](../../app/Http/Requests/StoreEventRequest.php)** and **[UpdateEventRequest.php](../../app/Http/Requests/UpdateEventRequest.php)** — add to `rules()` in both:

```php
'price' => 'required|numeric|min:0',
'currency' => 'required|in:INR,USD',
```

Both `Admin/EventController::store()` ([:53-78](../../app/Http/Controllers/Admin/EventController.php#L53)) and `update()` ([:242-266](../../app/Http/Controllers/Admin/EventController.php#L242)) pass `$request->validated()` straight to `Event::create()`/`$event->update()` — since `currency` is now in the validated payload and the model is unguarded, **no controller changes are needed**; it flows through automatically.

**[resources/views/admin/events/create.blade.php:72-87](../../resources/views/admin/events/create.blade.php#L72)** — add a currency selector next to the price field:

```blade
<!-- before -->
{{-- Price --}}
<div class="mb-3">
    <label class="form-label">Price (INR)</label>
    <input
        type="number"
        name="price"
        step="0.01"
        class="form-control @error('price') is-invalid @enderror"
        value="{{ old('price', '500.00') }}"
    >
    <div class="form-text">Amount in Indian Rupees (e.g. 500.00)</div>
    @error('price')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<!-- after -->
{{-- Currency --}}
<div class="mb-3">
    <label class="form-label">Currency</label>
    <select name="currency" class="form-control @error('currency') is-invalid @enderror">
        <option value="INR" {{ old('currency', 'INR') == 'INR' ? 'selected' : '' }}>INR (₹) — India</option>
        <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD ($) — United States</option>
    </select>
    <div class="form-text">Determines the symbol shown to customers and the payment currency used at checkout.</div>
    @error('currency')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Price --}}
<div class="mb-3">
    <label class="form-label">Price</label>
    <input
        type="number"
        name="price"
        step="0.01"
        class="form-control @error('price') is-invalid @enderror"
        value="{{ old('price', '500.00') }}"
    >
    <div class="form-text">Amount in the currency selected above (e.g. 500.00)</div>
    @error('price')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
```

**[resources/views/admin/events/edit.blade.php](../../resources/views/admin/events/edit.blade.php)** — same block, but default the selected option from the existing event: `{{ old('currency', $event->currency) == 'USD' ? 'selected' : '' }}` etc.

## 4. `PaymentController` — stop hardcoding `'INR'`

**[app/Http/Controllers/PaymentController.php](../../app/Http/Controllers/PaymentController.php)** — four write sites currently hardcode the literal:

**`createOrder()`** ([:18-54](../../app/Http/Controllers/PaymentController.php#L18)) — needs to load the booking first to know its currency, then pass it to the gateway:

```php
// before
public function createOrder(Request $request, PaymentGatewayManager $gatewayManager)
{
    $amount = floatval($request->amount ?? 500);
    $bookingId = $request->booking_id ?? null;
    $promoCode = $request->promo_code ?? null;

    try {
        if ($amount == 0 && $bookingId) {
            return $this->processFreeBooking($bookingId, $promoCode);
        }

        $gateway = $gatewayManager->getActiveGateway();
        $gatewayName = $gateway->getName();

        $paymentData = [
            'amount' => $amount,
            'receipt' => 'order_' . time(),
            'booking_id' => $bookingId,
            'product_info' => $request->product_info ?? 'Booking Payment',
            'first_name' => $request->first_name ?? '',
            'email' => $request->email ?? '',
            'txn_id' => 'txn_' . time() . rand(1000, 9999),
            'phone' => $request->phone ?? '',
            'promo_code' => $promoCode,
        ];

        $response = $gateway->initiatePayment($paymentData);
        $response['gateway'] = strtolower($gatewayName);
        return response()->json($response);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}

// after
public function createOrder(Request $request, PaymentGatewayManager $gatewayManager)
{
    $amount = floatval($request->amount ?? 500);
    $bookingId = $request->booking_id ?? null;
    $promoCode = $request->promo_code ?? null;

    try {
        if ($amount == 0 && $bookingId) {
            return $this->processFreeBooking($bookingId, $promoCode);
        }

        $booking = $bookingId ? Booking::with('event')->find($bookingId) : null;
        $currency = $booking->event->currency ?? 'INR';

        $gateway = $gatewayManager->getActiveGateway();
        $gatewayName = $gateway->getName();

        $paymentData = [
            'amount' => $amount,
            'currency' => $currency,
            'receipt' => 'order_' . time(),
            'booking_id' => $bookingId,
            'product_info' => $request->product_info ?? 'Booking Payment',
            'first_name' => $request->first_name ?? '',
            'email' => $request->email ?? '',
            'txn_id' => 'txn_' . time() . rand(1000, 9999),
            'phone' => $request->phone ?? '',
            'promo_code' => $promoCode,
        ];

        $response = $gateway->initiatePayment($paymentData);
        $response['gateway'] = strtolower($gatewayName);
        return response()->json($response);
    } catch (Exception $e) {
        return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
    }
}
```

(The `$gateway = $gatewayManager->getActiveGateway();` line above is revisited in [Phase 3](phase-3-payu-usd-gateway.md#2-route-usd-orders-to-payu) to route USD orders to PayU specifically — this phase only adds the `currency` field to the payload.)

**`processFreeBooking()`** ([:93](../../app/Http/Controllers/PaymentController.php#L93)):

```php
// before
'currency' => 'INR',

// after
'currency' => $booking->event->currency ?? 'INR',
```

(`$booking` is already loaded with `event` at the top of this method via `Booking::with(['event.user'])->find($bookingId)`.)

**`verifyPayment()`** ([:212](../../app/Http/Controllers/PaymentController.php#L212)) — `$booking` is already loaded with `event.user` at line 185:

```php
// before
'currency' => 'INR',

// after
'currency' => $booking->event->currency ?? 'INR',
```

**`payuCallback()`** failed-payment record ([:366](../../app/Http/Controllers/PaymentController.php#L366)) — `$booking` is loaded via `Booking::find($bookingId)` at line 313; load its event too:

```php
// before
$booking = Booking::find($bookingId);
...
PaymentModel::updateOrCreate(
    ['booking_id' => $booking->id],
    [
        'user_id' => $booking->user_id,
        'provider' => 'payu',
        'transaction_id' => $request->mihpayid ?? null,
        'status' => 'failed',
        'amount' => $request->amount ?? 0,
        'currency' => 'INR',
        'metadata' => json_encode($request->all()),
    ]
);

// after
$booking = Booking::with('event')->find($bookingId);
...
PaymentModel::updateOrCreate(
    ['booking_id' => $booking->id],
    [
        'user_id' => $booking->user_id,
        'provider' => 'payu',
        'transaction_id' => $request->mihpayid ?? null,
        'status' => 'failed',
        'amount' => $request->amount ?? 0,
        'currency' => $booking->event->currency ?? 'INR',
        'metadata' => json_encode($request->all()),
    ]
);
```

**`processSuccessfulPayuPayment()`** ([:502](../../app/Http/Controllers/PaymentController.php#L502)) — `$booking->load(['event.user', 'followUpInvite'])` already happens at line 486:

```php
// before
'currency'       => 'INR',

// after
'currency'       => $booking->event->currency ?? 'INR',
```

**`validatePromoCode()`** ([:648, 694](../../app/Http/Controllers/PaymentController.php#L648)) — hardcodes `₹` in user-facing message strings. The booking is fetched at line 667, *after* the `₹` message at line 648 — reorder so the currency symbol is available for both:

```php
// before (line ~610)
$promoCode = PromoCode::where('code', $code)->first();
if (!$promoCode) { ... }
if (!$promoCode->is_active) { ... }
// ... validity date checks ...
if ($promoCode->min_booking_amount && $originalAmount < $promoCode->min_booking_amount) {
    return response()->json([
        'success' => false,
        'message' => "Minimum booking amount of ₹{$promoCode->min_booking_amount} required"
    ], 400);
}
// ... usage limit check ...
$booking = Booking::findOrFail($bookingId);
// ...
return response()->json([
    'success' => true,
    'message' => "Promo code applied successfully! You saved ₹{$discountValue}",
    ...
]);

// after
$booking = Booking::with('event')->findOrFail($bookingId); // moved up, right after validating $request
$currencySymbol = $booking->event->currency_symbol ?? '₹'; // uses the Event::getCurrencySymbolAttribute() accessor from §2

$promoCode = PromoCode::where('code', $code)->first();
if (!$promoCode) { ... }
if (!$promoCode->is_active) { ... }
// ... validity date checks ...
if ($promoCode->min_booking_amount && $originalAmount < $promoCode->min_booking_amount) {
    return response()->json([
        'success' => false,
        'message' => "Minimum booking amount of {$currencySymbol}{$promoCode->min_booking_amount} required"
    ], 400);
}
// ... usage limit check (drop the now-duplicate $booking = Booking::findOrFail($bookingId) line below it) ...
return response()->json([
    'success' => true,
    'message' => "Promo code applied successfully! You saved {$currencySymbol}{$discountValue}",
    ...
]);
```

## 5. `RazorpayService` — flag, don't fix here

**[app/Services/RazorpayService.php:38](../../app/Services/RazorpayService.php#L38)** hardcodes `'currency' => 'INR'` in the Razorpay order-create call and doesn't read `$data['currency']` at all. Standard Razorpay India accounts generally can't settle USD orders without a separate "Razorpay International" product. **Leave this hardcoded for now** — [Phase 3](phase-3-payu-usd-gateway.md) routes USD bookings to PayU specifically rather than fixing Razorpay's USD support, since PayU is the gateway the astrologer is actually activating for cross-border.

## 6. Blade/JS — replace hardcoded `₹`

**[resources/views/payments/show.blade.php](../../resources/views/payments/show.blade.php)** — inject a currency symbol once near the top of the page and reuse it everywhere instead of the literal `₹`:

Add near the top of the main `@push('scripts')` block (before it's used anywhere), and expose the event's currency for the Razorpay options object too:

```blade
<script>
    const currencySymbol = @json($booking->event->currency_symbol ?? '₹');
    const eventCurrency = @json($booking->event->currency ?? 'INR');
</script>
```

Then update each hardcoded occurrence:

| Location | Before | After |
|---|---|---|
| [:316](../../resources/views/payments/show.blade.php#L316) (Blade) | `₹<span id="originalPrice">{{ $actualPrice }}</span>` | `{{ $booking->event->currency_symbol ?? '₹' }}<span id="originalPrice">{{ $actualPrice }}</span>` |
| [:320](../../resources/views/payments/show.blade.php#L320) (Blade) | `₹<span id="finalAmount">{{ $actualPrice }}</span>` | `{{ $booking->event->currency_symbol ?? '₹' }}<span id="finalAmount">{{ $actualPrice }}</span>` |
| [:329](../../resources/views/payments/show.blade.php#L329) (Blade) | `<span id="discountText">Saved ₹0</span>` | `<span id="discountText">Saved {{ $booking->event->currency_symbol ?? '₹' }}0</span>` |
| [:397](../../resources/views/payments/show.blade.php#L397) (Blade) | `Pay ₹<span id="payBtnAmount">{{ $actualPrice }}</span>` | `Pay {{ $booking->event->currency_symbol ?? '₹' }}<span id="payBtnAmount">{{ $actualPrice }}</span>` |
| [:615](../../resources/views/payments/show.blade.php#L615) (JS) | `originalPriceEl.textContent = originalAmount;` | unchanged (symbol is already in static markup around the span) |
| [:622](../../resources/views/payments/show.blade.php#L622) (JS) | `` discountText.textContent = `Saved ₹${discountValue}`; `` | `` discountText.textContent = `Saved ${currencySymbol}${discountValue}`; `` |
| [:629](../../resources/views/payments/show.blade.php#L629) (JS) | `` payBtnTextEl.innerHTML = `Pay ₹<span id="payBtnAmount">${discountedAmount}</span>`; `` | `` payBtnTextEl.innerHTML = `Pay ${currencySymbol}<span id="payBtnAmount">${discountedAmount}</span>`; `` |
| [:639](../../resources/views/payments/show.blade.php#L639) (JS) | `` payBtnTextEl.innerHTML = `Pay ₹<span id="payBtnAmount">${originalAmount}</span>`; `` | `` payBtnTextEl.innerHTML = `Pay ${currencySymbol}<span id="payBtnAmount">${originalAmount}</span>`; `` |

Tracking-pixel calls hardcode `'currency' => 'INR'` at [:23,30](../../resources/views/payments/show.blade.php#L23) — update both to `'currency' => $booking->event->currency ?? 'INR'` so ad-platform ROAS reporting (Meta/Google Ads) stays accurate for USD bookings. The Razorpay checkout options at [:744](../../resources/views/payments/show.blade.php#L744) hardcode `currency: 'INR'` — update to `currency: eventCurrency` (harmless for now since Phase 3 routes USD away from Razorpay entirely, but keeps the JS internally consistent).

**[resources/views/bookings/details.blade.php:215](../../resources/views/bookings/details.blade.php#L215)** (follow-up custom price display) — same swap: `₹` → `{{ $event->currency_symbol ?? '₹' }}`.

## 7. Email templates — three more hardcoded `₹` sites

Missed on the first pass of this doc: the Blade *views* (payment page, booking details) aren't the only place `₹` is hardcoded — three transactional emails are too, and none of them currently receive a currency symbol from their notification class.

**Follow-up / invitation price emails** — **[app/Notifications/FollowUpInviteNotification.php](../../app/Notifications/FollowUpInviteNotification.php)** renders `emails.invitation` or `emails.follow-up-invite` depending on `$this->invite->is_normal_invite`, passing `customPrice` but no currency. `FollowUpInvite` has its own `event()` relation ([FollowUpInvite.php:61](../../app/Models/FollowUpInvite.php#L61)), so the symbol is one line away in both branches:

```php
// before (toMail(), both branches)
->view('emails.invitation', [
    'userName' => $this->invite->user->name ?? $this->invite->booking->booker_name,
    'eventTitle' => $this->invite->event->title,
    'customPrice' => $this->invite->custom_price,
    'isFree' => $this->invite->custom_price == 0,
    ...
]);

// after
->view('emails.invitation', [
    'userName' => $this->invite->user->name ?? $this->invite->booking->booker_name,
    'eventTitle' => $this->invite->event->title,
    'customPrice' => $this->invite->custom_price,
    'currencySymbol' => $this->invite->event->currency_symbol ?? '₹',
    'isFree' => $this->invite->custom_price == 0,
    ...
]);
```

(Same addition to the second `->view('emails.follow-up-invite', [...])` call in the same method.)

**[resources/views/emails/invitation.blade.php:84](../../resources/views/emails/invitation.blade.php#L84)** and **[resources/views/emails/follow-up-invite.blade.php:87](../../resources/views/emails/follow-up-invite.blade.php#L87)**:

```blade
<!-- before -->
<p style="...">Special Price: ₹{{ number_format($customPrice, 2) }}</p>

<!-- after -->
<p style="...">Special Price: {{ $currencySymbol ?? '₹' }}{{ number_format($customPrice, 2) }}</p>
```

**Refund email** — **[app/Notifications/RefundProcessedNotification.php:52,58,81](../../app/Notifications/RefundProcessedNotification.php#L52)** has a **pre-existing bug** independent of this task: it builds `$refundAmount = '₹' . number_format(...)` in PHP, then the Blade view *also* prepends a literal `₹` ([refund-processed.blade.php:60](../../resources/views/emails/refund-processed.blade.php#L60): `₹{{ $refundAmount ?? '999' }}`), so the email currently renders `₹₹999.00`. Fixing this and adding currency support are the same edit — separate the number from the symbol and derive the symbol from the booking's event:

```php
// before
public function toMail($notifiable)
{
    $eventName = $this->booking->event->title ?? 'Event';
    $refundAmount = '₹' . number_format($this->refund->net_refund_amount, 2);
    $gateway = ucfirst($this->refund->gateway);

    return (new MailMessage)
        ->subject('Refund Processed - ' . $eventName)
        ->view('emails.refund-processed', [
            'refundAmount' => $refundAmount,
            'transactionId' => $this->refund->transaction_id ?? 'N/A',
            'processedDate' => $this->refund->created_at->format('M d, Y'),
            'refundMethod' => $gateway,
            'eventTitle' => $eventName,
            'transactionHistoryUrl' => url('/user/transactions'),
        ]);
}

public function toArray($notifiable)
{
    return [
        'booking_id' => $this->booking->id,
        'refund_id' => $this->refund->id,
        'event_name' => $this->booking->event->name ?? 'Event',
        'refund_amount' => $this->refund->net_refund_amount,
        'gateway' => $this->refund->gateway,
        'message' => 'Your refund of ₹' . number_format($this->refund->net_refund_amount, 2) . ' has been processed.',
    ];
}

// after
public function toMail($notifiable)
{
    $eventName = $this->booking->event->title ?? 'Event';
    $currencySymbol = $this->booking->event->currency_symbol ?? '₹';
    $refundAmount = number_format($this->refund->net_refund_amount, 2);
    $gateway = ucfirst($this->refund->gateway);

    return (new MailMessage)
        ->subject('Refund Processed - ' . $eventName)
        ->view('emails.refund-processed', [
            'refundAmount' => $refundAmount,
            'currencySymbol' => $currencySymbol,
            'transactionId' => $this->refund->transaction_id ?? 'N/A',
            'processedDate' => $this->refund->created_at->format('M d, Y'),
            'refundMethod' => $gateway,
            'eventTitle' => $eventName,
            'transactionHistoryUrl' => url('/user/transactions'),
        ]);
}

public function toArray($notifiable)
{
    $currencySymbol = $this->booking->event->currency_symbol ?? '₹';
    return [
        'booking_id' => $this->booking->id,
        'refund_id' => $this->refund->id,
        'event_name' => $this->booking->event->name ?? 'Event',
        'refund_amount' => $this->refund->net_refund_amount,
        'gateway' => $this->refund->gateway,
        'message' => 'Your refund of ' . $currencySymbol . number_format($this->refund->net_refund_amount, 2) . ' has been processed.',
    ];
}
```

**[resources/views/emails/refund-processed.blade.php:60](../../resources/views/emails/refund-processed.blade.php#L60)**:

```blade
<!-- before -->
<h3 style="...">₹{{ $refundAmount ?? '999' }}</h3>

<!-- after -->
<h3 style="...">{{ $currencySymbol ?? '₹' }}{{ $refundAmount ?? '999' }}</h3>
```

`booking-confirmation.blade.php` (the main booking-confirmation email covered for timezone in [Phase 1 §6](phase-1-timezone-display.md#6-emails--server-side-conversion-carbon)) doesn't display a price at all — just date/time/timezone/meeting link — so it needs no currency change.

**Final sweep**: grep the codebase for `₹` and `'currency' => 'INR'` / `currency: 'INR'` after making all the above changes to catch anything still missed — the pattern is identical everywhere: replace the literal with the event's `currency`/`currency_symbol`.

## Testing checklist

- [ ] Run the new migration; confirm `events.currency` exists, defaults to `INR`, and the existing India event still reads `INR`.
- [ ] Create a test event via the admin form with currency `USD`; confirm it saves and the edit form shows `USD` selected.
- [ ] Visit that event's payment page; confirm `$` appears everywhere instead of `₹` (amount display, promo discount, pay button).
- [ ] Apply a promo code on a USD event; confirm the success/error messages show `$` not `₹`.
- [ ] Confirm the India event's payment page is visually unchanged (still `₹` everywhere).
- [ ] Check a completed USD booking's `payments` row — `currency` should be `USD`, not the old hardcoded `INR`.
- [ ] Send a follow-up invite for a USD-event booking; confirm the email shows `$`, not `₹`.
- [ ] Trigger a refund on a USD-event booking; confirm the refund email shows a single `$` symbol (not a doubled `₹₹` as it currently would even for INR — verify the double-symbol bug is actually gone).
