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
        Schema::create('organization_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action', 100); // event.created, booking.cancelled, user.invited, etc.
            $table->string('entity_type', 50)->nullable(); // Event, Booking, User
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->json('metadata')->nullable(); // additional context
            $table->ipAddress('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['organization_id', 'created_at']);
            $table->index(['entity_type', 'entity_id']);
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('organization_activity_logs');
    }
};
