<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'dark_mode',
    ];

    protected $casts = [
        'dark_mode' => 'boolean',
    ];

    /**
     * Get the system settings for a user
     */
    public static function getSettings($userId = null)
    {
        if ($userId) {
            return static::firstOrCreate(
                ['user_id' => $userId],
                ['dark_mode' => false]
            );
        }

        // Default settings for non-authenticated users
        return static::whereNull('user_id')->first() ?? static::create([
            'user_id' => null,
            'dark_mode' => false,
        ]);
    }

    /**
     * Relationship with User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
