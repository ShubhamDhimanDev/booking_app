# Phase 1 — Automatic Per-Visitor Timezone Display

## Goal

Every visitor — Indian or American, on the India event or the future US event — sees slot times and booking confirmations in **their own local clock**, with the original IST time kept visible as a reference. Nothing about how times are stored or computed server-side changes: `config/app.php:73` stays `Asia/Kolkata`, and every `date`/`time` column keeps meaning "IST wall clock."

## Design

Two different mechanisms for two different contexts:

1. **Pages rendered in the browser** (slot picker, booking details, thank-you, reschedule, bookings list) — convert client-side with JavaScript. Progressive enhancement: the server still renders the IST time as before; a small shared script rewrites it to the visitor's local time on page load. If JS fails to run, the visitor still sees a correct, labeled IST time — nothing breaks.
2. **Emails** (confirmation, reminders) — no JavaScript available, so this needs a real server-side conversion using Carbon, using the visitor's timezone captured at booking time.

IST has a fixed UTC+5:30 offset (no DST), so converting a stored `date + time` into an absolute instant is simple and doesn't need a timezone-conversion library: `new Date(`${date}T${time}:00+05:30`)` in JS, or `Carbon::parse("$date $time", 'Asia/Kolkata')` in PHP — both give the correct instant, which can then be reformatted into any target zone.

## 1. New column: capture the visitor's timezone at booking time

**New migration** `database/migrations/2026_02_01_000001_add_timezone_to_bookings_table.php`:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('timezone', 64)->nullable()->after('booked_at_time');
        });
    }

    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
};
```

Nullable so existing rows are unaffected; `BookingCreatedNotification.php:56` already has a `?? 'IST'` fallback for when it's empty.

## 2. Shared JS helper — detect + convert

Add a new partial included on every customer-facing page, e.g. `resources/views/partials/timezone-convert.blade.php`, and `@include` it from `resources/views/layouts/app.blade.php` (near the closing `@stack('scripts')` at line 321, so it runs after page-specific scripts have populated the DOM):

```blade
<script>
(function () {
    // Detect once, reuse everywhere on this page.
    let visitorTz = 'Asia/Kolkata';
    try {
        visitorTz = Intl.DateTimeFormat().resolvedOptions().timeZone || 'Asia/Kolkata';
    } catch (e) { /* Intl unsupported — fall back to IST, matches server default */ }
    window.__visitorTz = visitorTz;

    // Given an IST-wall-clock date ("YYYY-MM-DD") and time ("HH:mm"), return
    // { local: "8:00 AM PDT", ist: "6:30 PM IST", sameDay: bool } for display.
    window.convertIstToVisitorTz = function (dateStr, timeStr) {
        const istDate = new Date(`${dateStr}T${timeStr}:00+05:30`);
        const localFmt = new Intl.DateTimeFormat('en-US', {
            hour: 'numeric', minute: '2-digit', hour12: true,
            timeZoneName: 'short', timeZone: visitorTz,
        });
        const istFmt = new Intl.DateTimeFormat('en-US', {
            hour: 'numeric', minute: '2-digit', hour12: true,
            timeZoneName: 'short', timeZone: 'Asia/Kolkata',
        });
        const localDayFmt = new Intl.DateTimeFormat('en-CA', { timeZone: visitorTz }); // YYYY-MM-DD
        return {
            local: localFmt.format(istDate),
            ist: istFmt.format(istDate),
            localDate: localDayFmt.format(istDate),
            isSameDayAsIst: localDayFmt.format(istDate) === dateStr,
        };
    };

    // Progressive enhancement: rewrite any element carrying data-ist-date/data-ist-time
    // (used on server-rendered pages — details/thankyou/reschedule/bookings-grid).
    document.querySelectorAll('[data-ist-date][data-ist-time]').forEach(function (el) {
        const conv = window.convertIstToVisitorTz(el.dataset.istDate, el.dataset.istTime);
        const dayNote = conv.isSameDayAsIst ? '' : ' *';
        el.textContent = `${conv.local}${dayNote} (${conv.ist})`;
        if (dayNote) el.title = 'Date shown is in your local timezone and may differ from the IST date.';
    });
})();
</script>
```

`timeZoneName: 'short'` gives abbreviations like "PDT"/"EST" from the browser's own `Intl` data — no hardcoded US-timezone list needed, and it works for any visitor worldwide, not just the US.

## 3. Slot picker — [resources/views/bookings/slot-selection.blade.php](../../resources/views/bookings/slot-selection.blade.php)

Replace the hardcoded label at [:275-278](../../resources/views/bookings/slot-selection.blade.php#L275-L278):

```blade
<!-- before -->
<div class="inline-flex items-center gap-2 ...">
    <span class="material-icons-round text-lg">public</span>
    <span>India Standard Time (IST)</span>
