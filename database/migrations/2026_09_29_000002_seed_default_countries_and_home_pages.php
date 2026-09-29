<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data migration: turns the old hardcoded India/US regions into Country rows,
 * moves the stored homepage HTML (settings homepage_html_in / _us) into a home
 * Page with one raw-HTML section each, and assigns existing events to a country
 * by currency.
 */
return new class extends Migration
{
    public function up()
    {
        $now = now();

        $defaults = [
            ['name' => 'India', 'slug' => 'en-in', 'iso_code' => 'IN', 'currency' => 'INR', 'default_timezone' => 'Asia/Kolkata', 'is_default' => true, 'sort_order' => 1, 'setting' => 'homepage_html_in'],
            ['name' => 'United States', 'slug' => 'en-us', 'iso_code' => 'US', 'currency' => 'USD', 'default_timezone' => 'America/New_York', 'is_default' => false, 'sort_order' => 2, 'setting' => 'homepage_html_us'],
        ];

        foreach ($defaults as $row) {
            $setting = $row['setting'];
            unset($row['setting']);

            $countryId = DB::table('countries')->where('slug', $row['slug'])->value('id');
            if (! $countryId) {
                $countryId = DB::table('countries')->insertGetId($row + ['is_active' => true, 'created_at' => $now, 'updated_at' => $now]);
            }

            $pageId = DB::table('pages')->insertGetId([
                'country_id' => $countryId,
                'title' => 'Home',
                'slug' => 'home',
                'is_home' => true,
                'status' => 'published',
                'sort_order' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $html = DB::table('settings')->where('key', $setting)->value('value');
            if ($html !== null && trim($html) !== '') {
                DB::table('page_sections')->insert([
                    'page_id' => $pageId,
                    'type' => 'html',
                    'content' => json_encode(['html' => $html]),
                    'sort_order' => 0,
                    'is_visible' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            DB::table('events')->whereNull('country_id')->where('currency', $row['currency'])->update(['country_id' => $countryId]);
        }

        // Any event with some other currency goes to the default country.
        $defaultId = DB::table('countries')->where('is_default', true)->value('id');
        DB::table('events')->whereNull('country_id')->update(['country_id' => $defaultId]);
    }

    public function down()
    {
        // The tables are dropped by the previous migration's down().
    }
};
