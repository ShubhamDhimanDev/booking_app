<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Organization Model - Multi-Tenant SaaS
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $domain
 * @property string|null $description
 * @property int $owner_id
 * @property string $contact_email
 * @property string|null $contact_phone
 * @property string $status
 * @property int|null $current_plan_id
 * @property \Carbon\Carbon|null $trial_ends_at
 * @property \Carbon\Carbon|null $subscribed_at
 * @property string $timezone
 * @property string $currency
 * @property string $locale
 * @property array|null $branding
 * @property array|null $settings
 * @property string $default_payment_gateway
 * @property array|null $gateway_customer_ids
 * @property string|null $billing_email
 * @property string|null $billing_address
 * @property string|null $gstin
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Organization extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'domain',
        'description',
        'owner_id',
        'contact_email',
        'contact_phone',
        'status',
        'current_plan_id',
        'trial_ends_at',
        'subscribed_at',
        'timezone',
        'currency',
        'locale',
        'branding',
        'settings',
        'default_payment_gateway',
        'gateway_customer_ids',
        'billing_email',
        'billing_address',
        'gstin',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'branding' => 'array',
        'settings' => 'array',
        'gateway_customer_ids' => 'array',
        'trial_ends_at' => 'datetime',
        'subscribed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * The owner of the organization
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * All users in this organization
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * All events in this organization
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * All bookings in this organization
     */
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * All payments in this organization
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * All promo codes in this organization
     */
    public function promoCodes()
    {
        return $this->hasMany(PromoCode::class);
    }

    /**
     * The organization's subscription (Phase 2)
     */
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    /**
     * The organization's current subscription plan (Phase 2)
     */
    public function currentPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'current_plan_id');
    }

    /**
     * Organization usage records (Phase 2)
     */
    public function usageRecords()
    {
        return $this->hasMany(UsageRecord::class);
    }

    /**
     * Organization invoices (Phase 2)
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Organization invitations
     */
    public function invitations()
    {
        return $this->hasMany(OrganizationInvitation::class);
    }

    /**
     * Organization activity logs
     */
    public function activityLogs()
    {
        return $this->hasMany(OrganizationActivityLog::class);
    }

    // ==================== QUERY SCOPES ====================

    /**
     * Scope: Filter active organizations
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope: Filter trial organizations
     */
    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    /**
     * Scope: Filter suspended organizations
     */
    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Check if organization is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if organization is on trial
     */
    public function isOnTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at > now();
    }

    /**
     * Check if organization has expired trial
     */
    public function hasExpiredTrial(): bool
    {
        return $this->status === 'trial' && $this->trial_ends_at && $this->trial_ends_at <= now();
    }

    /**
     * Check if organization is suspended
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Check if organization is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get the organization's subdomain URL
     */
    public function getSubdomainUrlAttribute(): string
    {
        $domain = config('app.domain', 'meetflow.app');
        return "https://{$this->slug}.{$domain}";
    }

    /**
     * Get the organization's primary URL (custom domain or subdomain)
     */
    public function getPrimaryUrlAttribute(): string
    {
        if ($this->domain) {
            return "https://{$this->domain}";
        }
        return $this->subdomain_url;
    }
}
