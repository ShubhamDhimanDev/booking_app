# Phase 4 — Creating and Launching the US Event

No code in this phase — it's the admin workflow for actually standing up the second event once [Phase 1](phase-1-timezone-display.md), [Phase 2](phase-2-currency-support.md), and [Phase 3](phase-3-payu-usd-gateway.md) are built and tested.

## Prerequisite

Phases 1–3 deployed, and PayU has confirmed production cross-border activation (Phase 3, Step 0). Don't create the real, publicly-linked US event until PayU is confirmed live — test it in the admin panel with `PAYU_ENVIRONMENT=test` first.

## 1. Create the event

Using the existing admin "create event" form (no new tooling needed — this is the same form used for the India event, now with the currency selector from Phase 2):

- **Title / description**: distinct from the India event (e.g. "Astrology Consultation — US").
- **Slug**: distinct URL, e.g. `astrology-consultation-usa`, so the astrologer can share a dedicated link on US-facing marketing channels.
- **Currency**: `USD`.
- **Price**: the fixed USD price the astrologer has decided on (Phase 2 locked in "manual fixed price," not FX-converted from the INR price).
- **Available dates / weekdays**: same mechanism as the India event.
- **Time slots (`custom_timeslots`)**: still entered and stored as **IST**, same as every other event (Phase 1 keeps this assumption unchanged app-wide) — but *chosen* with US hours in mind. E.g. IST evening (6–10 PM IST) lands in US morning/midday across most zones (Eastern: 8:30–12:30 AM previous day is bad; better to test actual conversions — see the note below).

**Concretely picking US-friendly slots**: use the same conversion Phase 1 ships (`Intl.DateTimeFormat` with a US timezone) to sanity-check candidate IST slots before publishing. Example reference points (IST is UTC+5:30, no DST; US zones do observe DST so offsets shift ~half the year):

| IST time | US Eastern (approx) | US Pacific (approx) |
|---|---|---|
| 6:00 PM IST | 8:30 AM | 5:30 AM |
| 8:00 PM IST | 10:30 AM | 7:30 AM |
| 10:00 PM IST | 12:30 PM | 9:30 AM |
| 12:00 AM IST (midnight) | 2:30 PM | 11:30 AM |

These are approximate and will shift by an hour depending on US DST — don't hardcode them into any decision; use them only as a starting point for picking a slot window, then verify against the live Phase 1 conversion once the event exists.

- **Refund policy / reminders**: astrologer's choice, independent of the India event's settings — these are already per-event ([Event.php refund_policy_type, refund_rules, reminders relation](../../app/Models/Event.php#L114)).

## 2. PayU sandbox test booking

Before sharing the real link publicly:

1. Set `PAYU_ENVIRONMENT=test` (or confirm it already is).
2. Book the new US event end-to-end as a test customer, with a browser timezone set to a US zone (see Phase 1's testing checklist for how).
3. Confirm: slot times displayed in the picker matched the visitor's local zone; the `$` symbol appeared throughout checkout; the PayU test transaction succeeded with `currency=USD`; the confirmation email arrived showing the visitor's local time with IST as reference; the `bookings` row has the right `timezone`, and the `payments` row has `currency = 'USD'`.
4. Only after this passes cleanly, and PayU has confirmed production cross-border approval, flip to `PAYU_ENVIRONMENT=production` and consider the event ready to share.

## 3. Optional: lightweight region nudge (deferred, not required for launch)

The original planning discussion considered IP-based country detection for routing visitors to the right event automatically. Since each event already has its own dedicated URL ([routes/web.php:55](../../routes/web.php#L55) → `/e/{event:slug}`) and the astrologer controls distribution by which link he shares where, this isn't required for launch — a visitor lands on whichever event's link they clicked.

If, later, both links end up being shared in overlapping channels (e.g. a shared homepage, or social profile bio with both links visible) and cross-traffic becomes a real problem (e.g. Indian visitors landing on the USD-priced US event by mistake), a lightweight addition would be: an IP-country lookup on each event page that shows a **dismissible banner** ("Looks like you're in India — did you mean our India pricing? [link]") rather than a forced redirect. A forced redirect risks blocking legitimate cross-border customers (an NRI who wants USD pricing, a US-based Indian who wants the India-priced event, a traveler). Not building this now — revisit only if cross-traffic turns out to be an actual problem in practice.

## Pre-launch checklist

- [ ] Phase 1, 2, 3 all deployed and their individual testing checklists passed.
- [ ] PayU has confirmed production cross-border activation.
- [ ] US event created with correct currency, price, and US-friendly slot times (verified via live conversion, not the approximate table above).
- [ ] Full sandbox test booking completed successfully (§2).
- [ ] `PAYU_ENVIRONMENT` flipped to `production` only after the above passes.
- [ ] Astrologer has the distinct booking link ready to share on US-facing marketing channels.
