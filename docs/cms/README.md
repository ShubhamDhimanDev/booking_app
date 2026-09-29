# Multi-Country CMS: Implementation Plan

Business context: an astrologer selling globally. Each country/region gets its own header, footer, pages and events, all managed from the admin panel. Only the admin uses the panel, so raw HTML/CSS/JS is allowed with **no sanitising**.

**Status: phases 1-5 implemented** (see "Implementation notes" at the bottom for where the build differs from this spec). Tests: `tests/Feature/CmsTest.php`.

## Decisions locked in

| Decision | Choice |
|---|---|
| URL scheme | **Path prefix per country**: `/{country-slug}/{page-slug}`, e.g. `/en-in`, `/en-us/about`, `/en-us/e/{event-slug}`. Extends the existing `/en-in` and `/en-us` routes. One domain. |
| Event ↔ country | **One country has many events; an event belongs to one country.** The event takes its currency from its country by default (the existing `events.currency` column stays and is set from the country, still overridable). |
| Timezone | Visitor's browser timezone first, **country default as fallback**. Admin panel, storage, reminders and refund math stay **IST** (as today; `config/app.php` = `Asia/Kolkata`). |
| Payment gateways | **Unchanged.** Razorpay/PayU chosen by event currency, as in `docs/us-expansion`. |
| Page builder | Ordered **sections** per page, drag-and-drop reorder with **SortableJS** (small, no build step, loadable from CDN or npm). |
| Raw HTML | A dedicated `html` section type; content is output unescaped, including `<style>` and `<script>`. |
| Existing homepage HTML | Migrated: `homepage_html_in` / `homepage_html_us` settings become the home page of the IN/US countries (one `html` section each). Full-document HTML is kept working (see "Full-document pages"). |

## Data model

```
countries
  id, name, slug (unique, e.g. "en-in"), iso_code (IN/US), currency (INR/USD),
  currency_symbol, default_timezone (Asia/Kolkata, America/New_York),
  is_active, is_default, sort_order,
  header_html (nullable), footer_html (nullable),   -- see Header/Footer
  timestamps

pages
  id, country_id → countries, title, slug, is_home (bool),
  status (draft|published), meta_title, meta_description, sort_order, timestamps
  unique (country_id, slug)

page_sections
  id, page_id → pages (cascade), type, content (json), sort_order, is_visible, timestamps

nav_items                       -- header/footer menus per country
  id, country_id → countries, location (header|footer), label, url or page_id,
  sort_order, opens_new_tab

events (existing)  + country_id (nullable FK → countries)
```

- `sort_order` on `page_sections` is what drag-and-drop writes.
- Section `content` is JSON so new section types need no migration.

## Section types (v1)

| Type | Fields |
|---|---|
| `html` | `html` (one big textarea/code editor; raw, unsanitised, may include style/script) |
| `hero` | heading, subheading, image, button label + URL |
| `text` | rich text (or Markdown) |
| `image` | image, alt, link |
| `events` | which of this country's events to list (all / selected); renders the country's currency |
| `faq`, `testimonials` | repeatable items |
| `cta` | heading, button |

Each type is a Blade partial `resources/views/cms/sections/{type}.blade.php` plus an admin form partial `resources/views/admin/cms/sections/{type}.blade.php`. A small registry class (`App\Cms\SectionTypes`) lists types and their fields, so adding a type means adding one entry and two views.

## Header and footer per country

- Header/footer are rendered from `nav_items` (logo, menu links) with an optional **custom HTML override** (`countries.header_html` / `footer_html`), same "paste anything" rule.
- A country layout (`resources/views/layouts/country.blade.php`) wraps every CMS page and the country's event/booking pages, so the header/footer appear on the booking flow too.
- Fallback: no header/footer configured → a minimal default, never a broken page.

## Full-document pages (backward compat)

Today `/en-in` returns the stored HTML as the whole response with no layout. To preserve that:
- If a page's first (and only) visible section is an `html` section whose content begins with `<!doctype` or `<html`, the page is returned as-is with no layout. Otherwise the country layout wraps it.

## Routing

```
GET  /                              detect country (cookie → browser tz/locale → default country) → redirect
GET  /{country}                     country home page      (is_home = true)
GET  /{country}/{page}              CMS page
GET  /{country}/e/{event:slug}      event slot selection (country-scoped)
GET  /{country}/e/{event:slug}/details, POST …/book    existing booking flow, prefixed
```

- Country routes are registered **after** all fixed routes (`/admin`, `/payment`, `/profile`, etc.) and `{country}` is constrained to an existing slug via route-model binding on `countries.slug`, so it cannot swallow other routes.
- Reserved slugs (admin, payment, e, api, profile, login, …) are blocked in validation for both country and page slugs.
- Existing `/e/{slug}` URLs **keep working**: they redirect (301) to `/{event.country.slug}/e/{slug}`, so shared links do not break. Events with no country fall back to the default country.
- The `region` cookie becomes a `country` cookie storing the slug; `/en-in` and `/en-us` continue to work because those are seeded as the first two countries.

## Currency

- `Country.currency` is the default for new events; `EventController` sets `events.currency` from the country on create, admin can override.
- Display goes through one helper, `Money::format($amount, $currency)`, replacing scattered symbol logic (phase 2 of `docs/us-expansion` already threads `currency` through views and emails).
- Event listing on a country's pages only shows that country's events, so visitors only ever see their country's currency.

## Timezone

