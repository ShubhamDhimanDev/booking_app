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
        Schema::table('bookings', function (Blueprint $table) {
            $table->timestamp('cancelled_at')->nullable()->after('status');
            $table->unsignedBigInteger('cancelled_by')->nullable()->after('cancelled_at');
            $table->text('cancellation_reason')->nullable()->after('cancelled_by');
            $table->enum('refund_status', ['not_applicable', 'pending', 'processing', 'completed', 'failed'])->default('not_applicable')->after('cancellation_reason');
            $table->decimal('refund_amount', 10, 2)->default(0)->after('refund_status');

            $table->foreign('cancelled_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn(['cancelled_at', 'cancelled_by', 'cancellation_reason', 'refund_status', 'refund_amount']);
        });
    }
};
