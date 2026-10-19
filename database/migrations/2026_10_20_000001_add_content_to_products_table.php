<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Structured landing-page copy for a product (see Product::sanitizeContent). Store-only; bookings are untouched. */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('content')->nullable()->after('images');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('content');
        });
    }
};
