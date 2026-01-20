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
        // Seed default Google tracking settings
        $settings = [
            ['key' => 'google_analytics_enabled', 'value' => '0', 'is_encrypted' => false],
            ['key' => 'google_analytics_id', 'value' => '', 'is_encrypted' => false],
            ['key' => 'google_event_page_view', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'google_event_begin_checkout', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'google_event_add_payment_info', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'google_event_purchase', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'google_event_view_bookings', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'google_event_booking_rescheduled', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'google_event_view_transactions', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'google_event_view_payment_page', 'value' => '1', 'is_encrypted' => false],
        ];

        foreach ($settings as $setting) {
            \App\Models\Setting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'is_encrypted' => $setting['is_encrypted']]
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove Google tracking settings
        \App\Models\Setting::whereIn('key', [
            'google_analytics_enabled',
            'google_analytics_id',
            'google_event_page_view',
            'google_event_begin_checkout',
            'google_event_add_payment_info',
            'google_event_purchase',
            'google_event_view_bookings',
            'google_event_booking_rescheduled',
            'google_event_view_transactions',
            'google_event_view_payment_page',
        ])->delete();
    }
};
