<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('restrict');

            // Billing Cycle & Status
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            $table->enum('status', ['trialing', 'active', 'past_due', 'cancelled', 'expired'])->default('trialing');

            // Payment Gateway Details (Gateway-agnostic)
            $table->string('gateway')->default('razorpay'); // razorpay, stripe, paypal, etc.
            $table->string('gateway_subscription_id')->unique()->nullable();
            $table->string('gateway_customer_id')->nullable();
            $table->string('gateway_plan_id')->nullable();
            $table->json('gateway_metadata')->nullable(); // Gateway-specific data

            // Billing Periods
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('ends_at')->nullable(); // grace period end

            // Pricing Snapshot (lock in pricing)
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');

            // Usage Tracking (reset monthly)
            $table->integer('events_used')->default(0);
            $table->integer('bookings_used')->default(0);
            $table->integer('team_members_used')->default(0);
            $table->timestamp('usage_reset_at')->nullable();

            // Cancellation
            $table->text('cancellation_reason')->nullable();
            $table->foreignId('cancelled_by_user_id')->nullable()->constrained('users')->onDelete('set null');

            $table->timestamps();

            // Indexes
            $table->index(['organization_id', 'status']);
            $table->index(['gateway', 'gateway_subscription_id']);
            $table->index(['status', 'current_period_end']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('subscriptions');
        Schema::enableForeignKeyConstraints();
    }
};
