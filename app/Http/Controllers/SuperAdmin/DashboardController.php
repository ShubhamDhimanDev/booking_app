<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display super admin dashboard with platform metrics
     */
    public function index(Request $request)
    {
        $stats = [
            'total_organizations' => Organization::count(),
            'new_orgs_this_month' => Organization::whereMonth('created_at', now()->month)->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'subscription_rate' => $this->calculateSubscriptionRate(),
            'monthly_revenue' => $this->calculateMonthlyRevenue(),
            'revenue_growth' => $this->calculateRevenueGrowth(),
            'total_users' => User::count(),
        ];

        $recentOrganizations = Organization::with(['owner', 'activeSubscription.plan'])
            ->latest()
            ->take(10)
            ->get();

        $recentSubscriptions = Subscription::with(['organization', 'plan'])
            ->latest('started_at')
            ->take(10)
            ->get();

        $planDistribution = $this->getPlanDistribution();

        return view('super-admin.dashboard', compact(
            'stats',
            'recentOrganizations',
            'recentSubscriptions',
            'planDistribution'
        ));
    }

    protected function calculateSubscriptionRate()
    {
        $totalOrgs = Organization::count();
        if ($totalOrgs === 0) return 0;

        $subscribedOrgs = Organization::whereHas('activeSubscription')->count();
        return round(($subscribedOrgs / $totalOrgs) * 100, 1);
    }

    protected function calculateMonthlyRevenue()
    {
        return Subscription::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->sum('amount');
    }

    protected function calculateRevenueGrowth()
    {
        $thisMonth = Subscription::where('status', 'active')
            ->whereMonth('started_at', now()->month)
            ->sum('amount');

        $lastMonth = Subscription::where('status', 'active')
            ->whereMonth('started_at', now()->subMonth()->month)
            ->sum('amount');

        if ($lastMonth === 0) return 100;

        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    protected function getPlanDistribution()
    {
        $total = Subscription::where('status', 'active')->count();
        if ($total === 0) return collect();

        return SubscriptionPlan::withCount(['subscriptions' => function ($query) {
            $query->where('status', 'active');
        }])
            ->having('subscriptions_count', '>', 0)
            ->get()
            ->map(function ($plan) use ($total) {
                $plan->percentage = round(($plan->subscriptions_count / $total) * 100, 1);
                return $plan;
            });
    }
}
