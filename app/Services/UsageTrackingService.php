<?php

namespace App\Services;

use App\Models\Organization;
use App\Models\UsageRecord;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * UsageTrackingService
 *
 * Tracks and manages organization usage metrics
 */
class UsageTrackingService
{
    /**
     * Record usage for an organization
     */
    public function recordUsage(Organization $org, string $metric, int $value = 1): void
    {
        try {
            $today = now()->toDateString();

            // Record in usage_records table
            UsageRecord::updateOrCreate(
                [
                    'organization_id' => $org->id,
                    'period_date' => $today,
                    'metric' => $metric,
                ],
                [
                    'value' => DB::raw("value + {$value}"),
                ]
            );

            // Update subscription usage counters if applicable
            if ($org->subscription) {
                $this->updateSubscriptionUsage($org->subscription, $metric, $value);
            }

            Log::debug('Usage recorded', [
                'organization_id' => $org->id,
                'metric' => $metric,
                'value' => $value,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to record usage', [
                'organization_id' => $org->id,
                'metric' => $metric,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Update subscription usage counters
     */
    protected function updateSubscriptionUsage(Subscription $subscription, string $metric, int $value = 1): void
    {
        switch ($metric) {
            case UsageRecord::METRIC_EVENTS:
            case 'events_created':
                $subscription->increment('events_used', $value);
                break;

            case UsageRecord::METRIC_BOOKINGS:
            case 'bookings_created':
                $subscription->increment('bookings_used', $value);
                break;

            case UsageRecord::METRIC_TEAM_MEMBERS:
            case 'team_members_added':
                $subscription->increment('team_members_used', $value);
                break;
        }
    }

    /**
     * Check if organization is within usage limits
     */
    public function checkLimit(Organization $org, string $resource): bool
    {
        $plan = $org->currentPlan;

        if (!$plan) {
            return true; // No limits if no plan
        }

        switch ($resource) {
            case 'events':
                $current = $org->events()->count();
                $limit = $plan->max_events;
                break;

            case 'bookings':
                if (!$org->subscription) return true;
                $current = $org->subscription->bookings_used;
                $limit = $plan->max_bookings_per_month;
                break;

            case 'team_members':
                $current = $org->users()->count();
                $limit = $plan->max_team_members;
                break;

            case 'promo_codes':
                $current = $org->promoCodes()->count();
                $limit = $plan->max_promo_codes;
                break;

            default:
                return true;
        }

        // Check if unlimited (999999 or higher)
        if ($limit >= 999999) {
            return true;
        }

        return $current < $limit;
    }

    /**
     * Get current usage for organization
     */
    public function getCurrentUsage(Organization $org): array
    {
        $plan = $org->currentPlan;

        if (!$plan) {
            return [
                'events' => ['used' => 0, 'limit' => 0, 'percentage' => 0],
                'bookings' => ['used' => 0, 'limit' => 0, 'percentage' => 0],
                'team_members' => ['used' => 0, 'limit' => 0, 'percentage' => 0],
            ];
        }

        $eventsUsed = $org->events()->count();
        $bookingsUsed = $org->subscription ? $org->subscription->bookings_used : 0;
        $teamMembersUsed = $org->users()->count();

        return [
            'events' => [
                'used' => $eventsUsed,
                'limit' => $plan->max_events,
                'percentage' => $this->calculatePercentage($eventsUsed, $plan->max_events),
                'unlimited' => $plan->max_events >= 999999,
            ],
            'bookings' => [
                'used' => $bookingsUsed,
                'limit' => $plan->max_bookings_per_month,
                'percentage' => $this->calculatePercentage($bookingsUsed, $plan->max_bookings_per_month),
                'unlimited' => $plan->max_bookings_per_month >= 999999,
            ],
            'team_members' => [
                'used' => $teamMembersUsed,
                'limit' => $plan->max_team_members,
                'percentage' => $this->calculatePercentage($teamMembersUsed, $plan->max_team_members),
                'unlimited' => $plan->max_team_members >= 999999,
            ],
        ];
    }

    /**
     * Calculate usage percentage
     */
    protected function calculatePercentage(int $used, int $limit): int
    {
        if ($limit === 0 || $limit >= 999999) {
            return 0;
        }

        return (int) min(100, ($used / $limit) * 100);
    }

    /**
     * Get usage analytics for a period
     */
    public function getUsageAnalytics(Organization $org, Carbon $startDate, Carbon $endDate): array
    {
        $records = UsageRecord::where('organization_id', $org->id)
            ->whereBetween('period_date', [$startDate, $endDate])
            ->get()
            ->groupBy('metric');

        $analytics = [];

        foreach ($records as $metric => $metricRecords) {
            $analytics[$metric] = [
                'total' => $metricRecords->sum('value'),
                'average_daily' => round($metricRecords->avg('value'), 2),
                'peak_day' => $metricRecords->sortByDesc('value')->first(),
                'trend' => $this->calculateTrend($metricRecords),
                'daily_data' => $metricRecords->pluck('value', 'period_date')->toArray(),
            ];
        }

        return $analytics;
    }

    /**
     * Calculate usage trend (growth/decline)
     */
    protected function calculateTrend($records): array
    {
        if ($records->count() < 2) {
            return ['direction' => 'stable', 'percentage' => 0];
        }

        $sorted = $records->sortBy('period_date')->values();
        $firstHalf = $sorted->take((int) ceil($sorted->count() / 2))->avg('value');
        $secondHalf = $sorted->skip((int) ceil($sorted->count() / 2))->avg('value');

        if ($firstHalf == 0) {
            return ['direction' => 'stable', 'percentage' => 0];
        }

        $change = (($secondHalf - $firstHalf) / $firstHalf) * 100;

        return [
            'direction' => $change > 5 ? 'growing' : ($change < -5 ? 'declining' : 'stable'),
            'percentage' => round(abs($change), 1),
        ];
    }

    /**
     * Reset monthly usage counters for all organizations
     */
    public function resetMonthlyUsage(): void
    {
        try {
            $subscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
                ->orWhere('status', Subscription::STATUS_TRIALING)
                ->get();

            foreach ($subscriptions as $subscription) {
                $subscription->resetUsageCounters();
            }

            Log::info('Monthly usage counters reset', [
                'count' => $subscriptions->count(),
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reset monthly usage', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Get organizations approaching limits
     */
    public function getOrganizationsApproachingLimits(int $threshold = 80): array
    {
        $organizations = Organization::with(['subscription', 'currentPlan'])
            ->whereHas('subscription', function ($query) {
                $query->where('status', Subscription::STATUS_ACTIVE)
                    ->orWhere('status', Subscription::STATUS_TRIALING);
            })
            ->get();

        $approaching = [];

        foreach ($organizations as $org) {
            $usage = $this->getCurrentUsage($org);

            foreach ($usage as $resource => $data) {
                if (!$data['unlimited'] && $data['percentage'] >= $threshold) {
                    $approaching[] = [
                        'organization' => $org,
                        'resource' => $resource,
                        'usage' => $data,
                    ];
                }
            }
        }

        return $approaching;
    }

    /**
     * Export usage data for billing/reporting
     */
    public function exportUsageData(Organization $org, Carbon $startDate, Carbon $endDate): array
    {
        $records = UsageRecord::where('organization_id', $org->id)
            ->whereBetween('period_date', [$startDate, $endDate])
            ->orderBy('period_date')
            ->orderBy('metric')
            ->get();

        return [
            'organization' => [
                'id' => $org->id,
                'name' => $org->name,
                'plan' => $org->currentPlan?->name,
            ],
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
                'days' => $startDate->diffInDays($endDate) + 1,
            ],
            'records' => $records->map(function ($record) {
                return [
                    'date' => $record->period_date,
                    'metric' => $record->metric,
                    'value' => $record->value,
                    'metadata' => $record->metadata,
                ];
            })->toArray(),
            'summary' => $this->getUsageAnalytics($org, $startDate, $endDate),
        ];
    }
}
