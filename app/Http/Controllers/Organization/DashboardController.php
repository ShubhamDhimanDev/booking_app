<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display organization dashboard with key metrics
     */
    public function index(Request $request)
    {
        $organization = auth()->user()->organization;

        $stats = [
            'total_events' => $organization->events()->count(),
            'total_bookings' => $organization->bookings()->count(),
            'revenue' => $organization->bookings()
                ->where('payment_status', 'completed')
                ->sum('amount'),
            'team_members' => $organization->users()->count(),
        ];

        $recentBookings = $organization->bookings()
            ->with(['event', 'user'])
            ->latest()
            ->take(10)
            ->get();

        $subscription = $organization->activeSubscription;

        return view('organization.dashboard', compact('stats', 'recentBookings', 'subscription'));
    }
}
