<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * PromoCode Model - SaaS Ready
 *
 * Recommended Database Indexes:
 * - UNIQUE Index: (code)
 * - Index: (is_active, valid_until)
 * - Index: (valid_from, valid_until)
 *
 * @property int $id
 * @property string $code
 * @property string|null $description
 * @property string $discount_type
 * @property float $discount_value
 * @property float|null $min_booking_amount
 * @property float|null $max_discount_amount
 * @property int|null $usage_limit
 * @property int $usage_count
 * @property \Carbon\Carbon|null $valid_from
 * @property \Carbon\Carbon|null $valid_until
 * @property bool $is_active
 */
class PromoCode extends Model
{
    use HasFactory;

    /**
     * Mass-assignable attributes
     */
    protected $fillable = [
        'organization_id',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_booking_amount',
        'max_discount_amount',
        'usage_limit',
        'usage_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    /**
     * Boot the model - Add multi-tenancy scopes
     */
    protected static function booted()
    {
        // Auto-assign organization_id on creation
        static::creating(function ($promoCode) {
            // Skip during bootstrap
            if (!app()->isBooted()) {
                return;
            }

            if (!$promoCode->organization_id && app()->has('currentOrganization')) {
                $org = app('currentOrganization');
                if ($org && is_object($org) && property_exists($org, 'id')) {
                    $promoCode->organization_id = $org->id;
                }
            }
        });

        // Global scope to filter by organization (except for super-admin)
        static::addGlobalScope('organization', function (Builder $builder) {
            // Skip during bootstrap
            if (!app()->isBooted()) {
                return;
            }

            if (app()->has('currentOrganization') && !app()->has('bypassTenantScope')) {
                $org = app('currentOrganization');
                if ($org && is_object($org) && property_exists($org, 'id')) {
                    $builder->where('promo_codes.organization_id', $org->id);
                }
            }
        });
    }

    /**
     * Relationship: PromoCode belongs to an Organization
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Attributes that should be cast
     */
    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_booking_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // ==================== CONSTANTS ====================

    public const TYPE_PERCENTAGE = 'percentage';
    public const TYPE_FIXED = 'fixed';

    // ==================== QUERY SCOPES ====================

    /**
     * Scope: Get only active promo codes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Get valid promo codes (active and within date range)
     */
    public function scopeValidNow(Builder $query): Builder
    {
        $now = Carbon::now();
        return $query->active()
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')
                  ->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_until')
                  ->orWhere('valid_until', '>=', $now);
            });
    }

    /**
     * Scope: Promo codes with available usage
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->whereNull('usage_limit')
              ->orWhereRaw('usage_count < usage_limit');
        });
    }

    /**
     * Scope: Search by code
     */
    public function scopeByCode(Builder $query, string $code): Builder
    {
        return $query->where('code', strtoupper($code));
    }

    // ==================== HELPER METHODS ====================

    // ==================== HELPER METHODS ====================

    /**
     * Check if promo code is valid for use
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check validity dates
        $now = Carbon::now();
        if ($this->valid_from && $now->lt($this->valid_from)) {
            return false;
        }
        if ($this->valid_until && $now->gt($this->valid_until)) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    /**
     * Calculate discount amount for given booking amount
     *
     * NOTE: Consider moving to PromoCodeService for complex business logic
     */
    public function calculateDiscount(float $bookingAmount): float
    {
        // Check minimum booking amount
        if ($this->min_booking_amount && $bookingAmount < $this->min_booking_amount) {
            return 0;
        }

        if ($this->discount_type === self::TYPE_PERCENTAGE) {
            $discount = ($bookingAmount * $this->discount_value) / 100;

            // Apply max discount cap if set
            if ($this->max_discount_amount && $discount > $this->max_discount_amount) {
                return (float) $this->max_discount_amount;
            }

            return $discount;
        }

        // Fixed discount
        return min((float) $this->discount_value, $bookingAmount);
    }

    /**
     * Increment usage count atomically to prevent race conditions
     *
     * FIXED: Now uses atomic increment to avoid concurrent booking issues
     */
    public function incrementUsage(): bool
    {
        // Use atomic increment to prevent race conditions
        return $this->increment('usage_count') > 0;
    }

    /**
     * Check if promo code can be used (has remaining usage)
     */
    public function canBeUsed(): bool
    {
        return $this->isValid() &&
               (!$this->usage_limit || $this->usage_count < $this->usage_limit);
    }

    /**
     * Get remaining uses
     */
    public function getRemainingUses(): ?int
    {
        if (!$this->usage_limit) {
            return null; // Unlimited
        }

        return max(0, $this->usage_limit - $this->usage_count);
    }
}
