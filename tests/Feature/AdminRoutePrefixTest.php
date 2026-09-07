<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Spot-checks that admin routes moved from an empty prefix to `/admin`
 * (routes/admin.php) without changing route names.
 */
class AdminRoutePrefixTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_route_resolves_under_admin_prefix()
    {
        $this->assertSame(url('/admin'), route('admin.dashboard'));
    }

    public function test_guest_hitting_admin_dashboard_is_redirected_to_login()
    {
        $this->get('/admin')->assertRedirect(route('login'));
    }

    public function test_admin_role_user_can_reach_admin_prefix_route_past_isadmin_middleware()
    {
        Role::firstOrCreate(['name' => 'admin']);
        // UserFactory seeds google_auth_metadata by default, so this also clears
        // LinkedWithGoogleMiddleware and reaches the dashboard itself.
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_old_bare_admin_paths_no_longer_resolve()
    {
        $this->get('/bookings')->assertNotFound();
        $this->get('/events')->assertNotFound();
    }
}
