<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Event;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SearchFilterTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    public function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin']);
        // UserFactory seeds google_auth_metadata by default, clearing LinkedWithGoogleMiddleware.
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');
    }

    public function test_bookings_search_matches_booker_name_or_email_and_excludes_others()
    {
        $event = Event::factory()->create(['user_id' => $this->admin->id]);
        // BookingController::index() excludes bookings with a null user_id, so every
        // fixture here needs one (representing the booker's own account).
        $match = Booking::factory()->create(['event_id' => $event->id, 'user_id' => User::factory()->create()->id, 'booker_name' => 'Jane Doe', 'booker_email' => 'jane@example.com']);
        $emailMatch = Booking::factory()->create(['event_id' => $event->id, 'user_id' => User::factory()->create()->id, 'booker_name' => 'Someone Else', 'booker_email' => 'findme@example.com']);
        $noMatch = Booking::factory()->create(['event_id' => $event->id, 'user_id' => User::factory()->create()->id, 'booker_name' => 'Unrelated Person', 'booker_email' => 'nope@example.com']);

        $response = $this->actingAs($this->admin)->get('/admin/bookings?search=jane');
        $response->assertOk();
        $response->assertSee('Jane Doe');
        $response->assertDontSee('Unrelated Person');

        $response = $this->actingAs($this->admin)->get('/admin/bookings?search=findme');
        $response->assertOk();
        $response->assertSee('Someone Else');
        $response->assertDontSee('Jane Doe');
        $response->assertDontSee('Unrelated Person');
    }

    public function test_transactions_search_matches_registered_user_name_email_and_booker_name_email()
    {
        $event = Event::factory()->create(['user_id' => $this->admin->id]);

        $customer = User::factory()->create(['name' => 'Ravi Kumar', 'email' => 'ravi@example.com']);
        $booking1 = Booking::factory()->create(['event_id' => $event->id, 'user_id' => $customer->id, 'booker_name' => 'Ravi Kumar', 'booker_email' => 'ravi@example.com']);
        Payment::create([
            'user_id' => $customer->id,
            'booking_id' => $booking1->id,
            'provider' => 'razorpay',
            'transaction_id' => 'txn_1',
            'status' => 'success',
            'amount' => 500,
            'currency' => 'INR',
        ]);

        $otherUser = User::factory()->create(['name' => 'Someone Random', 'email' => 'random@example.com']);
        $booking2 = Booking::factory()->create(['event_id' => $event->id, 'user_id' => $otherUser->id, 'booker_name' => 'Guest Booker', 'booker_email' => 'guest-booker@example.com']);
        Payment::create([
            'user_id' => $otherUser->id,
            'booking_id' => $booking2->id,
            'provider' => 'payu',
            'transaction_id' => 'txn_2',
            'status' => 'success',
            'amount' => 500,
            'currency' => 'INR',
        ]);

        // Search by the registered account's name.
        $response = $this->actingAs($this->admin)->get('/admin/payments/history?search=Ravi');
        $response->assertOk();
        $response->assertSee('txn_1');
        $response->assertDontSee('txn_2');

        // Search by the guest-entered booker_email (differs from the account owner's email above).
        $response = $this->actingAs($this->admin)->get('/admin/payments/history?search=guest-booker');
        $response->assertOk();
        $response->assertSee('txn_2');
        $response->assertDontSee('txn_1');
    }

    public function test_users_search_matches_name_username_or_email()
    {
        Role::firstOrCreate(['name' => 'owner']);
        $match = User::factory()->create(['name' => 'Amit Sharma', 'username' => 'amitsharma', 'email' => 'amit@example.com']);
        $noMatch = User::factory()->create(['name' => 'Someone Else', 'username' => 'someoneelse', 'email' => 'someone@example.com']);

        $response = $this->actingAs($this->admin)->get('/admin/users?search=Amit');
        $response->assertOk();
        $response->assertSee('Amit Sharma');
        $response->assertDontSee('Someone Else');
    }

    public function test_users_role_filter_matches_only_users_with_that_role()
    {
        Role::firstOrCreate(['name' => 'owner']);
        $owner = User::factory()->create(['name' => 'Owner Person']);
        $owner->assignRole('owner');
        $plainUser = User::factory()->create(['name' => 'Regular Person']);

        $response = $this->actingAs($this->admin)->get('/admin/users?role=owner');
        $response->assertOk();
        $response->assertSee('Owner Person');
        $response->assertDontSee('Regular Person');
    }
}
