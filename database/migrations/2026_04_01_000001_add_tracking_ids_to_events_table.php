<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('meta_pixel_id')->nullable()->after('currency');
            $table->string('google_analytics_id')->nullable()->after('meta_pixel_id');
        });
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['meta_pixel_id', 'google_analytics_id']);
        });
    }
};
