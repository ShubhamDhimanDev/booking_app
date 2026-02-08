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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('name'); // Starter, Growth, Business, Enterprise
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->json('features_list')->nullable(); // bullet points for marketing

            // Pricing
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2);
            $table->integer('discount_yearly_percent')->default(0); // e.g., 20% off yearly

            // Payment Gateway Plan IDs (JSON: {"razorpay": {"monthly": "plan_xxx", "yearly": "plan_yyy"}, "stripe": {...}})
            $table->json('gateway_plan_ids')->nullable(); // Supports multiple payment gateways

            // Usage Limits
            $table->integer('max_events')->default(10);
            $table->integer('max_bookings_per_month')->default(100);
            $table->integer('max_team_members')->default(5);
            $table->integer('max_promo_codes')->default(10);

            // Feature Flags (boolean flags)
            $table->boolean('custom_domain')->default(false);
            $table->boolean('white_label')->default(false);
            $table->boolean('api_access')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('advanced_analytics')->default(false);
            $table->boolean('google_calendar')->default(true);
            $table->boolean('email_reminders')->default(true);
            $table->boolean('remove_branding')->default(false);

            // Meta
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // additional config

            $table->timestamps();

            // Indexes
            $table->index(['is_active', 'sort_order']);
            $table->index('slug');
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
        Schema::dropIfExists('subscription_plans');
        Schema::enableForeignKeyConstraints();
    }
};
