<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Subscription;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Display reports dashboard
     */
    public function index()
    {
        return view('super-admin.reports.index');
    }

    /**
     * Revenue report
     */
    public function revenue(Request $request)
    {
        $period = $request->get('period', 'monthly'); // monthly, yearly

        $revenueData = Invoice::where('status', 'paid')
            ->select(
                DB::raw('DATE_FORMAT(invoice_date, "%Y-%m") as period'),
                DB::raw('SUM(amount) as total'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->take(12)
            ->get();

        return view('super-admin.reports.revenue', compact('revenueData', 'period'));
    }

    /**
     * Subscriptions report
     */
    public function subscriptions(Request $request)
    {
        $stats = [
            'active' => Subscription::where('status', 'active')->count(),
            'trial' => Subscription::where('status', 'trial')->count(),
            'cancelled' => Subscription::where('status', 'cancelled')->count(),
            'expired' => Subscription::where('status', 'expired')->count(),
        ];

        $growthData = Subscription::select(
                DB::raw('DATE_FORMAT(started_at, "%Y-%m") as period'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->take(12)
            ->get();

        return view('super-admin.reports.subscriptions', compact('stats', 'growthData'));
    }

    /**
     * Organizations report
     */
    public function organizations(Request $request)
    {
        $stats = [
            'total' => Organization::count(),
            'active' => Organization::where('status', 'active')->count(),
            'inactive' => Organization::where('status', 'inactive')->count(),
            'suspended' => Organization::where('status', 'suspended')->count(),
        ];

        $growthData = Organization::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as period'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('period')
            ->orderBy('period', 'desc')
            ->take(12)
            ->get();

        return view('super-admin.reports.organizations', compact('stats', 'growthData'));
    }

    /**
     * Export reports
     */
    public function export(Request $request)
    {
        $type = $request->get('type', 'revenue');

        // Implementation for CSV/Excel export
        return back()->with('info', 'Export feature coming soon!');
    }
}
