<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    /**
     * Get system settings with caching for a specific user
     */
    public static function getSettings($userId = null)
    {
        $cacheKey = $userId ? "system_settings_user_{$userId}" : 'system_settings_default';

        return Cache::remember($cacheKey, 3600, function () use ($userId) {
            return SystemSetting::getSettings($userId);
        });
    }

    /**
     * Check if dark mode is enabled for a user
     */
    public static function isDarkModeEnabled($userId = null): bool
    {
        // If no userId provided, try to get from auth
        if ($userId === null && auth()->check()) {
            $userId = auth()->id();
        }

        // For guests, return false (they manage via localStorage)
        if ($userId === null) {
            return false;
        }

        return static::getSettings($userId)->dark_mode;
    }

    /**
     * Clear settings cache
     */
    public static function clearCache($userId = null)
    {
        if ($userId) {
            Cache::forget("system_settings_user_{$userId}");
        } else {
            Cache::forget('system_settings_default');
        }
    }

    /**
     * Get theme-specific CSS classes for a user
     */
    public static function getThemeClasses($userId = null): string
    {
        $classes = [];

        if (static::isDarkModeEnabled($userId)) {
            $classes[] = 'dark-mode';
        }

        return implode(' ', $classes);
    }
}
