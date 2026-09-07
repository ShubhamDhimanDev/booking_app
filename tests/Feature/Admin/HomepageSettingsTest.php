<?php

namespace Tests\Feature\Admin;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class HomepageSettingsTest extends TestCase
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

    public function test_guest_cannot_access_homepage_settings()
    {
        $this->get('/admin/homepage-settings')->assertRedirect(route('login'));
    }

    public function test_index_defaults_to_the_india_region()
    {
        $response = $this->actingAs($this->admin)->get('/admin/homepage-settings');

        $response->assertOk();
        $response->assertViewHas('region', 'in');
    }

    public function test_index_respects_the_region_query_param()
    {
        $response = $this->actingAs($this->admin)->get('/admin/homepage-settings?region=us');

        $response->assertOk();
        $response->assertViewHas('region', 'us');
    }

    public function test_updating_india_content_does_not_touch_us_content()
    {
        Setting::setSetting('homepage_html_us', '<p>Existing US content</p>');

        $this->actingAs($this->admin)->put('/admin/homepage-settings', [
            'region' => 'in',
            'html' => '<h1>New India content</h1>',
        ])->assertRedirect(route('admin.homepage-settings.index', ['region' => 'in']));

        $this->assertSame('<h1>New India content</h1>', Setting::getSetting('homepage_html_in'));
        $this->assertSame('<p>Existing US content</p>', Setting::getSetting('homepage_html_us'));
    }

    public function test_saved_html_is_rendered_unescaped_on_the_public_page()
    {
        $this->actingAs($this->admin)->put('/admin/homepage-settings', [
            'region' => 'us',
            'html' => '<div class="hero"><h1>Raw &amp; open</h1></div>',
        ]);

        $response = $this->get('/en-us');

        $response->assertOk();
        // Raw markup must appear verbatim, not HTML-escaped (no &lt;div&gt; etc.).
        $response->assertSee('<div class="hero"><h1>Raw &amp; open</h1></div>', false);
    }

    public function test_rejects_an_invalid_region()
    {
        $this->actingAs($this->admin)->put('/admin/homepage-settings', [
            'region' => 'uk',
            'html' => '<h1>Nope</h1>',
        ])->assertSessionHasErrors('region');
    }
}
