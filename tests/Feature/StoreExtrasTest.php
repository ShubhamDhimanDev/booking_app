<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Country;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreExtrasTest extends TestCase
{
    use RefreshDatabase;

    protected Country $in;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        $this->in = Country::where('slug', 'en-in')->first();
        $this->in->update(['ecommerce_enabled' => true]);

        $this->product = Product::create(['name' => 'Rudraksha', 'slug' => 'rudraksha', 'sku' => 'RUD-1', 'stock_qty' => 9, 'track_stock' => true, 'is_active' => true, 'is_featured' => true]);
        $this->product->prices()->create(['country_id' => $this->in->id, 'currency' => 'INR', 'mrp' => 1000, 'sale_price' => 800]);
    }

    protected function paidOrder(?User $user, string $email = 'asha@example.com'): Order
    {
        $this->post('/en-in/cart/add', ['product_id' => $this->product->id]);
        $service = app(OrderService::class);
        $order = $service->createFromCart(Cart::latest('id')->first(), [
            'customer_name' => 'Asha Rao', 'customer_email' => $email, 'customer_phone' => '9999999999',
            'address_line1' => '1 MG Road', 'city' => 'Pune', 'postal_code' => '411001',
        ], $user?->id);
        $service->markPaid($order, 'razorpay', 'pay_' . $order->id);

        return $order->fresh();
    }

    public function test_customers_see_only_their_own_orders()
    {
        $alice = User::factory()->create();
        $bob = User::factory()->create();
        $mine = $this->paidOrder($alice);
        $theirs = $this->paidOrder($bob);

        $this->actingAs($alice)->get('/user/orders')->assertOk()->assertSee($mine->order_number)->assertDontSee($theirs->order_number);
        $this->actingAs($alice)->get('/user/orders/' . $mine->id)->assertOk()->assertSee('Rudraksha');
        $this->actingAs($alice)->get('/user/orders/' . $theirs->id)->assertNotFound();
        $this->app['auth']->guard()->logout();
        $this->get('/user/orders')->assertRedirect('/login');
    }

    public function test_guest_orders_show_up_for_a_user_with_the_same_verified_email()
    {
        $guestOrder = $this->paidOrder(null, 'maya@example.com');
        $verified = User::factory()->create(['email' => 'maya@example.com']);
        $unverified = User::factory()->unverified()->create(['email' => 'other@example.com']);
        $this->paidOrder(null, 'other@example.com');

        $this->actingAs($verified)->get('/user/orders')->assertSee($guestOrder->order_number);
        $this->actingAs($unverified)->get('/user/orders')->assertDontSee('ORD-');
    }

    public function test_unfinished_order_payments_are_hidden_from_transactions_and_dashboard_totals()
    {
        $user = User::factory()->create();
        $this->post('/en-in/cart/add', ['product_id' => $this->product->id]);
        $order = app(OrderService::class)->createFromCart(Cart::first(), [
            'customer_name' => 'A', 'customer_email' => 'a@example.com', 'customer_phone' => '1',
            'address_line1' => 'x', 'city' => 'y', 'postal_code' => '1',
        ], $user->id);
        $order->payments()->create(['user_id' => $user->id, 'provider' => 'razorpay', 'status' => 'pending', 'amount' => 800, 'currency' => 'INR']);

        $this->actingAs($user)->get('/user/transactions')->assertOk()->assertDontSee('800 INR');
    }

    public function test_purchase_event_fires_once_per_session_when_a_pixel_is_configured()
    {
        Setting::setSetting('meta_pixel_enabled', true);
        Setting::setSetting('meta_pixel_id', '123456');
        $order = $this->paidOrder(null);
        $url = URL::signedRoute('order.thankyou', ['en-in', $order->order_number]);

        $this->get($url)->assertOk()->assertSee("fbq('track', 'Purchase'", false)->assertSee('RUD-1');
        $this->get($url)->assertOk()->assertDontSee("fbq('track', 'Purchase'", false);
    }

    public function test_view_content_and_checkout_events_fire()
    {
        Setting::setSetting('meta_pixel_enabled', true);
        Setting::setSetting('meta_pixel_id', '123456');

        $this->get('/en-in/store/p/rudraksha')->assertSee("fbq('track', 'ViewContent'", false);

        $this->post('/en-in/cart/add', ['product_id' => $this->product->id])->assertSessionHas('track_add');
        $this->followingRedirects()->post('/en-in/cart/add', ['product_id' => $this->product->id])->assertSee("fbq('track', 'AddToCart'", false);
        $this->get('/en-in/checkout')->assertSee("fbq('track', 'InitiateCheckout'", false);
    }

    public function test_dashboard_shows_store_cards_once_there_are_orders()
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->get('/admin')->assertOk()->assertDontSee('Store orders (This month)');

        $this->paidOrder(null);
        $this->actingAs($admin)->get('/admin')->assertOk()->assertSee('Store orders (This month)')->assertSee('800.00');
    }

    public function test_page_builder_sections_render_products_and_categories()
    {
        $category = ProductCategory::create(['name' => 'Beads', 'slug' => 'beads', 'is_active' => true]);
        $this->product->update(['category_id' => $category->id]);
        $home = $this->in->pages()->where('is_home', true)->first();
        $home->sections()->create(['type' => 'products', 'content' => ['heading' => 'Shop now', 'product_ids' => [], 'limit' => '4'], 'sort_order' => 0]);
        $home->sections()->create(['type' => 'product_categories', 'content' => ['heading' => 'By category'], 'sort_order' => 1]);

        $this->get('/en-in')->assertOk()
            ->assertSee('Shop now')->assertSee('Rudraksha')->assertSee('800.00')
            ->assertSee('By category')->assertSee('Beads');

        $this->in->update(['ecommerce_enabled' => false]);
        $this->get('/en-in')->assertOk()->assertDontSee('Shop now');
    }

    public function test_admin_can_save_a_products_section_with_picked_products()
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $home = $this->in->pages()->where('is_home', true)->first();
        $section = $home->sections()->create(['type' => 'products', 'content' => [], 'sort_order' => 0]);

        $this->actingAs($admin)->put('/admin/sections/' . $section->id, [
            'content' => ['heading' => 'Picks', 'product_ids' => [$this->product->id], 'limit' => '3'], 'is_visible' => 1,
        ])->assertRedirect();

        $this->assertSame([$this->product->id], $section->fresh()->content['product_ids']);
        $this->actingAs($admin)->get('/admin/pages/' . $home->id . '/edit')->assertOk()->assertSee('Rudraksha');
    }
}
