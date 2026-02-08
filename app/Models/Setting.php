<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    protected $fillable = ['organization_id', 'key', 'value', 'is_encrypted'];

    /**
     * Boot the model - Add multi-tenancy scopes
     */
    protected static function booted()
    {
        // Auto-assign organization_id on creation
        static::creating(function ($setting) {
            // Skip during bootstrap
            if (!app()->isBooted()) {
                return;
            }

            if (!$setting->organization_id && app()->has('currentOrganization')) {
                $org = app('currentOrganization');
                if ($org && is_object($org) && property_exists($org, 'id')) {
                    $setting->organization_id = $org->id;
                }
            }
        });

        // Global scope to filter by organization (except for super-admin)
        static::addGlobalScope('organization', function ($builder) {
            // Skip during bootstrap
            if (!app()->isBooted()) {
                return;
            }

            if (app()->has('currentOrganization') && !app()->has('bypassTenantScope')) {
                $org = app('currentOrganization');
                if ($org && is_object($org) && property_exists($org, 'id')) {
                    $builder->where('settings.organization_id', $org->id);
                }
            }
        });
    }

    /**
     * Relationship: Setting belongs to an Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

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
