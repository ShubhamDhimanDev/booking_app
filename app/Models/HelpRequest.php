<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HelpRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'email',
        'subject',
        'message',
        'status',
    ];

    /**
     * Boot the model - Add multi-tenancy scopes
     */
    protected static function booted()
    {
        // Auto-assign organization_id on creation
        static::creating(function ($helpRequest) {
            // Skip during bootstrap
            if (!app()->isBooted()) {
                return;
            }

            if (!$helpRequest->organization_id && app()->has('currentOrganization')) {
                $org = app('currentOrganization');
                if ($org && is_object($org) && property_exists($org, 'id')) {
                    $helpRequest->organization_id = $org->id;
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
                    $builder->where('help_requests.organization_id', $org->id);
                }
            }
        });
    }

    /**
     * Relationship: HelpRequest belongs to an Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    protected $attributes = [
        'status' => 'open',
    ];
}
