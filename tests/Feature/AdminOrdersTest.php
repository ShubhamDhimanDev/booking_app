<?php

namespace Tests\Feature;

use App\Jobs\ProcessOrderRefundJob;
use App\Models\Cart;
use App\Models\Country;
use App\Models\Event;
use App\Models\Order;
use App\Models\Product;
use App\Models\Refund;
use App\Models\User;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderRefundedNotification;
use App\Notifications\OrderShippedNotification;
use App\Services\OrderService;
use App\Services\RefundGatewayManager;
use App\Services\RefundServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminOrdersTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Country $in;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'admin']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->in = Country::where('slug', 'en-in')->first();
        $this->in->update(['ecommerce_enabled' => true]);

        $this->product = Product::create(['name' => 'Rudraksha', 'slug' => 'rudraksha', 'stock_qty' => 5, 'track_stock' => true, 'is_active' => true]);
        $this->product->prices()->create(['country_id' => $this->in->id, 'currency' => 'INR', 'mrp' => 1000, 'sale_price' => 800]);
    }

    /** A paid order for 2 x Rudraksha (stock 5 -> 3). */
    protected function paidOrder(string $provider = 'razorpay'): Order
    {
        Notification::fake();
        $this->post('/en-in/cart/add', ['product_id' => $this->product->id, 'quantity' => 2]);
        $service = app(OrderService::class);
        $order = $service->createFromCart(Cart::first(), [
            'customer_name' => 'Asha Rao', 'customer_email' => 'asha@example.com', 'customer_phone' => '9999999999',
            'address_line1' => '1 MG Road', 'city' => 'Pune', 'postal_code' => '411001',
        ], null);
        $service->markPaid($order, $provider, 'pay_123');
        Notification::fake(); // forget the checkout emails

        return $order->fresh();
    }

    public function test_orders_pages_render()
    {
        $order = $this->paidOrder();

        $this->actingAs($this->admin)->get('/admin/orders')->assertOk()->assertSee($order->order_number);
        $this->actingAs($this->admin)->get('/admin/orders?status=paid&q=asha')->assertOk()->assertSee($order->order_number);
        $this->actingAs($this->admin)->get('/admin/orders?status=shipped')->assertOk()->assertDontSee($order->order_number);
        $this->actingAs($this->admin)->get('/admin/orders/' . $order->id)->assertOk()->assertSee('Asha Rao');
        $this->actingAs($this->admin)->get('/admin/orders/' . $order->id . '/slip')->assertOk()->assertSee('Packing slip');
        $this->actingAs($this->admin)->get('/admin/orders/export')->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    public function test_non_admins_cannot_see_orders()
    {
        $order = $this->paidOrder();
        $this->actingAs(User::factory()->create())->get('/admin/orders/' . $order->id)->assertRedirect();
        $this->actingAs(User::factory()->create())->post('/admin/orders/' . $order->id . '/refund')->assertRedirect();
        $this->assertSame(0, Refund::count());
    }

    public function test_fulfilment_path_ships_with_tracking_and_emails_the_customer()
    {
        $order = $this->paidOrder();
        Notification::fake();

        $this->actingAs($this->admin)->post("/admin/orders/{$order->id}/status", ['status' => 'processing']);
        $this->actingAs($this->admin)->post("/admin/orders/{$order->id}/status", ['status' => 'shipped', 'carrier' => 'Delhivery', 'tracking_number' => 'DL123']);
        $order->refresh();

        $this->assertSame('shipped', $order->status);
        $this->assertSame('DL123', $order->tracking_number);
        $this->assertNotNull($order->shipped_at);
        Notification::assertSentOnDemandTimes(OrderShippedNotification::class, 1);

        $this->actingAs($this->admin)->post("/admin/orders/{$order->id}/status", ['status' => 'delivered']);
        $this->assertSame('delivered', $order->fresh()->status);
    }

    public function test_invalid_status_jumps_are_rejected()
    {
        $order = $this->paidOrder();

        $this->actingAs($this->admin)->post("/admin/orders/{$order->id}/status", ['status' => 'delivered'])
            ->assertSessionHas('alert_type', 'danger');

        $this->assertSame('paid', $order->fresh()->status);
    }

    public function test_only_unpaid_orders_can_be_cancelled()
    {
        $paid = $this->paidOrder();
        $this->actingAs($this->admin)->post("/admin/orders/{$paid->id}/cancel")->assertSessionHas('alert_type', 'danger');
        $this->assertSame('paid', $paid->fresh()->status);

        $this->post('/en-in/cart/add', ['product_id' => $this->product->id]);
        $pending = app(OrderService::class)->createFromCart(Cart::first(), [
            'customer_name' => 'B', 'customer_email' => 'b@example.com', 'customer_phone' => '1',
            'address_line1' => 'x', 'city' => 'y', 'postal_code' => '1',
        ], null);
        $this->actingAs($this->admin)->post("/admin/orders/{$pending->id}/cancel")->assertSessionHas('alert_type', 'success');
        $this->assertSame('cancelled', $pending->fresh()->status);
    }

    public function test_gateway_refund_creates_a_refund_and_queues_the_job()
    {
        $order = $this->paidOrder('razorpay');
        Queue::fake();

        $this->actingAs($this->admin)->post("/admin/orders/{$order->id}/refund")->assertSessionHas('alert_type', 'success');

        $refund = Refund::where('order_id', $order->id)->firstOrFail();
        $this->assertSame('1600.00', $refund->net_refund_amount);
        $this->assertNull($refund->booking_id);
        Queue::assertPushed(ProcessOrderRefundJob::class);
        $this->assertSame('paid', $order->fresh()->status, 'order closes only after the gateway confirms');

        $this->actingAs($this->admin)->post("/admin/orders/{$order->id}/refund")->assertSessionHas('alert_type', 'danger');
        $this->actingAs($this->admin)->get('/admin/refunds')->assertOk()->assertSee($order->order_number);
    }

    public function test_refund_job_closes_the_order_restocks_and_cancels_the_free_session_link()
    {
        $owner = User::factory()->create();
        $event = Event::create([
            'user_id' => $owner->id, 'country_id' => $this->in->id, 'title' => 'Free Consult', 'slug' => 'free-consult',
            'price' => 0, 'currency' => 'INR', 'duration' => 30,
            'available_from_date' => now()->toDateString(), 'available_to_date' => now()->addDays(5)->toDateString(),
        ]);
        $this->product->update(['grants_free_session' => true, 'free_session_event_id' => $event->id]);
        $order = $this->paidOrder('razorpay');
        $this->assertSame(3, $this->product->fresh()->stock_qty);
        $this->assertTrue($order->freeSessionInvite->isValid());

        $gateway = new class implements RefundServiceInterface {
            public function getName(): string { return 'fake'; }
            public function processRefund(string $paymentId, float $amount, array $options = []): array
            {
                return ['success' => true, 'refund_id' => 'rfnd_1', 'raw_response' => ['paid' => $paymentId, 'amount' => $amount]];
            }
            public function getRefundStatus(string $refundId): array { return []; }
            public function calculateGatewayCharges(float $amount): float { return 0; }
        };
        $manager = $this->createMock(RefundGatewayManager::class);
        $manager->method('getGateway')->willReturn($gateway);
        $this->app->instance(RefundGatewayManager::class, $manager);

        Queue::fake();
        $this->actingAs($this->admin)->post("/admin/orders/{$order->id}/refund");
        $refund = Refund::where('order_id', $order->id)->firstOrFail();
        Notification::fake();

        (new ProcessOrderRefundJob($refund))->handle($manager, app(OrderService::class));

        $order->refresh();
        $this->assertSame('refunded', $order->status);
        $this->assertSame('completed', $refund->fresh()->status);
        $this->assertSame('rfnd_1', $refund->fresh()->gateway_refund_id);
        $this->assertSame(5, $this->product->fresh()->stock_qty, 'unshipped items return to stock');
        $this->assertSame('expired', $order->freeSessionInvite->fresh()->status);
        Notification::assertSentOnDemandTimes(OrderRefundedNotification::class, 1);

        // Running the job again must not refund or restock twice.
        (new ProcessOrderRefundJob($refund))->handle($manager, app(OrderService::class));
        $this->assertSame(5, $this->product->fresh()->stock_qty);
    }

    public function test_refunding_a_shipped_order_does_not_restock()
    {
        $order = $this->paidOrder('free');
        app(OrderService::class)->advance($order, 'shipped', 'DTDC', 'X1');

        $this->actingAs($this->admin)->post("/admin/orders/{$order->id}/refund");

        $this->assertSame('refunded', $order->fresh()->status);
        $this->assertSame(3, $this->product->fresh()->stock_qty);
    }

    public function test_resend_reopens_an_expired_free_session_link()
    {
        $owner = User::factory()->create();
        $event = Event::create([
            'user_id' => $owner->id, 'country_id' => $this->in->id, 'title' => 'Free Consult', 'slug' => 'free-consult',
            'price' => 0, 'currency' => 'INR', 'duration' => 30,
            'available_from_date' => now()->toDateString(), 'available_to_date' => now()->addDays(5)->toDateString(),
        ]);
        $this->product->update(['grants_free_session' => true, 'free_session_event_id' => $event->id]);
        $order = $this->paidOrder();
        $order->freeSessionInvite->update(['expires_at' => now()->subDay()]);

        $this->actingAs($this->admin)->post("/admin/orders/{$order->id}/resend")->assertSessionHas('alert_type', 'success');

        $this->assertTrue($order->freeSessionInvite->fresh()->isValid());
        Notification::assertSentOnDemandTimes(OrderPlacedNotification::class, 1);
    }
}
