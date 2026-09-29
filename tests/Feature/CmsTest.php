<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Event;
use App\Models\Page;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CmsTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    protected function homeOf(string $slug): Page
    {
        return Country::where('slug', $slug)->first()->pages()->where('is_home', true)->first();
    }

    protected function makeEvent(User $user, Country $country, string $slug, string $currency, float $price, string $title = 'T'): Event
    {
        return Event::create([
            'user_id' => $user->id, 'country_id' => $country->id, 'title' => $title, 'slug' => $slug,
            'price' => $price, 'currency' => $currency, 'duration' => 30,
            'available_from_date' => now()->toDateString(), 'available_to_date' => now()->addDays(5)->toDateString(),
        ]);
    }

    public function test_default_countries_are_seeded_by_migration()
    {
        $this->assertSame(['en-in', 'en-us'], Country::orderBy('sort_order')->pluck('slug')->all());
        $this->assertTrue(Country::where('slug', 'en-in')->first()->is_default);
    }

    public function test_root_shows_detect_page_without_cookie()
    {
        $this->get('/')->assertOk()->assertViewIs('home.detect');
    }

    public function test_root_redirects_by_country_cookie()
    {
        $this->withCookie('country', 'en-us')->get('/')->assertRedirect(url('/en-us'));
    }

    public function test_root_redirects_by_cloudflare_country_header()
    {
        $this->withHeader('CF-IPCountry', 'US')->get('/')->assertRedirect(url('/en-us'));
    }

    public function test_redetect_bypasses_cookie()
    {
        $this->withCookie('country', 'en-in')->get('/?redetect=1')->assertOk()->assertViewIs('home.detect');
    }

    public function test_country_home_sets_cookie_and_renders_layout_when_empty()
    {
        $this->get('/en-in')->assertOk()->assertCookie('country', 'en-in')->assertSee('cms-header', false);
    }

    public function test_unknown_country_is_404_and_inactive_country_is_404()
    {
        $this->get('/xx-zz')->assertNotFound();
        Country::where('slug', 'en-us')->update(['is_active' => false]);
        $this->get('/en-us')->assertNotFound();
    }

    public function test_full_document_html_is_returned_as_is_without_layout()
    {
        $this->homeOf('en-in')->sections()->create(['type' => 'html', 'content' => ['html' => '<h1>Only this</h1>'], 'sort_order' => 0]);
        // Not a full document -> wrapped in the country layout
        $this->get('/en-in')->assertOk()->assertSee('<h1>Only this</h1>', false)->assertSee('cms-header', false);

        $page = $this->homeOf('en-us');
        $doc = '<!doctype html><html><head><script>var x=1;</script></head><body>Hi</body></html>';
        $page->sections()->create(['type' => 'html', 'content' => ['html' => $doc], 'sort_order' => 0]);
        $this->assertSame($doc, $this->get('/en-us')->getContent());
    }

    public function test_sections_render_in_order_and_hidden_ones_are_skipped()
    {
        $page = $this->homeOf('en-in');
        $page->sections()->create(['type' => 'html', 'content' => ['html' => '<p>SECOND</p><script>window.ran=1</script>'], 'sort_order' => 2]);
        $page->sections()->create(['type' => 'html', 'content' => ['html' => '<p>FIRST</p>'], 'sort_order' => 1]);
        $page->sections()->create(['type' => 'html', 'content' => ['html' => '<p>HIDDEN</p>'], 'sort_order' => 3, 'is_visible' => false]);

        $body = $this->get('/en-in')->assertOk()->getContent();
        $this->assertLessThan(strpos($body, 'SECOND'), strpos($body, 'FIRST'));
        $this->assertStringContainsString('<script>window.ran=1</script>', $body);
        $this->assertStringNotContainsString('HIDDEN', $body);
    }

    public function test_draft_pages_are_404_publicly_but_previewable_by_admin()
    {
        $india = Country::where('slug', 'en-in')->first();
        $page = $india->pages()->create(['title' => 'About', 'slug' => 'about', 'status' => 'draft']);
        $page->sections()->create(['type' => 'html', 'content' => ['html' => '<p>About us</p>'], 'sort_order' => 0]);

        $this->get('/en-in/about')->assertNotFound();
        $this->actingAs($this->admin())->get('/en-in/about?preview=1')->assertOk()->assertSee('About us');

        $page->update(['status' => 'published']);
        $this->get('/en-in/about')->assertOk()->assertSee('About us');
    }

    public function test_same_slug_in_two_countries_has_independent_content_and_hreflang()
    {
        foreach (['en-in' => 'India about', 'en-us' => 'US about'] as $slug => $text) {
            $p = Country::where('slug', $slug)->first()->pages()->create(['title' => 'About', 'slug' => 'about', 'status' => 'published']);
            $p->sections()->create(['type' => 'html', 'content' => ['html' => "<p>$text</p>"], 'sort_order' => 0]);
        }

        $this->get('/en-us/about')->assertOk()->assertSee('US about')->assertDontSee('India about')
            ->assertSee('hreflang="in"', false);
    }

    public function test_fixed_routes_are_not_swallowed_by_country_routes()
    {
        $this->get('/login')->assertOk();
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_events_section_lists_only_that_countrys_events_with_its_currency()
    {
        $user = User::factory()->create();
        $this->makeEvent($user, Country::where('slug', 'en-in')->first(), 'india-session', 'INR', 500, 'India Session');
        $this->makeEvent($user, Country::where('slug', 'en-us')->first(), 'us-session', 'USD', 25, 'US Session');

        $this->homeOf('en-us')->sections()->create(['type' => 'events', 'content' => ['heading' => 'Book'], 'sort_order' => 0]);

        $this->get('/en-us')->assertOk()->assertSee('US Session')->assertDontSee('India Session')
            ->assertSee('$25.00', false)->assertSee('/en-us/e/us-session', false);
    }

    public function test_legacy_event_url_redirects_to_country_url()
    {
        $user = User::factory()->create();
        $this->makeEvent($user, Country::where('slug', 'en-us')->first(), 'legacy', 'USD', 1);

        $this->get('/e/legacy')->assertStatus(301)->assertRedirect(url('/en-us/e/legacy'));
        // Wrong country in the URL is corrected too
        $this->get('/en-in/e/legacy')->assertStatus(301)->assertRedirect(url('/en-us/e/legacy'));
    }

    public function test_country_event_page_renders_with_country_layout_and_timezone_default()
    {
        $user = User::factory()->create();
        $us = Country::where('slug', 'en-us')->first();
        $us->update(['header_html' => '<div id="custom-us-header">US HEADER</div>']);
        $this->makeEvent($user, $us, 'us-call', 'USD', 25, 'US Call')->update([
            'custom_timeslots' => [['start' => '20:00', 'end' => '20:30']],
            'available_week_days' => ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'],
        ]);

        $this->get('/en-us/e/us-call')->assertOk()
            ->assertSee('US HEADER', false)
            ->assertSee('America\/New_York', false);
    }

    public function test_event_of_another_country_is_blocked_for_a_detected_visitor()
    {
        $user = User::factory()->create();
        $this->makeEvent($user, Country::where('slug', 'en-in')->first(), 'india-only', 'INR', 500, 'India Only');

        // US visitor (Cloudflare header) on the Indian event: page, details and booking are all blocked
        $this->withHeader('CF-IPCountry', 'US')->get('/en-in/e/india-only')
            ->assertForbidden()->assertSee('not available in your country')->assertSee('/en-us', false);
        $this->withHeader('CF-IPCountry', 'US')->get('/e/india-only/details')->assertForbidden();
        $this->withHeader('CF-IPCountry', 'US')->post('/e/india-only/book', [])->assertForbidden();

        // Indian visitor, unknown visitor, and a visitor from a country with no site are allowed
        $this->withHeader('CF-IPCountry', 'IN')->get('/en-in/e/india-only')->assertOk();
        $this->get('/en-in/e/india-only')->assertOk();
        $this->withHeader('CF-IPCountry', 'DE')->get('/en-in/e/india-only')->assertOk();

        // Admins are never blocked
        $this->actingAs($this->admin())->withHeader('CF-IPCountry', 'US')->get('/en-in/e/india-only')->assertOk();
    }

    public function test_visitor_country_is_looked_up_by_ip_and_cached()
    {
        \Illuminate\Support\Facades\Http::fake(['free.freeipapi.com/*' => \Illuminate\Support\Facades\Http::response(['countryCode' => 'US'])]);
        $user = User::factory()->create();
        $this->makeEvent($user, Country::where('slug', 'en-in')->first(), 'india-only', 'INR', 500, 'India Only');

        for ($i = 0; $i < 3; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])->get('/en-in/e/india-only')->assertForbidden();
        }
        \Illuminate\Support\Facades\Http::assertSentCount(1); // cached after the first lookup
    }

    public function test_ip_lookup_fails_open_and_skips_private_ips()
    {
        $user = User::factory()->create();
        $this->makeEvent($user, Country::where('slug', 'en-in')->first(), 'india-only', 'INR', 500, 'India Only');

        // API error -> visitor allowed, failure remembered (no retry storm)
        \Illuminate\Support\Facades\Http::fake(['free.freeipapi.com/*' => \Illuminate\Support\Facades\Http::response([], 429)]);
        for ($i = 0; $i < 2; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '9.9.9.9'])->get('/en-in/e/india-only')->assertOk();
        }
        \Illuminate\Support\Facades\Http::assertSentCount(1);

        // Local/private address -> never looked up
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.5'])->get('/en-in/e/india-only')->assertOk();
        \Illuminate\Support\Facades\Http::assertSentCount(1);
    }

    public function test_admin_can_create_country_and_page_and_reorder_sections()
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/countries', [
            'name' => 'United Kingdom', 'slug' => 'en-gb', 'iso_code' => 'gb', 'currency' => 'GBP',
            'default_timezone' => 'Europe/London', 'is_active' => 1,
        ])->assertRedirect();
        $gb = Country::where('slug', 'en-gb')->first();
        $this->assertSame('GB', $gb->iso_code);
        $this->assertFalse($gb->is_default);

        $this->actingAs($admin)->post('/admin/countries', [
            'name' => 'Bad', 'slug' => 'admin', 'currency' => 'GBP', 'default_timezone' => 'UTC',
        ])->assertSessionHasErrors('slug');

        $this->actingAs($admin)->post('/admin/pages', [
            'country_id' => $gb->id, 'title' => 'Home', 'slug' => 'home', 'status' => 'published', 'is_home' => 1,
        ])->assertRedirect();
        $page = $gb->pages()->first();

        foreach (['html', 'faq'] as $type) {
            $this->actingAs($admin)->post("/admin/pages/{$page->id}/sections", ['type' => $type])->assertRedirect();
        }
        [$a, $b] = $page->sections()->get()->all();

        $this->actingAs($admin)->postJson("/admin/pages/{$page->id}/sections/reorder", ['order' => [$b->id, $a->id]])->assertOk();
        $this->assertSame([$b->id, $a->id], $page->sections()->pluck('id')->all());

        $this->actingAs($admin)->put("/admin/sections/{$b->id}", [
            'content' => ['heading' => 'FAQ', 'items' => [['question' => 'Q1', 'answer' => 'A1'], ['question' => '', 'answer' => '']]],
            'is_visible' => 1,
        ])->assertRedirect();
        $this->assertSame([['question' => 'Q1', 'answer' => 'A1']], $b->fresh()->content['items']);

        $this->get('/en-gb')->assertOk()->assertSee('Q1');
    }

    public function test_admin_screens_render()
    {
        $admin = $this->admin();
        $india = Country::where('slug', 'en-in')->first();
        $page = $this->homeOf('en-in');
        foreach (['html', 'hero', 'events', 'faq', 'testimonials', 'image', 'text', 'cta'] as $type) {
            $page->sections()->create(['type' => $type, 'content' => [], 'sort_order' => 0]);
        }

        foreach ([
            '/admin/countries', '/admin/countries/create', "/admin/countries/{$india->id}/edit",
            '/admin/pages', '/admin/pages/create', "/admin/pages/{$page->id}/edit",
            '/admin/events', '/admin/events/create',
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
        $this->actingAs($admin)->get('/admin/homepage-settings')->assertRedirect('/admin/pages');
        // and every section type renders publicly even when empty
        $this->get('/en-in')->assertOk();
    }

    public function test_only_one_home_page_per_country()
    {
        $india = Country::where('slug', 'en-in')->first();
        $this->actingAs($this->admin())->post('/admin/pages', [
            'country_id' => $india->id, 'title' => 'New home', 'slug' => 'new-home', 'status' => 'published', 'is_home' => 1,
        ]);

        $this->assertSame(1, $india->pages()->where('is_home', true)->count());
        $this->assertSame('new-home', $india->pages()->where('is_home', true)->first()->slug);
    }

    public function test_nav_items_render_in_country_header_and_page_duplicate_copies_sections()
    {
        $admin = $this->admin();
        $india = Country::where('slug', 'en-in')->first();
        $page = $india->pages()->create(['title' => 'About', 'slug' => 'about', 'status' => 'published']);
        $page->sections()->create(['type' => 'html', 'content' => ['html' => '<p>x</p>'], 'sort_order' => 0]);

        $this->actingAs($admin)->put("/admin/countries/{$india->id}/navigation", [
            'header' => [['label' => 'About us', 'type' => 'page', 'page_id' => $page->id], ['label' => 'Blog', 'type' => 'url', 'url' => 'https://example.com/blog']],
        ])->assertRedirect();

        $this->homeOf('en-in')->sections()->create(['type' => 'html', 'content' => ['html' => '<p>hi</p>'], 'sort_order' => 0]);
        $this->get('/en-in')->assertSee('About us')->assertSee('/en-in/about', false)->assertSee('https://example.com/blog', false);

        $us = Country::where('slug', 'en-us')->first();
        $this->actingAs($admin)->post("/admin/pages/{$page->id}/duplicate", ['country_id' => $us->id])->assertRedirect();
        $copy = $us->pages()->where('slug', 'about')->first();
        $this->assertSame('draft', $copy->status);
        $this->assertSame(1, $copy->sections()->count());
    }

    public function test_event_requires_country()
    {
        $this->actingAs($this->admin())->post('/admin/events', ['title' => 'x'])->assertSessionHasErrors('country_id');
    }
}
