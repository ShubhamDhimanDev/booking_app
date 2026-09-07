<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_renders_detect_view_when_no_region_cookie_is_set()
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertViewIs('home.detect');
    }

    public function test_root_redirects_to_en_in_when_region_cookie_is_in()
    {
        $response = $this->withCookie('region', 'in')->get('/');

        $response->assertRedirect(route('home.in'));
    }

    public function test_root_redirects_to_en_us_when_region_cookie_is_us()
    {
        $response = $this->withCookie('region', 'us')->get('/');

        $response->assertRedirect(route('home.us'));
    }

    public function test_redetect_query_param_bypasses_the_region_cookie()
    {
        $response = $this->withCookie('region', 'in')->get('/?redetect=1');

        $response->assertOk();
        $response->assertViewIs('home.detect');
    }

    public function test_en_in_sets_the_region_cookie_to_in()
    {
        $response = $this->get('/en-in');

        $response->assertOk();
        $response->assertCookie('region', 'in');
    }

    public function test_en_us_sets_the_region_cookie_to_us()
    {
        $response = $this->get('/en-us');

        $response->assertOk();
        $response->assertCookie('region', 'us');
    }

    public function test_en_in_response_contains_nothing_but_the_stored_html_no_app_layout()
    {
        Setting::setSetting('homepage_html_in', '<h1>Only this</h1>');

        $response = $this->get('/en-in');

        $response->assertOk();
        // No app shell/layout markup should be present -- this is a standalone
        // response, not a view rendered through layouts.app.
        $this->assertSame('<h1>Only this</h1>', $response->getContent());
    }

    public function test_en_in_renders_the_admin_configured_html_for_india()
    {
        Setting::setSetting('homepage_html_in', '<h1>Welcome India visitors</h1>');
        Setting::setSetting('homepage_html_us', '<h1>Welcome US visitors</h1>');

        $response = $this->get('/en-in');

        $response->assertOk();
        $response->assertSee('Welcome India visitors', false);
        $response->assertDontSee('Welcome US visitors', false);
    }

    public function test_en_us_renders_the_admin_configured_html_for_us()
    {
        Setting::setSetting('homepage_html_in', '<h1>Welcome India visitors</h1>');
        Setting::setSetting('homepage_html_us', '<h1>Welcome US visitors</h1>');

        $response = $this->get('/en-us');

        $response->assertOk();
        $response->assertSee('Welcome US visitors', false);
        $response->assertDontSee('Welcome India visitors', false);
    }

    public function test_en_in_shows_a_fallback_message_when_no_html_is_configured_yet()
    {
        $response = $this->get('/en-in');

        $response->assertOk();
        $response->assertSee("hasn't been set up yet", false);
    }
}
