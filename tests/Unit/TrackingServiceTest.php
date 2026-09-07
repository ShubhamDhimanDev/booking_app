<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Setting;
use App\Services\TrackingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Setting::setSetting('meta_pixel_enabled', '1');
        Setting::setSetting('meta_pixel_id', 'GLOBAL_PIXEL_ID');
        Setting::setSetting('google_analytics_enabled', '1');
        Setting::setSetting('google_analytics_id', 'GLOBAL_GA_ID');
    }

    public function test_get_meta_pixel_id_falls_back_to_global_setting_when_event_has_no_override()
    {
        $event = Event::factory()->create(['meta_pixel_id' => null]);

        $this->assertSame('GLOBAL_PIXEL_ID', TrackingService::getMetaPixelId($event));
    }

    public function test_get_meta_pixel_id_uses_the_events_own_override_when_set()
    {
        $event = Event::factory()->create(['meta_pixel_id' => 'EVENT_SPECIFIC_PIXEL']);

        $this->assertSame('EVENT_SPECIFIC_PIXEL', TrackingService::getMetaPixelId($event));
    }

    public function test_get_meta_pixel_id_uses_global_when_no_event_is_given()
    {
        $this->assertSame('GLOBAL_PIXEL_ID', TrackingService::getMetaPixelId(null));
    }

    public function test_get_google_analytics_id_uses_the_events_own_override_when_set()
    {
        $event = Event::factory()->create(['google_analytics_id' => 'EVENT_SPECIFIC_GA']);

        $this->assertSame('EVENT_SPECIFIC_GA', TrackingService::getGoogleAnalyticsId($event));
    }

    public function test_get_google_analytics_id_falls_back_to_global_setting_when_event_has_no_override()
    {
        $event = Event::factory()->create(['google_analytics_id' => null]);

        $this->assertSame('GLOBAL_GA_ID', TrackingService::getGoogleAnalyticsId($event));
    }

    public function test_base_script_uses_the_events_pixel_id_when_overridden()
    {
        $event = Event::factory()->create(['meta_pixel_id' => 'EVENT_SPECIFIC_PIXEL']);

        $script = TrackingService::getBaseScript($event);

        $this->assertStringContainsString('EVENT_SPECIFIC_PIXEL', $script);
        $this->assertStringNotContainsString('GLOBAL_PIXEL_ID', $script);
    }

    public function test_base_script_uses_global_pixel_id_when_event_has_no_override()
    {
        $event = Event::factory()->create(['meta_pixel_id' => null]);

        $script = TrackingService::getBaseScript($event);

        $this->assertStringContainsString('GLOBAL_PIXEL_ID', $script);
    }
}
