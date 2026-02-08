<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * SubscriptionPlan Model
 *
 * Defines subscription tiers and their features/limits
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property array|null $features_list
 * @property float $price_monthly
 * @property float $price_yearly
 * @property int $discount_yearly_percent
 * @property array|null $gateway_plan_ids
 * @property int $max_events
 * @property int $max_bookings_per_month
 * @property int $max_team_members
 * @property int $max_promo_codes
 * @property bool $custom_domain
 * @property bool $white_label
 * @property bool $api_access
 * @property bool $priority_support
 * @property bool $advanced_analytics
 * @property bool $google_calendar
 * @property bool $email_reminders
 * @property bool $remove_branding
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $sort_order
 * @property array|null $metadata
 */
class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'features_list',
        'price_monthly',
        'price_yearly',
        'discount_yearly_percent',
        'gateway_plan_ids',
        'max_events',
        'max_bookings_per_month',
        'max_team_members',
        'max_promo_codes',
        'custom_domain',
        'white_label',
        'api_access',
        'priority_support',
        'advanced_analytics',
        'google_calendar',
        'email_reminders',
        'remove_branding',
        'is_active',
        'is_featured',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'features_list' => 'array',
        'gateway_plan_ids' => 'array',
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'discount_yearly_percent' => 'integer',
        'max_events' => 'integer',
        'max_bookings_per_month' => 'integer',
        'max_team_members' => 'integer',
        'max_promo_codes' => 'integer',
        'custom_domain' => 'boolean',
        'white_label' => 'boolean',
        'api_access' => 'boolean',
        'priority_support' => 'boolean',
        'advanced_analytics' => 'boolean',
        'google_calendar' => 'boolean',
        'email_reminders' => 'boolean',
        'remove_branding' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'metadata' => 'array',
    ];

    // ==================== RELATIONSHIPS ====================

    /**
     * Organizations using this plan
     */
    public function organizations()
    {
        return $this->hasMany(Organization::class, 'current_plan_id');
    }

    /**
     * Subscriptions on this plan
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Active plans only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Featured plans
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Ordered by sort order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // ==================== HELPER METHODS ====================

    /**
     * Get yearly savings amount
     */
    public function getYearlySavingsAttribute(): float
    {
        return ($this->price_monthly * 12) - $this->price_yearly;
    }

    /**
     * Get yearly savings percentage
     */
    public function getYearlySavingsPercentAttribute(): int
    {
        if ($this->price_monthly == 0) return 0;

        $monthly_total = $this->price_monthly * 12;
        return (int) round((($monthly_total - $this->price_yearly) / $monthly_total) * 100);
    }

    /**
     * Get formatted monthly price
     */
    public function getFormattedMonthlyPriceAttribute(): string
    {
        return '₹' . number_format($this->price_monthly, 0);
    }

    /**
     * Get formatted yearly price
     */
    public function getFormattedYearlyPriceAttribute(): string
    {
        return '₹' . number_format($this->price_yearly, 0);
    }

    /**
     * Check if plan has feature
     */
    public function hasFeature(string $feature): bool
    {
        return $this->{$feature} ?? false;
    }

    /**
     * Check if unlimited
     */
    public function isUnlimited(): bool
    {
        return $this->max_events >= 999999;
    }

    /**
     * Get display name for limit
     */
    public function getEventsLimitDisplay(): string
    {
        return $this->isUnlimited() ? 'Unlimited' : number_format($this->max_events);
    }

    public function getBookingsLimitDisplay(): string
    {
        return $this->isUnlimited() ? 'Unlimited' : number_format($this->max_bookings_per_month);
    }

    public function getTeamMembersLimitDisplay(): string
    {
        return $this->isUnlimited() ? 'Unlimited' : number_format($this->max_team_members);
    }
}
