<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('image')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('sku')->nullable()->unique();
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->json('images')->nullable();
            $table->unsignedInteger('stock_qty')->default(0);
            $table->boolean('track_stock')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('grants_free_session')->default(false);
            $table->foreignId('free_session_event_id')->nullable()->constrained('events')->nullOnDelete();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();
        });

        // One row per product and country. No row = not sold in that country.
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('currency', 3);
            $table->decimal('mrp', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->timestamps();

            $table->unique(['product_id', 'country_id']);
        });

        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('session_token', 64)->nullable()->index();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->string('promo_code', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['cart_id', 'product_id']);
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->nullOnDelete();
            $table->string('currency', 3);
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('promo_code', 50)->nullable();
            $table->string('status', 24)->default('pending_payment');
            $table->string('payment_status', 16)->default('pending');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 32)->nullable();
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code', 20);
            $table->string('address_country')->nullable();
            $table->text('notes')->nullable();
            $table->string('carrier')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->decimal('mrp', 10, 2);
            $table->decimal('price', 10, 2);
            $table->unsignedInteger('quantity');
            $table->decimal('line_total', 10, 2);
            $table->timestamps();
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->boolean('ecommerce_enabled')->default(false);
            $table->foreignId('free_session_event_id')->nullable()->constrained('events')->nullOnDelete();
        });

        // Payments and refunds can belong to an order instead of a booking.
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('booking_id')->constrained()->nullOnDelete();
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('booking_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('booking_id')->nullable()->change();
        });

        // Free-session invites granted by a purchase have no original booking.
        Schema::table('follow_up_invites', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->after('booking_id')->constrained()->cascadeOnDelete();
            $table->string('source', 16)->default('booking')->after('order_id'); // booking | order
            $table->string('email')->nullable()->after('user_id'); // guest purchasers
            $table->unsignedBigInteger('booking_id')->nullable()->change();
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('follow_up_invites', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn(['order_id', 'source', 'email']);
        });
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropColumn('order_id');
        });
        Schema::table('countries', function (Blueprint $table) {
            $table->dropForeign(['free_session_event_id']);
            $table->dropColumn(['ecommerce_enabled', 'free_session_event_id']);
        });
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
    }
};
