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
        Schema::create('usage_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');

            // Period & Metric
            $table->date('period_date'); // YYYY-MM-DD (daily/monthly aggregation)
            $table->string('metric', 50); // events_count, bookings_count, team_members_count, api_calls
            $table->integer('value')->default(0);

            // Additional Context
            $table->json('metadata')->nullable(); // breakdown details, hourly data, etc.

            $table->timestamps();

            // Indexes
            $table->unique(['organization_id', 'period_date', 'metric']);
            $table->index(['organization_id', 'period_date']);
            $table->index(['metric', 'period_date']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('usage_records');
    }
};
