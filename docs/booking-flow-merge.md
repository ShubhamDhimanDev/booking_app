# Merge slot-selection and details into one booking page

Status: **implemented on branch `astrochaitnya` (uncommitted at time of writing).** See "Implementation notes" at the end for what differs from the plan.
Written 2026-10-27 against branch `astrochaitnya` (HEAD `9a4e9eb`).
Updated 2026-10-27 with the owner's answers to the open questions (section 10).

## 1. Goal

Today a visitor books in two page loads:

1. **Slot page** — pick a date and time.
2. **Details page** — enter name, email, phone and notes. Then continue to payment.

Merge these into **one page** at the event URL. The visitor picks a slot and fills in their details without leaving the page, then submits straight to `bookings.store`.

Why: one fewer page load and one fewer drop-off point. Also fewer files to keep in sync, since both views repeat the same event-info card and progress bar.

## 2. How it works today

```
GET  /{country}/e/{slug}          EventController::showInCountry → renderPublic()   → bookings/slot-selection.blade.php
GET  /e/{slug}                    EventController::showPublic   → 301 to the above when the event has an active country
GET  /followup/{token}            BookingController::showFollowUpBooking            → bookings/slot-selection.blade.php  (same view)
        │  "Next: Enter Details" = GET form  (date, time, timezone, followup_token)
        ▼
GET  /e/{slug}/details            BookingController::showDetailsForm                → bookings/details.blade.php
        │  POST form  (booked_at_date, booked_at_time, timezone, followup_token, booker_*, phone, additional_notes)
        ▼
POST /e/{slug}/book               BookingController::store (StoreBookingRequest)
        ├─ price == 0  → confirm, Google event, notify → payment.thankyou
        └─ price  > 0  → pending booking → payment.page
```