</div>

<!-- after -->
<div class="inline-flex items-center gap-2 ...">
    <span class="material-icons-round text-lg">public</span>
    <span id="tzLabel">Times shown in your local timezone</span>
</div>
```

Update the slot-rendering JS at [:470-483](../../resources/views/bookings/slot-selection.blade.php#L470-L483) to convert before display, and at [:497-500](../../resources/views/bookings/slot-selection.blade.php#L497-L500) (the `selectTime` confirmation text) similarly:

```js
// before (line ~470)
daySlots.forEach(slot => {
    const t = document.createElement('div');
    t.className = 'time-slot';
    let [hours, minutes] = slot.start.split(':').map(Number);
    const ampm = hours >= 12 ? 'pm' : 'am';
    const displayHours = hours % 12 || 12;
    t.textContent = `${displayHours}:${minutes.toString().padStart(2,'0')}${ampm}`;
    t.dataset.backendTime = slot.start;
    t.addEventListener('click', () => selectTime(slot.start, t));
    timeSlotsDiv.appendChild(t);
});

// after
daySlots.forEach(slot => {
    const t = document.createElement('div');
    t.className = 'time-slot';
    const conv = window.convertIstToVisitorTz(dateStr, slot.start);
    t.textContent = conv.local;
    t.title = `${conv.ist} (India time)`;
    t.dataset.backendTime = slot.start;
    t.addEventListener('click', () => selectTime(slot.start, t));
    timeSlotsDiv.appendChild(t);
});
```

```js
// before (line ~497, inside selectTime)
const [hours, minutes] = time.split(':').map(Number);
const ampm = hours >= 12 ? 'pm' : 'am';
const displayHours = hours % 12 || 12;
const displayTime = `${displayHours}:${minutes.toString().padStart(2,'0')}${ampm}`;
confirmText.textContent = `You've selected ${selectedDate.toDateString()} at ${displayTime}`;

// after
const conv = window.convertIstToVisitorTz(formatLocalDate(selectedDate), time);
confirmText.textContent = `You've selected ${conv.localDate} at ${conv.local} (${conv.ist} India time)`;
```

`window.convertIstToVisitorTz` is defined in the shared partial (§2), which loads before this page-specific script since it's included from the layout after `@stack('scripts')`.

Carry the detected timezone forward into the next step by adding a hidden field to `#slotForm` ([:291-296](../../resources/views/bookings/slot-selection.blade.php#L291-L296)):

```blade
<form method="GET" action="{{ route('bookings.details', $event->slug) }}" id="slotForm">
    <input type="hidden" name="date" id="selectedDate">
    <input type="hidden" name="time" id="selectedTime">
    <input type="hidden" name="timezone" id="selectedTimezone">
    @if(isset($isFollowUp) && $isFollowUp && isset($invite))
        <input type="hidden" name="followup_token" value="{{ $invite->token }}">
    @endif
    ...
</form>
```

And set it once the timezone is detected (top of the `@push('scripts')` block, after the shared partial has run):

```js
document.getElementById('selectedTimezone').value = window.__visitorTz || 'Asia/Kolkata';
```

## 4. Carry timezone through the booking flow

