<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Setting;

class TrackingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Meta Pixel tracking settings
        $metaSettings = [
            ['key' => 'meta_pixel_enabled', 'value' => '0', 'is_encrypted' => false],
            ['key' => 'meta_pixel_id', 'value' => '', 'is_encrypted' => false],
            ['key' => 'meta_event_page_view', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'meta_event_initiate_checkout', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'meta_event_add_payment_info', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'meta_event_purchase', 'value' => '1', 'is_encrypted' => false],
        ];

        // Google Analytics tracking settings
        $googleSettings = [
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

        $allSettings = array_merge($metaSettings, $googleSettings);

        foreach ($allSettings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                ['value' => $setting['value'], 'is_encrypted' => $setting['is_encrypted']]
            );
        }
    }
}