Routes: [routes/web.php:63-65](../routes/web.php#L63-L65) (flat `/e/...`, all with `event.country` middleware) and [routes/cms.php](../routes/cms.php) (`country.event`).

### Files involved

| File | Role |
|---|---|
| [resources/views/bookings/slot-selection.blade.php](../resources/views/bookings/slot-selection.blade.php) (521 lines) | Calendar and slot picker, follow-up banner, event-info card. Its JS renders the calendar from `availableSlots`, weekdays and exclusions, and converts IST slots to the visitor's timezone. |
| [resources/views/bookings/details.blade.php](../resources/views/bookings/details.blade.php) (254 lines) | Event-info card (copy of the one above), "Selected Time" card, the details form, the `InitiateCheckout` / `begin_checkout` tracking scripts, and a "Back" link. |
| [app/Http/Controllers/Admin/EventController.php:168-253](../app/Http/Controllers/Admin/EventController.php#L168-L253) | `renderPublic()` builds slot data for the public page. |
| [app/Http/Controllers/BookingController.php:279-298](../app/Http/Controllers/BookingController.php#L279-L298) | `showDetailsForm()` validates `date`/`time` query params and renders the details view. |
| [app/Http/Controllers/BookingController.php:305-509](../app/Http/Controllers/BookingController.php#L305-L509) | `store()` re-validates availability, finds or creates the user, creates the pending booking, and branches to free or paid. |
| [app/Http/Controllers/BookingController.php:1156-1243](../app/Http/Controllers/BookingController.php#L1156-L1243) | `showFollowUpBooking()` builds slot data for follow-up invites. Reuses `slot-selection`. |
| [app/Http/Requests/StoreBookingRequest.php](../app/Http/Requests/StoreBookingRequest.php) | Validates date, time, name, email and timezone. |
| [app/Providers/AppServiceProvider.php:30-36](../app/Providers/AppServiceProvider.php#L30-L36) | View composer that gives `layouts.app` the `$cmsCountry` for the event in scope (header, footer, timezone default). |
| [resources/views/partials/timezone-convert.blade.php](../resources/views/partials/timezone-convert.blade.php) | Defines `window.__visitorTz` and `window.convertIstToVisitorTz()`. Loaded by the layout. |

## 3. Research findings that shape the plan

1. **`slot-selection` has two producers.** `renderPublic()` (normal flow) and `showFollowUpBooking()` (invite flow, including the free session that comes with a store order). Both must render the merged page. The follow-up flow carries `followup_token` via the GET form into the details page, and from there into the POST. In a merged page it is just a hidden field on the one form.

2. **`details` has a single entry point.** Only the slot form links to it, so it is easy to retire. Other references:
   - `tests/Feature/EventTrackingScriptsTest.php:55-69` requests it directly.
   - `tests/Feature/CmsTest.php` (country-block test) asserts `/e/india-only/details` returns 403 for a US visitor.
   - The `BookingController::showDetailsForm` redirects back to `events.show.public` on bad input.
   - The `docs/us-expansion/phase-1-timezone-display.md` doc describes both pages.

3. **The three slot builders have drifted apart.**

   | | `renderPublic` | `showFollowUpBooking` | `showRescheduleForm` / `routes/api.php` |
   |---|---|---|---|
   | Hides slots starting within 2 h | yes | no | no |
   | Counts the owner's bookings on other events as taken | yes | no | no |
   | Server-side weekday skip | no (JS does it) | yes | yes |
   | Server-side exclusions | no (JS does it) | yes | no (JS does it) |

   `store()` enforces owner-wide availability (check 6) but the follow-up page does not show it, so a follow-up visitor can pick a slot that `store()` then rejects. The owner chose to keep this behaviour as it is, so the merge does not change it (decision D6).

4. **`store()` returns JSON on failure.** Its manual checks `return response()->json(['error' => …], 422|409)`. The details form is a plain browser POST, so a visitor who hits one of these (for example someone else booked the slot meanwhile) sees raw JSON. `StoreBookingRequest` failures do redirect back with errors. The merged page must handle both, and it is the natural place to fix the JSON case (Phase 3).

5. **Tracking moves.** `InitiateCheckout` (Meta) and `begin_checkout` (GA) fire on details-page load via `TrackingService::getEventScript` / `getGoogleEventScript` (`details.blade.php:13-26`). The admin tracking screen describes it as "User starts booking details form". After the merge there is no "details page load". Fire it once, the first time a slot is selected. `TrackingService::getInlineTrackingCode` and `getGoogleInlineTrackingCode` already exist for use in JS handlers.

6. **Progress bars disagree.** The slot page shows *Details → Payment → Confmration* (typo, and "Details" is the active step). The details page shows *Select Time ✓ → Details → Payment*. The merged page should show one bar: **Choose time & details → Payment → Confirmation**.

7. **Timezone display differs.** The slot page converts to the visitor's timezone via `convertIstToVisitorTz`. The details page prints the IST time as plain text (the last commit, `9a4e9eb`, removed the conversion there on purpose, to stop showing two times). In the merged page the "Selected time" summary should show the visitor-local time only, using the same helper the slot picker already uses. The hidden `booked_at_time` stays in IST, because slot times are IST wall-clock everywhere (see `docs/us-expansion/README.md`, "Underlying time storage").

8. **Back-navigation after a validation error.** `StoreBookingRequest` failure does `redirect()->back()->withInput()`. Today "back" is the details page (its URL carries `?date=&time=`). In the merged page "back" is the event page, so it must restore the selection from `old('booked_at_date')` / `old('booked_at_time')`. This is the main new piece of front-end logic.

9. **Unused data.** `renderPublic()` passes `activeGateway` and `gatewayConfig` to the view. The view does not use them.

### Pre-existing issues noticed (not fixed by this plan)

- The slot picker's "hide today's past slots" uses the browser clock against IST slot times (`new Date(dateStr + 'T' + s.end)`). For a non-IST visitor this is wrong near the day boundary. The 2 h cutoff in `renderPublic` partly masks it.
- `phone` is not in `StoreBookingRequest::rules()` (read via `$request->phone`). It is marked `required` in HTML only, while the label has no `*`.
- Every submit creates a new `pending` booking, so double-submits and back-button retries create duplicates. Not new, but the merged page should disable the button on submit.
- `tests/Feature/Controllers/BookingsControllerTest.php` appears stale (Inertia assertions and a `"Slot booked successfully!"` flash that `store()` no longer sets). I have not run the suite. Check which tests currently pass before starting, so regressions can be told from existing failures.

## 4. Design decisions

| # | Decision | Recommendation | Alternative |
|---|---|---|---|
| D1 | **Layout** | Keep the existing two-column shell: left = event info card; right = calendar → time slots → **details form, always visible below the slots**. Submit stays disabled until a slot is chosen. When a slot is chosen, the page smooth-scrolls down to the details form. A "Selected time" chip at the top of the form shows the choice, with a *Change* link back to the calendar. | A JS wizard that swaps the card between "pick time" and "your details". Rejected by the owner. |
| D2 | **URL** | Merged page lives at the existing event URL (`/{country}/e/{slug}`, legacy `/e/{slug}`). No new route. | A new route name. Not needed. |
| D3 | **`/e/{slug}/details`** | **Remove it outright** (owner decision). Delete the `bookings.details` route, `showDetailsForm()` and `details.blade.php` in the same PR. Its only inbound link was the slot form. Git history is the rollback. | Keep a redirect for one release. Rejected: not worth the code. |
| D4 | **Restoring the selection** | After a failed submit, the page restores the slot from `old('booked_at_date')` / `old('booked_at_time')` if it is still selectable. No query-string pre-selection is needed now that `/details` is gone. | `?date=&time=` pre-selection. Dropped. |
| D5 | **Follow-up page** | `/followup/{token}` renders the same merged view with the banner on top; `followup_token` becomes a hidden input on the one form. | None. |
| D6 | **Slot-data builders** | **Leave all three as they are** (owner decision: keep current functionality). No service extraction, no change to the 2 h cutoff or owner-wide blocking on the follow-up page. The mismatch in finding 3 stays a known, separate issue. | Extract a shared `SlotAvailability` service. Dropped from this plan. |
| D7 | **Store failures** | Convert the manual checks in `store()` from JSON responses to `back()->withInput()->withErrors(...)`, with a distinct error key `slot` for conflicts so the page can clear the selection and show "That slot was just taken". | Submit via `fetch` and handle JSON. More JS, and loses native form behaviour. |

## 5. Target page

```
┌ progress:  ● Choose time & details  ──  ○ Payment  ──  ○ Confirmation ┐

┌ follow-up banner (only on /followup/{token}) ───────────────────────┐

┌ event info card ┐  ┌ Select Date & Time ─────────────────────────┐
│ avatar, title   │  │  < Month >   calendar grid                  │
│ duration        │  │  time slots (visitor-local)                 │
│ description     │  │  tz label                                   │
│                 │  ├ Your Details  (always visible) ─────────────┤
│                 │  │  [chip: Wed, Oct 29 · 8:00 AM PDT  Change]  │
│                 │  │  Full name *  Email *  Phone *  Notes       │
│                 │  │  [ Continue to Payment ]  (disabled until a slot is chosen)
└─────────────────┘  └─────────────────────────────────────────────┘
```

Behaviour of the page:
- **Nothing selected:** the details form is visible and its fields can be filled in. The chip reads "No time selected yet", and the submit button is disabled with the hint "Choose a date and time above".
- **Date picked:** the time slots for that date render in place (as today). On narrow screens, scroll the slot list into view if it is below the fold.
- **Time slot picked** (date + time now complete): fill the hidden inputs, update the chip, enable submit, and **smooth-scroll to the details form**. The scroll only fires when a slot is chosen, not on every click on the calendar, because a booking needs both a date and a time. If you meant "scroll as soon as a date is clicked", it is a one-line move into `selectDate()`.
- **Name/email/phone** are prefilled from `auth()->user()` as `details.blade.php` does today.
- **After a server error:** slot restored from `old()`, form fields repopulated, error banner at the top of the details form, and the page scrolls to it.

## 6. Implementation steps

### Phase 0 — dropped

The slot-builder extraction was dropped (decision D6): the public page, follow-up page and reschedule keep their current availability rules. Phases below keep their numbers.

### Phase 1 — Merged view

1. Create `resources/views/bookings/book.blade.php` by starting from `slot-selection.blade.php` . Move shared partials out so nothing is copy-pasted again:
   - `bookings/partials/event-card.blade.php` — avatar, title, duration, description.
   - `bookings/partials/progress.blade.php` — the single progress bar.
   - `bookings/partials/followup-banner.blade.php` — banner currently inline at `slot-selection.blade.php:196-223`.
2. Replace the GET `#slotForm` (`slot-selection.blade.php:281-303`) with the POST form from `details.blade.php:149-249`, targeting `route('bookings.store', $event->slug)`, with `@csrf` and hidden `booked_at_date`, `booked_at_time`, `timezone` (`window.__visitorTz`), and `followup_token` when `$invite` is present.
3. Put the form in `#detailsBlock` below `#timeSlots`, visible from the start. Render the submit button `disabled`. `selectTime()` fills the hidden inputs, updates the "Selected time" chip, enables submit, and calls `detailsBlock.scrollIntoView({ behavior: 'smooth', block: 'start' })`. Add a `scroll-margin-top` to `#detailsBlock` equal to the sticky header height (the header is `sticky top-0`). Changing the date clears the selection: disable submit again and reset the chip.
4. Chip text uses `convertIstToVisitorTz(date, time)` (`local` and `localDate`), as `selectTime()` does today for `confirmText`.
5. **Restore after error:** after `renderCalendar()`, read `@json(old('booked_at_date'))` / `@json(old('booked_at_time'))`. If that date is selectable, move `currentMonth` to it, call `selectDate`, then `selectTime`. If not (taken or past), ignore silently, or show the slot-taken message from D7.
6. Disable the submit button and show the loader on submit (in addition to the "no slot yet" disabled state, guard the submit handler so an empty `booked_at_date` / `booked_at_time` never posts) (the layout already supports `@section('loader')`).
7. Mark phone as required in the label to match the existing `required` attribute.
8. Keep the `.schedule .date` / `.time-slot` `all: unset` CSS reset and the calendar styles as they are. This plan does not restyle.
9. Drop the unused `activeGateway` / `gatewayConfig` from `renderPublic`'s `compact(...)`.

### Phase 2 — Tracking

- Remove `@push('head-scripts')` from the details view; do not re-add it on page load.
- **Decision (owner: "whatever is good for tracking"):** fire on the first slot selection, once per page view. Reasoning: the old trigger was the details page load, which only happened after a slot had been chosen, so "first slot selected" is the closest equivalent in both meaning and volume. It also keeps the Meta/GA funnel (`ViewContent`/page view → `InitiateCheckout` → `ViewPaymentPage` → `Purchase`) comparable with past data. Firing on submit would undercount, because the `ViewPaymentPage` event on the next page would sit almost on top of it. Add the once-only JS hook in `selectTime()`:

  ```php
  const trackStart = (() => { let done = false; return () => {
      if (done) return; done = true;
      {!! \App\Services\TrackingService::getInlineTrackingCode('InitiateCheckout', [...same params...], $event) !!}
      {!! \App\Services\TrackingService::getGoogleInlineTrackingCode('begin_checkout', [...], $event) !!}
  }; })();
  ```

- Admin copy in `resources/views/admin/tracking/index.blade.php` (lines ~103 and ~300) says "User starts booking details form" and "User starts booking". Reword to "User selects a time slot".
- **Heads-up for marketing:** the event now fires when the slot is picked rather than on the next page load. Volume should be close to unchanged, but visitors who pick a slot and never press the button are now counted. Note the deploy date so the before/after is clear in Pixel/GA.

### Phase 3 — Controllers and errors

- `EventController::renderPublic()` and `BookingController::showFollowUpBooking()` return `view('bookings.book', …)`.
- Delete `BookingController::showDetailsForm()` and the `bookings.details` route ([routes/web.php:64](../routes/web.php#L64)). Delete `bookings/details.blade.php` and `bookings/slot-selection.blade.php`.
- `store()` — replace the manual JSON error returns (checks 1-6 at `BookingController.php:330-390`, and the follow-up invite check at `:314`) with `back()->withInput()->withErrors(['slot' => '…'])`. Use `slot` for 409 conflicts, and `booked_at_date` / `booked_at_time` for range/weekday/exclusion failures so the existing error list shows them. The success paths are unchanged.
- `StoreBookingRequest`: add `'phone' => ['required', 'string', 'max:32']` and `'additional_notes' => ['nullable', 'string', 'max:2000']`. Confirm first that no caller posts without phone (the factory and tests do).
- Update the "Back" behaviours: `payments/failed.blade.php:157` already links to `events.show.public`; that now lands on the merged page, which is correct.

### Phase 4 — Doc cleanup

- Update `docs/us-expansion/phase-1-timezone-display.md`, which references both deleted views and their line numbers.
- Grep for leftover references to `bookings.details`, `showDetailsForm`, `bookings.slot-selection` and `bookings/details` before finishing.

## 7. Tests

Update:
- `EventTrackingScriptsTest::test_booking_details_page_renders_the_events_own_tracking_scripts` → request the event page and assert the event's Pixel/GA IDs are present. The inline `InitiateCheckout` string is now inside the page's JS, so assert `assertSee('InitiateCheckout', false)` on the event page, and that `ViewPaymentPage` etc. are unchanged.
- `CmsTest` country-block test: remove the `/e/india-only/details` 403 assertion (the route is gone). The page and `POST /e/india-only/book` assertions stay.

Add (`tests/Feature/BookingFlowTest.php`):
- Event page contains the form posting to `bookings.store` and the hidden `booked_at_*` inputs.
- `/e/{slug}/details` returns 404.
- Event page with `old()` input (flash via `withSession`/`withInput`) renders the restore values.
- `store()` conflict (slot taken) redirects back with a `slot` error instead of JSON.
- `/followup/{token}` renders the form with a hidden `followup_token`. `StoreTest` already asserts "Your Free Session" there.

Manual checks (browser):
- India visitor (IST) and US visitor (e.g. `America/Los_Angeles`, with the tz picker): selected-time chip shows local time, and the stored booking time is the IST slot.
- Slot taken between page load and submit → error shown, selection cleared.
- Free event and paid event: free → thank-you; paid → payment page.
- Follow-up invite with price 0 and with a custom price; expired invite still shows `followup-expired`.
- Logged-in user prefill; guest creates account (`NewUserCredentials` mail queued).
- Mobile width: details block scrolls into view, no horizontal scroll.
- Dark mode.
- Per-event Pixel/GA IDs: `InitiateCheckout` fires once, on first slot pick, not on page load.

## 8. Risks and mitigations

| Risk | Mitigation |
|---|---|
| Visitor has the old details page open during deploy | Their POST still works: `store()` keeps the same field names. Only a reload of `/details` would 404. |
| Funnel metrics change (finding 5) | Announce it, and note the date in the tracking screen description. |
| Follow-up flow regression | Covered by the `StoreTest` case and the new follow-up form test. The `followup_token` field name is unchanged. |
| `old()` restore picks a slot that is no longer valid | The pre-selection code only selects a slot that exists in the rendered calendar. |
| Auto-scroll hidden under the sticky header, or jumpy on mobile | `scroll-margin-top` on `#detailsBlock`; test on a phone-width viewport. |
| Visitor fills the form first and never picks a slot | Submit stays disabled with a hint; server-side validation still rejects an empty slot. |
| Large single view gets unwieldy | Extract partials in Phase 1 (event card, progress, banner). |

## 9. Suggested order and size

One PR for Phases 1-4. Nothing is kept for a later release; git is the rollback.

## 10. Decisions from the owner

| Question | Answer | Effect on the plan |
|---|---|---|
| Form appears after slot pick, or always visible? | Always visible; scroll down to the details once the date is picked. | D1 and section 5 updated. Scroll fires when the slot (date + time) is complete. |
| `InitiateCheckout` timing | "Whatever is good for tracking." | Fire once on first slot selection (Phase 2 explains why). |
| Follow-up visitors: 2 h cutoff and owner-wide blocking? | Keep functionality as it is. | Phase 0 and `SlotAvailability` dropped (D6). |
| Cover the reschedule page? | Only if it has a details page. | It does not: `user/bookings/reschedule.blade.php` goes straight from slot pick to `POST user.bookings.reschedule` with no details step. **Out of scope.** |
| Keep a `/details` redirect for a release? | If it has no use, remove it. Git can revert. | D3: route, controller method and both old views are deleted in the same PR. No redirect. |

## 11. Implementation notes

What was built, and where it differs from the plan above:

- `resources/views/bookings/book.blade.php` is the merged page (renamed from `slot-selection.blade.php`). Shared pieces are in `resources/views/bookings/partials/` (`event-card`, `progress`, `followup-banner`). `details.blade.php` is deleted.
- `bookings.details` route and `BookingController::showDetailsForm()` are removed. `/e/{slug}/details` now 404s.
- `EventController::renderPublic()` and `showFollowUpBooking()` render `bookings.book`. The unused gateway variables were dropped from `renderPublic()`.
- `BookingController::store()` no longer returns JSON for failures. A new `bookingError()` helper redirects back with input and an error: `slot` for conflicts, `booked_at_date` / `booked_at_time` for range, weekday and exclusion failures.
- `StoreBookingRequest` now validates `phone` (required, max 32) and `additional_notes` (max 2000).
- Tracking: `InitiateCheckout` / `begin_checkout` fire once, on the first slot selection, via the inline tracking helpers. Admin tracking screen wording updated.
- The submit button reads "Confirm Booking" when the price (event or follow-up) is 0, otherwise "Continue to Payment".
- Small extras: the selected-time chip shows a full date in the visitor's timezone; the calendar keeps the selected date highlighted when paging months; `currentMonth` starts on day 1 so paging from the 29th-31st cannot skip a month; `pageshow` re-enables submit after a back-button restore.
- Tests: `tests/Feature/BookingFlowTest.php` added; `EventTrackingScriptsTest`, `CmsTest` and `StoreTest` updated.
- Not done: `docs/us-expansion/phase-1-timezone-display.md` still describes the old two-page files. It is a historical spec; a note at its top points here.
