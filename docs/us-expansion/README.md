# India → US Expansion: Implementation Plan

Business context: a single astrologer, currently running one event (India, INR, IST, PayU/Razorpay). Expanding to serve US customers as a **second, separate event** — its own price (USD), its own PayU currency, and slot times that display correctly in each visitor's own local timezone regardless of which event they're viewing.

This directory documents four independent, sequentially-buildable phases. Each phase doc contains the full migration, code, and Blade/JS changes needed for that phase, plus a testing checklist. Nothing in this directory has been applied to the codebase yet — these are specs to implement against.

## Decisions locked in (from planning discussion)

| Decision | Choice |
|---|---|
| Event structure | **Two separate `Event` records** — India (existing) and US (new). Each gets its own public URL (`/e/{slug}`) that the astrologer shares on the matching marketing channel. No shared listing page exists to route between them (confirmed via `routes/web.php`), so no auto-redirect logic is needed. |
| Timezone detection | Browser-side `Intl.DateTimeFormat().resolvedOptions().timeZone` — applies to **both** events uniformly, since a "US event" audience still spans multiple US zones (Eastern ≠ Pacific). |
| Underlying time storage | **Unchanged.** `config/app.php` stays `Asia/Kolkata`; every `date`/`time` column keeps meaning "IST wall clock" for both events. Timezone conversion is a display-layer feature only — it does not touch refund-window math or the reminder job. |
| USD pricing | Astrologer sets a **fixed USD price manually** on the US event. No live FX conversion. |
| Region/currency routing | **IP-based, but scoped down**: since each event already has its own URL, we don't need IP routing to decide *which event* to show. What actually matters is currency/gateway selection at payment time, which is driven by **which event was booked** (its `currency` column), not by IP at all. IP-based "you look like you're in the US" nudges are optional/deferred — see Phase 4 notes. |
| US payment gateway | **PayU**, via PayU's existing "International/Cross-Border Payments" product on the astrologer's current merchant account — confirmed to support USD ([payu.in/international-payments](https://payu.in/international-payments/)). Requires a business-side activation step with PayU (Phase 3, Step 0) before any code goes live. |

## Phases

1. **[Phase 1 — Automatic per-visitor timezone display](phase-1-timezone-display.md)**
   Ship first. Zero risk to India/payments — pure display layer plus one new nullable column. Makes both the existing India event and the future US event show correct local times to every visitor.

2. **[Phase 2 — Currency support (schema + display)](phase-2-currency-support.md)**
   Adds a `currency` column to `events`, threads it through booking/payment records, Blade views, **and three transactional emails** (follow-up-invite/invitation price emails, refund-processed email — the latter also fixes a pre-existing doubled-`₹` bug). Additive — the India event defaults to `INR` and is unaffected.

3. **[Phase 3 — USD payments via PayU](phase-3-payu-usd-gateway.md)**
   Smallest code footprint of all four phases (PayU's form-post already forwards arbitrary fields, so this is close to a one-line service change) — but **blocked on a business step**: PayU must activate cross-border payments on the merchant account first. Start that request in parallel with Phase 1/2 development.

4. **[Phase 4 — Creating and launching the US event](phase-4-us-event-launch-runbook.md)**
   No new code — an admin runbook for creating the second `Event` record once Phases 1–3 are live, plus a pre-launch test checklist.

## Suggested sequencing

```
Now         Astrologer contacts PayU re: cross-border activation (Phase 3, Step 0)
            ─────────────────────────────────────────────────────────▶ (runs in background, take days)
Day 1–2     Build + test Phase 1 (timezone display)
Day 2–4     Build + test Phase 2 (currency schema/display)
Day 4+      Once PayU confirms activation → build + test Phase 3
Day 5+      Phase 4: create the real US event, run a live sandbox test booking, go live
```

## Out of scope / flagged separately

- **`PayUService::verifyHash()` currently has a hardcoded `return true;` bypass** ([app/Services/PayUService.php:122](../../app/Services/PayUService.php#L122)) — webhook signatures aren't actually verified right now. Not part of this expansion, but should be fixed before real USD transactions rely on the same webhook path. Not covered further in these docs — raise as its own fix.
- **Tax/compliance** (FEMA/RBI/GST implications of an India-registered individual accepting USD from US customers) is outside code scope — the astrologer should confirm with an accountant. Not covered in these docs.
