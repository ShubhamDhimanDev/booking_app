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
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained()->onDelete('set null');

            // Payment Gateway Details (Gateway-agnostic)
            $table->string('gateway')->default('razorpay'); // razorpay, stripe, paypal, etc.
            $table->string('gateway_payment_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');

            // Status & Response
            $table->enum('status', ['pending', 'authorized', 'captured', 'failed', 'refunded'])->default('pending');
            $table->text('failure_reason')->nullable();
            $table->json('gateway_response')->nullable();

            // Payment Method Used
            $table->string('payment_method')->nullable(); // card, netbanking, upi, wallet
            $table->string('card_last4')->nullable();
            $table->string('card_network')->nullable(); // Visa, Mastercard, etc.

            // Metadata
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['subscription_id', 'status', 'created_at']);
            $table->index(['gateway_payment_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_attempts');
    }
};
