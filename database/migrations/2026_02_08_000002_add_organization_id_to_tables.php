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
        // Add organization_id to users table
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->index('organization_id');
        });

        // Add organization_id to events table
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->index(['organization_id', 'user_id', 'created_at']);
        });

        // Add organization_id to bookings table
        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->index(['organization_id', 'status', 'booked_at_date']);
        });

        // Add organization_id to payments table
        Schema::table('payments', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->index(['organization_id', 'status', 'created_at']);
        });

        // Add organization_id to promo_codes table
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->index('organization_id');
        });

        // Add organization_id to refunds table
        Schema::table('refunds', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->index(['organization_id', 'status']);
        });

        // Add organization_id to settings table
        Schema::table('settings', function (Blueprint $table) {
            // Drop existing unique constraint on 'key' if exists
            $table->dropUnique(['key']);

            $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->unique(['organization_id', 'key']);
            $table->index('organization_id');
        });

        // Add organization_id to follow_up_invites table
        Schema::table('follow_up_invites', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->index('organization_id');
        });

        // Add organization_id to help_requests table
        Schema::table('help_requests', function (Blueprint $table) {
            $table->foreignId('organization_id')->after('id')->nullable()->constrained()->onDelete('cascade');
            $table->index('organization_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'user_id', 'created_at']);
            $table->dropColumn('organization_id');
        });

        Schema::table('bookings', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'status', 'booked_at_date']);
            $table->dropColumn('organization_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'status', 'created_at']);
            $table->dropColumn('organization_id');
        });

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id', 'status']);
            $table->dropColumn('organization_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropUnique(['organization_id', 'key']);
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
            $table->unique('key');
        });

        Schema::table('follow_up_invites', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });

        Schema::table('help_requests', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropIndex(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