- Slot times remain IST wall-clock in the DB (unchanged).
- Country pages pass `country.default_timezone` to the existing browser-side conversion from `docs/us-expansion/phase-1`: use the visitor's `Intl` timezone; if unavailable or invalid, use the country default. A small "Times shown in: America/New_York [change]" picker lets visitors override.
- Booking stores the chosen timezone in `bookings.timezone` (already exists), so notification emails already convert correctly.
- Admin panel: everything remains IST, no change.

## Admin UI

New "CMS" group in the admin sidebar:

1. **Countries**: CRUD, active toggle, default toggle, currency + timezone pickers (timezone list from `DateTimeZone::listIdentifiers()`; currency from a short list).
2. **Country → Header/Footer**: menu builder (drag-and-drop `nav_items`) + optional custom HTML.
3. **Pages** (filtered by country): CRUD, publish/draft, set as home, SEO fields.
4. **Page builder**: list of sections, add-section dropdown, drag handle (SortableJS, saves order via `POST /admin/cms/pages/{page}/sections/reorder`), inline edit form per section, visibility toggle, delete, "Preview" button (renders draft).
5. **Events**: new **Country** dropdown; events index gets a country filter (fits the existing search/filter work in `admin/bookings`).

All admin routes go in `routes/admin.php` under the existing admin auth/permission middleware. Admin views follow the existing Blade + Tailwind style in `resources/views/admin`.

## Implementation phases

Each phase ships independently and leaves the site working.

1. **Countries + country-scoped routing.** Migrations, `Country` model, seeder (IN, US from current behaviour), admin CRUD, `/{country}` routing, detection redirect, `/e/{slug}` redirects. `events.country_id` + admin dropdown, backfill existing events by currency (INR → IN, USD → US).
2. **Pages + sections + page builder.** `pages`, `page_sections`, section registry, `html` section first, then the rest; drag-and-drop reorder; migrate `homepage_html_in/us` into home pages; keep `HomepageSettingsController` as a redirect to the home page's editor and then remove it.
3. **Header/footer + country layout.** `nav_items`, layout, wrap CMS pages and booking flow.
4. **Currency + timezone wiring.** `Money` helper, currency from country, country default timezone in the slot-selection JS, timezone picker.
5. **Polish.** Draft preview, page duplication ("copy from another country"), sitemap per country, `hreflang` links between country versions of a page (same page slug).

## Testing checklist

- `/` redirects by cookie, then by browser timezone, then to the default country.
- `/en-in` and `/en-us` render exactly what they render today after migration.
- Unknown `/{country}` → 404; `/admin`, `/payment/...` etc. are not captured.
- Reordering sections persists across reload; hidden sections don't render; drafts return 404 to the public and render in preview.
- A `<script>` in an `html` section runs on the public page.
- Event created under US shows USD on `/en-us`, is absent from `/en-in`, and payment picks the USD gateway.
- Old `/e/{slug}` links 301 to the country URL.
- A US visitor in `America/Los_Angeles` sees slots in PT; one with no detectable zone sees the country default; admin bookings list still shows IST.

## Assumptions to confirm (I proceeded with these defaults)

1. Countries are managed entirely by the admin (no hardcoded list); the first two are seeded as IN and US.
2. Pages can have the same slug in different countries; content is **not** shared between countries (copying is a one-off action in phase 5).
3. Language/translation is out of scope: each country's pages are written in whatever language the admin types. (The slug prefix `en-` is only a naming convention.)
4. Uploaded images go to `storage/app/public` via the existing filesystem config.
5. The booking flow's own pages (slot selection, details, payment, thank-you) stay in their current design but gain the country header/footer.

## Implementation notes (deviations from the spec above)

- **Booking flow URLs stay flat.** Only slot selection moved to `/{country}/e/{event}`; `/e/{slug}/details`, `/e/{slug}/book`, `/payment/...` keep their URLs. They pick up the country header/footer/timezone default from the event via a view composer on `layouts.app` (`AppServiceProvider`).
- **`countries.currency_symbol` and `nav_items.page_id` do not exist / differ**: the symbol comes from `config/cms.php` (`currencies`), which is also the list of currencies admins can choose.
- **One generic admin form per section type**, generated from `App\Cms\SectionTypes` field definitions, instead of one Blade form per type. Public rendering is still one partial per type in `resources/views/cms/sections/`.
- **Menus** are saved with one form (rows renumbered after each drag) rather than an AJAX call per drag; **section order** is saved via AJAX on drop.
- **Detection at `/`**: `country` cookie -> `CF-IPCountry` header (Cloudflare) -> browser timezone/locale on the detect page -> default country.
- **Homepage Settings removed.** Its data was moved by migration `2026_09_29_000002` into each country's `Home` page (one raw-HTML section). `/admin/homepage-settings` redirects to `/admin/pages`. Full-document HTML (starts with doctype/html) is still served with no layout, so existing homepages render as before.
- **Preview**: admins can open drafts / inactive countries with `?preview=1`.
- **Sitemap** (phase 5) was not built; hreflang alternates and page duplication ("Copy to" country) were.
- **Image uploads** use the `public` disk; run `php artisan storage:link` on the server once.
- Cutover: run `php artisan migrate`. Existing events are assigned to a country by currency (INR -> India, USD -> US, anything else -> default).
- A timezone picker (visitor override, stored in `localStorage`) appears above the booking pages; order is picker choice -> browser timezone -> country default -> IST.
