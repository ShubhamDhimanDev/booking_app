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
        Schema::create('booking_reminder_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            // A string key identifying which reminder was sent for this booking.
            // Examples: "event_reminder:123" or "legacy_default:1h-2h" or "offset:120"
            $table->string('reminder_key')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();

            $table->unique(['booking_id', 'reminder_key']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('booking_reminder_logs');
    }
};
