<?php

namespace Tests\Feature;

use App\Models\Event;
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
        $response->assertViewIs('home.show');
        $response->assertCookie('region', 'in');
    }

    public function test_en_us_sets_the_region_cookie_to_us()
    {
        $response = $this->get('/en-us');

        $response->assertOk();
        $response->assertViewIs('home.show');
        $response->assertCookie('region', 'us');
    }

    public function test_en_in_shows_cta_when_an_inr_event_exists()
    {
        $event = Event::factory()->create(['currency' => 'INR']);

        $response = $this->get('/en-in');

        $response->assertOk();
        $response->assertViewHas('event', function ($viewEvent) use ($event) {
            return $viewEvent->id === $event->id;
        });
        $response->assertSee(route('events.show.public', $event), false);
    }

    public function test_en_us_shows_coming_soon_when_no_usd_event_exists()
    {
        // Ensure no USD event exists (an INR one alone must not satisfy the lookup).
        Event::factory()->create(['currency' => 'INR']);

        $response = $this->get('/en-us');

        $response->assertOk();
        $response->assertViewHas('event', null);
        $response->assertSee('Coming Soon');
    }

    public function test_en_us_shows_cta_once_a_usd_event_exists()
    {
        $event = Event::factory()->create(['currency' => 'USD']);

        $response = $this->get('/en-us');

        $response->assertOk();
        $response->assertViewHas('event', function ($viewEvent) use ($event) {
            return $viewEvent->id === $event->id;
        });
        $response->assertSee(route('events.show.public', $event), false);
    }
}
