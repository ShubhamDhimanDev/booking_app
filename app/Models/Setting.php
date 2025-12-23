<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'is_encrypted'];

    /**
     * Get the decrypted value if encrypted
     */
    public function getDecryptedValueAttribute()
    {
        if ($this->is_encrypted) {
            return Crypt::decrypt($this->value);
        }
        return $this->value;
    }

    /**
     * Encrypt value before saving if marked as encrypted
     */
    public static function setSetting($key, $value, $encrypt = false)
    {
        $data = ['value' => $encrypt ? Crypt::encrypt($value) : $value, 'is_encrypted' => $encrypt];
        return self::updateOrCreate(['key' => $key], $data);
    }

    /**
     * Get setting value, decrypt if needed
     */
    public static function getSetting($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return $default;
        return $setting->is_encrypted ? Crypt::decrypt($setting->value) : $setting->value;
    }
}
