<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, boolean, json, integer
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('key');
        });

        // Insert default settings
        DB::table('app_settings')->insert([
            [
                'key' => 'default_payment_gateway',
                'value' => 'razorpay',
                'type' => 'string',
                'description' => 'Default payment gateway for subscriptions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'razorpay_enabled',
                'value' => 'true',
                'type' => 'boolean',
                'description' => 'Enable Razorpay payment gateway',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'stripe_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable Stripe payment gateway',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key' => 'paypal_enabled',
                'value' => 'false',
                'type' => 'boolean',
                'description' => 'Enable PayPal payment gateway',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_settings');
    }
};
