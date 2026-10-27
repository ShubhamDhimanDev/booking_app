<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Slot selection and the details form are a single page: the event page itself
 * carries the form that posts to bookings.store.
 */
class BookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function makeEvent(User $owner, string $slug, float $price = 500): Event
    {
        return Event::create([
            'user_id' => $owner->id, 'title' => 'Consult', 'slug' => $slug,
            'price' => $price, 'currency' => 'INR', 'duration' => 30,
            'available_from_date' => now()->toDateString(),
            'available_to_date' => now()->addDays(5)->toDateString(),
            'custom_timeslots' => [['start' => '10:00', 'end' => '10:30']],
        ]);
    }

    public function test_event_page_has_the_details_form_posting_to_the_booking_store()
    {
        $event = $this->makeEvent(User::factory()->create(), 'one-page');

        $this->get(route('events.show.public', $event))
            ->assertOk()
            ->assertSee('action="' . route('bookings.store', $event->slug) . '"', false)
            ->assertSee('name="booked_at_date"', false)
            ->assertSee('name="booked_at_time"', false)
            ->assertSee('name="booker_email"', false)
            ->assertSee('name="phone"', false)
            ->assertSee('Continue to Payment');
    }

    public function test_free_event_button_says_confirm_booking()
    {
        $event = $this->makeEvent(User::factory()->create(), 'free-one', 0);

        $this->get(route('events.show.public', $event))->assertOk()->assertSee('Confirm Booking');
    }

    public function test_the_separate_details_page_is_gone()
    {
        $event = $this->makeEvent(User::factory()->create(), 'no-details');

        $this->get('/e/' . $event->slug . '/details?date=' . now()->addDay()->toDateString() . '&time=10:00')
            ->assertNotFound();
    }

    public function test_booking_requires_a_phone_number()
    {
        $event = $this->makeEvent(User::factory()->create(), 'needs-phone');

        $this->post(route('bookings.store', $event->slug), [
            'booker_name' => 'Asha',
            'booker_email' => 'asha@example.com',
            'booked_at_date' => now()->addDay()->toDateString(),
            'booked_at_time' => '10:00',
        ])->assertSessionHasErrors('phone');
    }

    public function test_slot_conflict_redirects_back_with_a_slot_error_instead_of_json()
    {
        $owner = User::factory()->create();
        $event = $this->makeEvent($owner, 'conflict-a');
        $other = $this->makeEvent($owner, 'conflict-b');
        $date = now()->addDay()->toDateString();

        // The owner is already busy at this time on another event.
        $other->bookings()->create([
            'booker_name' => 'Someone', 'booker_email' => 'someone@example.com', 'phone' => '9999999999',
            'booked_at_date' => $date, 'booked_at_time' => '10:00', 'user_id' => $owner->id, 'status' => 'confirmed',
        ]);

        $this->from(route('events.show.public', $event))
            ->post(route('bookings.store', $event->slug), [
                'booker_name' => 'Asha',
                'booker_email' => 'asha@example.com',
                'phone' => '8888888888',
                'booked_at_date' => $date,
                'booked_at_time' => '10:00',
            ])
            ->assertRedirect(route('events.show.public', $event))
            ->assertSessionHasErrors('slot')
            ->assertSessionHasInput('booker_email', 'asha@example.com');

        $this->assertSame(0, $event->bookings()->count());
    }
}
