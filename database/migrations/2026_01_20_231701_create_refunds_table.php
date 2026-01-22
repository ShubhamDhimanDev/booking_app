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
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('gateway_charges', 10, 2)->default(0);
            $table->decimal('net_refund_amount', 10, 2);
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->string('gateway')->comment('razorpay or payu');
            $table->string('gateway_refund_id')->nullable()->comment('Gateway refund transaction ID');
            $table->enum('initiated_by', ['user', 'organizer', 'admin', 'system'])->default('user');
            $table->unsignedBigInteger('initiated_by_user_id')->nullable();
            $table->text('failure_reason')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('initiated_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['booking_id', 'status']);
            $table->index(['gateway', 'gateway_refund_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refunds');
    }
};
