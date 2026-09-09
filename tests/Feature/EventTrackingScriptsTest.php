<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies every page in the booking/payment flow renders the EVENT's own
 * Pixel/GA script (not the site-wide default) once the event has its own
 * meta_pixel_id/google_analytics_id configured -- and that this works even
 * when the global "Enable Meta Pixel"/"Enable Google Analytics" toggles are
 * off, since a dedicated per-event ID is itself the enable signal.
 */
class EventTrackingScriptsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Global toggles deliberately OFF -- only the event's own IDs should fire.
        Setting::setSetting('meta_pixel_enabled', '0');
        Setting::setSetting('meta_pixel_id', 'GLOBAL_PIXEL_SHOULD_NOT_APPEAR');
        Setting::setSetting('google_analytics_enabled', '0');
        Setting::setSetting('google_analytics_id', 'GLOBAL_GA_SHOULD_NOT_APPEAR');
    }

    protected function eventWithTracking(): Event
    {
        return Event::factory()->create([
            'meta_pixel_id' => 'EVENT_PIXEL_123',
            'google_analytics_id' => 'EVENT_GA_456',
        ]);
    }

    public function test_event_public_page_renders_the_events_own_tracking_scripts()
    {
        $event = $this->eventWithTracking();

        $response = $this->get(route('events.show.public', $event));

        $response->assertOk();
        $response->assertSee('EVENT_PIXEL_123', false);
        $response->assertSee('EVENT_GA_456', false);
        $response->assertDontSee('GLOBAL_PIXEL_SHOULD_NOT_APPEAR', false);
        $response->assertDontSee('GLOBAL_GA_SHOULD_NOT_APPEAR', false);
    }

    public function test_booking_details_page_renders_the_events_own_tracking_scripts()
    {
        $event = $this->eventWithTracking();
        $date = now()->addDay()->format('Y-m-d');

        $response = $this->get(route('bookings.details', $event->slug) . "?date={$date}&time=10:00");

        $response->assertOk();
        $response->assertSee('EVENT_PIXEL_123', false);
        $response->assertSee('EVENT_GA_456', false);
        $response->assertDontSee('GLOBAL_PIXEL_SHOULD_NOT_APPEAR', false);
        $response->assertDontSee('GLOBAL_GA_SHOULD_NOT_APPEAR', false);
        // InitiateCheckout is the specific conversion event fired on this page.
        $response->assertSee('InitiateCheckout', false);
    }

    public function test_payment_page_renders_the_events_own_tracking_scripts()
    {
        $event = $this->eventWithTracking();
        $booking = Booking::factory()->create(['event_id' => $event->id]);

        $response = $this->get(route('payment.page', $booking->id));

        $response->assertOk();
        $response->assertSee('EVENT_PIXEL_123', false);
        $response->assertSee('EVENT_GA_456', false);
        $response->assertDontSee('GLOBAL_PIXEL_SHOULD_NOT_APPEAR', false);
        $response->assertDontSee('GLOBAL_GA_SHOULD_NOT_APPEAR', false);
        $response->assertSee('ViewPaymentPage', false);
    }

    public function test_thankyou_page_renders_the_events_own_tracking_scripts()
    {
        $event = $this->eventWithTracking();
        $booking = Booking::factory()->create(['event_id' => $event->id]);
        Payment::create([
            'user_id' => $booking->user_id,
            'booking_id' => $booking->id,
            'provider' => 'payu',
            'transaction_id' => 'txn_test_123',
            'status' => 'success',
            'amount' => $event->price ?: 500,
            'currency' => $event->currency ?? 'INR',
        ]);

        $response = $this->get(route('payment.thankyou', $booking->id));

        $response->assertOk();
        $response->assertSee('EVENT_PIXEL_123', false);
        $response->assertSee('EVENT_GA_456', false);
        $response->assertDontSee('GLOBAL_PIXEL_SHOULD_NOT_APPEAR', false);
        $response->assertDontSee('GLOBAL_GA_SHOULD_NOT_APPEAR', false);
        $response->assertSee('Purchase', false);
    }
}
