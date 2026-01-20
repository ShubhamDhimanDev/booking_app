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
        // Seed default tracking settings
        $settings = [
            ['key' => 'meta_pixel_enabled', 'value' => '0', 'is_encrypted' => false],
            ['key' => 'meta_pixel_id', 'value' => '', 'is_encrypted' => false],
            ['key' => 'meta_event_page_view', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'meta_event_initiate_checkout', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'meta_event_add_payment_info', 'value' => '1', 'is_encrypted' => false],
            ['key' => 'meta_event_purchase', 'value' => '1', 'is_encrypted' => false],
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
        // Remove tracking settings
        \App\Models\Setting::whereIn('key', [
            'meta_pixel_enabled',
            'meta_pixel_id',
            'meta_event_page_view',
            'meta_event_initiate_checkout',
            'meta_event_add_payment_info',
            'meta_event_purchase',
        ])->delete();
    }
};