**[app/Http/Controllers/BookingController.php](../../app/Http/Controllers/BookingController.php) — `showDetailsForm()`** ([:238-256](../../app/Http/Controllers/BookingController.php#L238)):

```php
// before
public function showDetailsForm(Event $event, Request $request)
{
    $date = $request->query('date');
    $time = $request->query('time');
    ...
    return view('bookings.details', compact('event', 'date', 'time'));
}

// after
public function showDetailsForm(Event $event, Request $request)
{
    $date = $request->query('date');
    $time = $request->query('time');
    $timezone = $request->query('timezone', 'Asia/Kolkata');
    ...
    return view('bookings.details', compact('event', 'date', 'time', 'timezone'));
}
```

**[resources/views/bookings/details.blade.php](../../resources/views/bookings/details.blade.php)** — add the hidden field to the booking form ([:151-154](../../resources/views/bookings/details.blade.php#L151)):

```blade
<form method="POST" action="{{ route('bookings.store', $event->slug) }}" class="space-y-5">
    @csrf
    <input type="hidden" name="booked_at_date" value="{{ $date }}">
    <input type="hidden" name="booked_at_time" value="{{ $time }}">
    <input type="hidden" name="timezone" value="{{ $timezone }}">
    ...
```

**[app/Http/Requests/StoreBookingRequest.php](../../app/Http/Requests/StoreBookingRequest.php)** — add validation (in `rules()`, alongside the existing fields):

```php
return [
  'booker_name' => 'required|string',
  'booker_email' => 'required|email',
  'booked_at_date' => $dateRules,
  'booked_at_time' => $timeRules,
  'timezone' => 'nullable|string|max:64',
];
```

**[app/Http/Controllers/BookingController.php](../../app/Http/Controllers/BookingController.php) — `store()`** ([:379-390](../../app/Http/Controllers/BookingController.php#L379)):

```php
$booking = $event->bookings()->create([
  'booker_name' => $bookerName,
  'booker_email' => $bookerEmail,
  'phone' => $phone,
  'booked_at_date' => $request->validated('booked_at_date'),
  'booked_at_time' => $request->validated('booked_at_time'),
  'timezone' => $request->input('timezone', 'Asia/Kolkata'),
  'user_id' => $user->id,
  'status' => 'pending',
  'is_followup' => $followUpInvite ? true : false,
  'followup_invite_id' => $followUpInvite ? $followUpInvite->id : null,
  'additional_notes' => $request->input('additional_notes', null),
]);
```

## 5. Server-rendered display pages — progressive-enhancement markup

Wrap the printed date/time in each of these with `data-ist-date`/`data-ist-time` attributes so the shared script (§2) rewrites them on load. Keep the existing PHP/Carbon formatting as the fallback text (in case JS is blocked).

- **[resources/views/bookings/details.blade.php:118,122](../../resources/views/bookings/details.blade.php#L118)**
- **[resources/views/payments/thankyou.blade.php:185,189](../../resources/views/payments/thankyou.blade.php#L185)**
- **[resources/views/user/bookings/reschedule.blade.php:207,211](../../resources/views/user/bookings/reschedule.blade.php#L207)**
- **[resources/views/user/bookings/partials/bookings-grid.blade.php:52,63,71](../../resources/views/user/bookings/partials/bookings-grid.blade.php#L52)**

Pattern for each (exact surrounding markup differs slightly per file — the shape is the same):

```blade
<!-- before -->
<span>{{ Carbon::parse($date)->format('l, F j, Y') }}</span>
<span>{{ Carbon::parse($time, 'UTC')->format('g:i A') }}</span>

<!-- after -->
<span data-ist-date="{{ \Carbon\Carbon::parse($date)->format('Y-m-d') }}"
      data-ist-time="{{ \Carbon\Carbon::parse($time)->format('H:i') }}">
    {{ \Carbon\Carbon::parse($date)->format('l, F j, Y') }}, {{ \Carbon\Carbon::parse($time)->format('g:i A') }} IST
</span>
```

Since `bookings-grid.blade.php` renders a list (one row per booking), apply the `data-ist-*` attributes per-row inside its loop, same pattern.

## 6. Emails — server-side conversion (Carbon)

**[app/Notifications/BookingCreatedNotification.php](../../app/Notifications/BookingCreatedNotification.php) — `toMail()`**, booker branch ([:47-59](../../app/Notifications/BookingCreatedNotification.php#L47)):

```php
// before
if ($notifiable instanceof AnonymousNotifiable) {
  return (new MailMessage)
    ->subject("Booking Confirmation - {$this->booking->event->title}")
    ->view('emails.booking-confirmation', [
      'bookerName' => $this->booking->booker_name,
      'eventTitle' => $this->booking->event->title,
      'bookingDate' => $this->booking->booked_at_date,
      'bookingTime' => $this->booking->booked_at_time,
      'timezone' => $this->booking->timezone ?? 'IST',
      'meetingLink' => $this->booking->meet_link ?? $this->booking->calendar_link,
      'organizerName' => $this->booking->event->user->name,
    ]);
}

// after
if ($notifiable instanceof AnonymousNotifiable) {
  $visitorTz = $this->booking->timezone ?: 'Asia/Kolkata';
  $istMoment = \Carbon\Carbon::parse(
      $this->booking->booked_at_date . ' ' . $this->booking->booked_at_time,
      'Asia/Kolkata'
  );
  $localMoment = $istMoment->copy()->setTimezone($visitorTz);

  return (new MailMessage)
    ->subject("Booking Confirmation - {$this->booking->event->title}")
    ->view('emails.booking-confirmation', [
      'bookerName' => $this->booking->booker_name,
      'eventTitle' => $this->booking->event->title,
      'bookingDate' => $localMoment->format('l, F j, Y'),
      'bookingTime' => $localMoment->format('g:i A'),
      'timezoneAbbr' => $localMoment->format('T'),      // e.g. "PDT"
      'istDate' => $istMoment->format('l, F j, Y'),
      'istTime' => $istMoment->format('g:i A'),
      'meetingLink' => $this->booking->meet_link ?? $this->booking->calendar_link,
      'organizerName' => $this->booking->event->user->name,
    ]);
}
```

The organizer branch (rest of the method) is intentionally left untouched — the astrologer is in India and should always see IST regardless of the customer's timezone.

**[resources/views/emails/booking-confirmation.blade.php:75,88,103](../../resources/views/emails/booking-confirmation.blade.php#L75)** — update to use the new variables:

```blade
<!-- before -->
Date: {{ $bookingDate }}
Time: {{ $bookingTime }}
...
Timezone: {{ $timezone ?? '(GMT+5:30) India Standard Time' }}

<!-- after -->
Date: {{ $bookingDate }}
Time: {{ $bookingTime }} {{ $timezoneAbbr }}
...
Your local time — India time: {{ $istDate }}, {{ $istTime }} IST
```

`$bookingDate`/`$bookingTime` now arrive already converted from the notification class, so the template itself does no conversion — it just labels what it's given.

Check the other transactional templates for the same raw-echo pattern before considering this phase done: `booking-created-organizer.blade.php` (leave in IST — organizer-facing), `booking-declined.blade.php`, `booking-rescheduled.blade.php`, `booking-reminder.blade.php`, `booking-reschedule-request.blade.php`. Any of these sent **to the booker** should get the same `Carbon::parse(...)->setTimezone($booking->timezone)` treatment; any sent **to the organizer** should stay in IST.

## 7. `BookingReminderJob`

**[app/Jobs/BookingReminderJob.php:71](../../app/Jobs/BookingReminderJob.php#L71)** currently has a comment `// booking datetime (server timezone)` — this stays correct and unchanged. The reminder job's internal scheduling math (deciding *when* to fire, e.g. "2 hours before") should keep comparing in server/IST time, since that's an internal scheduling concern, not something the customer sees. If the reminder email itself echoes the booking time to the customer, apply the same Carbon conversion as §6 in whatever template it renders.

## Testing checklist

- [ ] Run the new migration; confirm `bookings.timezone` exists and is nullable.
- [ ] Visit the India event's public page from a browser manually set to `America/Los_Angeles` (Chrome DevTools → Sensors → Location, or OS timezone override) — slot times should show in Pacific time with IST in the tooltip/parentheses.
- [ ] Complete a full test booking under a non-IST browser timezone; confirm the `bookings` row has `timezone` populated with the correct IANA string (e.g. `America/Los_Angeles`).
- [ ] Confirm the confirmation email to the booker shows the converted local time + IST reference; confirm the organizer notification email still shows plain IST.
- [ ] Confirm `details.blade.php`, `thankyou.blade.php`, `reschedule.blade.php`, and the bookings list still show a sensible IST time when JS is disabled (progressive-enhancement fallback).
- [ ] Confirm existing India bookings (browser in IST) show unchanged behavior — local time and IST reference should read identically since the offset is zero.
