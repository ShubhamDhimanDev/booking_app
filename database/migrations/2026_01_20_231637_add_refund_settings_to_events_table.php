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
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('refund_enabled')->default(false)->after('price');
            $table->enum('refund_policy_type', ['flexible', 'moderate', 'strict', 'custom'])->default('moderate')->after('refund_enabled');
            $table->integer('min_cancellation_hours')->default(24)->after('refund_policy_type')->comment('Minimum hours before event to cancel');
            $table->json('refund_rules')->nullable()->after('min_cancellation_hours')->comment('Custom refund rules: [{hours: 48, percentage: 100}]');
            $table->boolean('deduct_gateway_charges')->default(true)->after('refund_rules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['refund_enabled', 'refund_policy_type', 'min_cancellation_hours', 'refund_rules', 'deduct_gateway_charges']);
        });
    }
};
