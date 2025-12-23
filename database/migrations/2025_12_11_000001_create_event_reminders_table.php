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
        Schema::create('event_reminders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            // offset in minutes before the booking time when the reminder should be sent
            $table->integer('offset_minutes')->unsigned();
            // optional human friendly name/label
            $table->string('name')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_reminders');
    }
};
