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
        Schema::create('follow_up_invites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->onDelete('cascade');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // booker
            $table->decimal('custom_price', 10, 2)->default(0); // can be 0 for free
            $table->string('token', 64)->unique(); // unique token for booking link
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        // Add follow-up reference to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('followup_invite_id')->nullable()->constrained('follow_up_invites')->onDelete('set null');
            $table->boolean('is_followup')->default(false)->after('user_id');
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
            $table->dropForeign(['followup_invite_id']);
            $table->dropColumn(['followup_invite_id', 'is_followup']);
        });

        Schema::dropIfExists('follow_up_invites');
    }
};
