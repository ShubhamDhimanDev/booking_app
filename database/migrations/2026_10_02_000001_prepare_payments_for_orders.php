<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Store prices can be fractional (e.g. 499.50); the column used to be an integer.
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('amount', 10, 2)->default(0)->change();
            $table->string('gateway_order_id')->nullable()->after('transaction_id')->index(); // Razorpay order id / PayU txnid
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('cart_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cart_id']);
            $table->dropColumn('cart_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['gateway_order_id']);
            $table->dropColumn('gateway_order_id');
            $table->integer('amount')->default(0)->change();
        });
    }
};
