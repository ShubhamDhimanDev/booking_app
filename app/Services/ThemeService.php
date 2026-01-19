<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;

class ThemeService
{
    /**
     * Get system settings with caching
     */
    public static function getSettings()
    {
        return Cache::remember('system_settings', 3600, function () {
            return SystemSetting::getSettings();
        });
    }

    /**
     * Get active theme layout
     */
    public static function getActiveTheme(): string
    {
        return static::getSettings()->theme_layout;
    }

    /**
     * Check if dark mode is enabled
     */
    public static function isDarkModeEnabled(): bool
    {
        return static::getSettings()->dark_mode;
    }

    /**
     * Get user layout path based on active theme
     */
    public static function getUserLayout(): string
    {
        $theme = static::getActiveTheme();
        return "layouts.user-{$theme}";
    }

    /**
     * Clear settings cache
     */
    public static function clearCache()
    {
        Cache::forget('system_settings');
    }

    /**
     * Get theme-specific CSS classes
     */
    public static function getThemeClasses(): string
    {
        $classes = [];

        if (static::isDarkModeEnabled()) {
            $classes[] = 'dark-mode';
        }

        $classes[] = 'theme-' . static::getActiveTheme();

        return implode(' ', $classes);
    }
}
