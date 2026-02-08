<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * UsageRecord Model
 *
 * Tracks daily/monthly usage metrics for organizations
 *
 * @property int $id
 * @property int $organization_id
 * @property string $period_date
 * @property string $metric
 * @property int $value
 * @property array|null $metadata
 */
class UsageRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'period_date',
        'metric',
        'value',
        'metadata',
    ];

    protected $casts = [
        'period_date' => 'date',
        'value' => 'integer',
        'metadata' => 'array',
    ];

    // ==================== CONSTANTS ====================

    public const METRIC_EVENTS = 'events_count';
    public const METRIC_BOOKINGS = 'bookings_count';
    public const METRIC_TEAM_MEMBERS = 'team_members_count';
    public const METRIC_API_CALLS = 'api_calls';
    public const METRIC_EMAIL_SENT = 'emails_sent';

    // ==================== RELATIONSHIPS ====================

    /**
     * The organization this usage record belongs to
     */
    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Records for a specific metric
     */
    public function scopeForMetric($query, string $metric)
    {
        return $query->where('metric', $metric);
    }

    /**
     * Scope: Records for a date range
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('period_date', [$startDate, $endDate]);
    }

    /**
     * Scope: Current month records
     */
    public function scopeCurrentMonth($query)
    {
        return $query->whereYear('period_date', now()->year)
            ->whereMonth('period_date', now()->month);
    }

    /**
     * Scope: Last 30 days
     */
    public function scopeLast30Days($query)
    {
        return $query->where('period_date', '>=', now()->subDays(30));
    }

    // ==================== HELPER METHODS ====================

    /**
     * Increment usage value
     */
    public function incrementValue(int $amount = 1): void
    {
        $this->increment('value', $amount);
    }

    /**
     * Get total usage for organization and metric
     */
    public static function getTotalUsage(int $organizationId, string $metric, $startDate = null, $endDate = null): int
    {
        $query = self::where('organization_id', $organizationId)
            ->where('metric', $metric);

        if ($startDate && $endDate) {
            $query->whereBetween('period_date', [$startDate, $endDate]);
        }

        return $query->sum('value');
    }

    /**
     * Record usage for today
     */
    public static function recordUsage(int $organizationId, string $metric, int $value = 1): self
    {
        return self::updateOrCreate(
            [
                'organization_id' => $organizationId,
                'period_date' => now()->toDateString(),
                'metric' => $metric,
            ],
            [
                'value' => \DB::raw("value + {$value}"),
            ]
        );
    }

    /**
     * Get usage breakdown for organization
     */
    public static function getUsageBreakdown(int $organizationId, $startDate, $endDate): array
    {
        $records = self::where('organization_id', $organizationId)
            ->whereBetween('period_date', [$startDate, $endDate])
            ->get()
            ->groupBy('metric');

        $breakdown = [];

        foreach ($records as $metric => $metricRecords) {
            $breakdown[$metric] = [
                'total' => $metricRecords->sum('value'),
                'daily' => $metricRecords->pluck('value', 'period_date')->toArray(),
            ];
        }

        return $breakdown;
    }
}
