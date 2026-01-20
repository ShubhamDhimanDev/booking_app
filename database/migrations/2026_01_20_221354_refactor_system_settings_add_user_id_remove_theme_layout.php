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
        Schema::table('system_settings', function (Blueprint $table) {
            // Add user_id for per-user theme settings
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            // Remove theme_layout as it's not being used
            $table->dropColumn('theme_layout');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->string('theme_layout')->default('modern');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
