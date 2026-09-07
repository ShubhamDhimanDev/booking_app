<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $columns = array_filter(['available_from_time', 'available_to_time'], function ($column) {
            return Schema::hasColumn('events', $column);
        });

        if (! empty($columns)) {
            Schema::table('events', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }

    public function down()
    {
        Schema::table('events', function (Blueprint $table) {
            if (! Schema::hasColumn('events', 'available_from_time')) {
                $table->time('available_from_time')->nullable()->after('available_from_date');
            }
            if (! Schema::hasColumn('events', 'available_to_time')) {
                $table->time('available_to_time')->nullable()->after('available_from_time');
            }
        });
    }
};
