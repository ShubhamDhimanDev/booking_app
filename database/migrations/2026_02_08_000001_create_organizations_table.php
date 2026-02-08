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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // for subdomain: {slug}.meetflow.app
            $table->string('domain')->nullable()->unique(); // optional custom domain
            $table->text('description')->nullable();

            // Owner & Contact
            $table->foreignId('owner_id')->constrained('users')->onDelete('restrict');
            $table->string('contact_email');
            $table->string('contact_phone')->nullable();

            // Subscription Status
            $table->enum('status', ['trial', 'active', 'suspended', 'cancelled'])->default('trial');
            $table->unsignedBigInteger('current_plan_id')->nullable(); // Foreign key added later after subscription_plans table exists
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('subscribed_at')->nullable();

            // Preferences
            $table->string('timezone')->default('Asia/Kolkata');
            $table->string('currency')->default('INR');
            $table->string('locale')->default('en');

            // Branding (JSON)
            $table->json('branding')->nullable(); // logo_url, primary_color, secondary_color
            $table->json('settings')->nullable(); // org-specific configurations

            // Billing (Gateway-agnostic)
            $table->string('default_payment_gateway')->default('razorpay'); // razorpay, stripe, paypal
            $table->json('gateway_customer_ids')->nullable(); // {"razorpay": "cust_xxx", "stripe": "cus_yyy"}
            $table->string('billing_email')->nullable();
            $table->text('billing_address')->nullable();
            $table->string('gstin')->nullable(); // GST number for Indian businesses

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'trial_ends_at']);
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
        Schema::dropIfExists('organizations');
        Schema::enableForeignKeyConstraints();
    }
};
