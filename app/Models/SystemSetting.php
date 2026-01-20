<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_layout',
        'dark_mode',
    ];

    protected $casts = [
        'dark_mode' => 'boolean',
    ];

    /**
     * Get the system settings instance
     */
    public static function getSettings()
    {
        return static::first() ?? static::create([
            'theme_layout' => 'modern',
            'dark_mode' => false,
        ]);
    }
}
