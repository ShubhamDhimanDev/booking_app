<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Country;
use App\Models\Event;
use App\Models\Order;
use App\Models\Product;
use App\Models\PromoCode;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreTest extends TestCase
{
    use RefreshDatabase;

    protected Country $in;
    protected Country $us;

    protected function setUp(): void
    {
        parent::setUp();
        $this->in = Country::where('slug', 'en-in')->first();
        $this->us = Country::where('slug', 'en-us')->first();
        $this->in->update(['ecommerce_enabled' => true]);
    }

    protected function product(array $attrs = [], array $prices = null): Product
    {
        $product = Product::create($attrs + ['name' => 'Rudraksha', 'slug' => 'rudraksha', 'stock_qty' => 5, 'track_stock' => true, 'is_active' => true]);
        foreach ($prices ?? [[$this->in, 1000, 800]] as [$country, $mrp, $sale]) {
            $product->prices()->create(['country_id' => $country->id, 'currency' => $country->currency, 'mrp' => $mrp, 'sale_price' => $sale]);
        }

        return $product;
    }

    protected function customer(): array
    {
        return [
            'customer_name' => 'Asha Rao', 'customer_email' => 'asha@example.com', 'customer_phone' => '9999999999',
            'address_line1' => '1 MG Road', 'city' => 'Pune', 'state' => 'MH', 'postal_code' => '411001',
        ];
    }

    public function test_store_is_hidden_unless_country_has_ecommerce_enabled()
    {
        $this->product();
        $this->get('/en-in/store')->assertOk()->assertSee('Rudraksha');
        $this->get('/en-us/store')->assertNotFound();
    }

    public function test_product_is_only_listed_in_countries_it_is_priced_for()
    {
        $this->us->update(['ecommerce_enabled' => true]);
        $product = $this->product();

        $this->get('/en-in/store/p/' . $product->slug)->assertOk()->assertSee('20% off');
        $this->get('/en-us/store/p/' . $product->slug)->assertNotFound();
    }

    public function test_cart_totals_use_server_side_prices_and_promo()
    {
        $product = $this->product();
        PromoCode::create(['code' => 'TEN', 'discount_type' => 'percentage', 'discount_value' => 10, 'is_active' => true]);

        $this->post('/en-in/cart/add', ['product_id' => $product->id, 'quantity' => 2])->assertRedirect();
        $this->post('/en-in/cart/promo', ['promo_code' => 'ten'])->assertRedirect();

        $summary = app(PricingService::class)->summarize(Cart::first());
        $this->assertSame(1600.0, $summary['subtotal']);
        $this->assertSame(400.0, $summary['savings']);
        $this->assertSame(160.0, $summary['discount']);
        $this->assertSame(1440.0, $summary['total']);
        $this->get('/en-in/cart')->assertOk()->assertSee('1,440.00');
    }

    public function test_quantity_is_capped_by_stock()
    {
        $product = $this->product(['stock_qty' => 2]);

        $this->post('/en-in/cart/add', ['product_id' => $product->id, 'quantity' => 5]);

        $this->assertSame(2, (int) Cart::first()->items()->first()->quantity);
    }

    public function test_guest_cart_is_merged_into_the_users_cart_on_login()
    {
        $product = $this->product();
        $this->post('/en-in/cart/add', ['product_id' => $product->id]);

        $user = User::factory()->create();
        $this->actingAs($user)->get('/en-in/cart')->assertOk();

        $this->assertSame($user->id, Cart::first()->user_id);
        $this->assertSame(1, Cart::count());
    }

    public function test_mark_paid_is_idempotent_decrements_stock_and_sends_free_session_link()
    {
        Notification::fake();
        $owner = User::factory()->create();
        $event = Event::create([
            'user_id' => $owner->id, 'country_id' => $this->in->id, 'title' => 'Free Consult', 'slug' => 'free-consult',
            'price' => 0, 'currency' => 'INR', 'duration' => 30,
            'available_from_date' => now()->toDateString(), 'available_to_date' => now()->addDays(5)->toDateString(),
        ]);
        $product = $this->product(['grants_free_session' => true, 'free_session_event_id' => $event->id]);

        $this->post('/en-in/cart/add', ['product_id' => $product->id, 'quantity' => 2]);
        $cart = Cart::first();
        $orders = app(OrderService::class);
        $order = $orders->createFromCart($cart, $this->customer(), null);

        $this->assertSame('1600.00', $order->total);
        $this->assertSame('pending_payment', $order->status);
        $this->assertSame(800.0, (float) $order->items->first()->price);

        $this->assertTrue($orders->markPaid($order, 'razorpay', 'pay_1'));
        $this->assertFalse($orders->markPaid($order, 'razorpay', 'pay_1'), 'second confirmation must be ignored');

        $this->assertSame(3, $product->fresh()->stock_qty, 'stock is decremented once');
        $this->assertSame(0, Cart::count(), 'cart is cleared');
        $this->assertSame('paid', $order->fresh()->status);

        $invite = $order->freeSessionInvite;
        $this->assertNotNull($invite);
        $this->assertSame($event->id, $invite->event_id);
        $this->assertSame('0.00', $invite->custom_price);
        $this->assertTrue($invite->isValid());

        Notification::assertSentOnDemandTimes(OrderPlacedNotification::class, 1);
    }

    public function test_free_session_link_opens_the_slot_page_for_an_order_invite()
    {
        Notification::fake();
        $owner = User::factory()->create();
        $event = Event::create([
            'user_id' => $owner->id, 'country_id' => $this->in->id, 'title' => 'Free Consult', 'slug' => 'free-consult',
            'price' => 0, 'currency' => 'INR', 'duration' => 30,
            'available_from_date' => now()->toDateString(), 'available_to_date' => now()->addDays(5)->toDateString(),
        ]);
        $product = $this->product(['grants_free_session' => true], [[$this->in, 500, 500]]);
        $this->in->update(['free_session_event_id' => $event->id]); // falls back to the country default

        $this->post('/en-in/cart/add', ['product_id' => $product->id]);
        $order = app(OrderService::class)->createFromCart(Cart::first(), $this->customer(), null);
        app(OrderService::class)->markPaid($order, 'razorpay', 'pay_2');

        $this->get('/followup/' . $order->freeSessionInvite->token)->assertOk()->assertSee('Your Free Session');
    }

    public function test_a_full_discount_order_skips_the_gateway_and_is_paid()
    {
        Notification::fake();
        $product = $this->product();
        PromoCode::create(['code' => 'FREE', 'discount_type' => 'percentage', 'discount_value' => 100, 'is_active' => true]);

        $this->post('/en-in/cart/add', ['product_id' => $product->id]);
        $this->post('/en-in/cart/promo', ['promo_code' => 'FREE']);
        $this->post('/en-in/checkout', $this->customer())->assertRedirect();

        $order = Order::first();
        $this->assertSame('paid', $order->status);
        $this->assertSame('0.00', $order->total);
    }

    public function test_payu_callback_with_forged_status_does_not_pay_the_order()
    {
        $product = $this->product();
        $this->post('/en-in/cart/add', ['product_id' => $product->id]);
        $order = app(OrderService::class)->createFromCart(Cart::first(), $this->customer(), null);
        $order->payments()->create([
            'provider' => 'payu', 'gateway_order_id' => 'TXN1', 'status' => 'pending', 'amount' => $order->total, 'currency' => 'INR',
        ]);

        $this->post('/payment/payu/order-callback', [
            'udf1' => $order->order_number, 'txnid' => 'TXN1', 'status' => 'success', 'amount' => $order->total, 'hash' => 'forged',
        ])->assertRedirect();

        $this->assertFalse($order->fresh()->isPaid());
    }

    public function test_admin_can_create_a_product_with_country_prices()
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Yantra', 'stock_qty' => 3, 'track_stock' => 1, 'is_active' => 1,
            'prices' => [
                $this->in->id => ['mrp' => '1000', 'sale_price' => '750'],
                $this->us->id => ['mrp' => '', 'sale_price' => ''],
            ],
        ])->assertRedirect();

        $product = Product::where('name', 'Yantra')->firstOrFail();
        $this->assertSame('yantra', $product->slug);
        $this->assertSame(1, $product->prices()->count());
        $this->assertSame('750.00', $product->prices()->first()->sale_price);
    }

    public function test_sale_price_above_mrp_is_rejected()
    {
        Role::firstOrCreate(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)->post('/admin/products', [
            'name' => 'Yantra', 'prices' => [$this->in->id => ['mrp' => '100', 'sale_price' => '200']],
        ])->assertSessionHasErrors();

        $this->assertSame(0, Product::count());
    }
}
