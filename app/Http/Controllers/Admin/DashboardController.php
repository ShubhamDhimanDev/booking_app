<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Response;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->toDateString();

        // Upcoming sessions (confirmed or pending)
        $upcoming = Booking::with('event')
            ->whereDate('booked_at_date', '>=', $today)
            ->whereIn('status', ['confirmed', 'pending'])
            ->orderBy('booked_at_date')
            ->limit(15)
            ->get();

        // Past sessions (held)
        $past = Booking::with('event')
            ->whereDate('booked_at_date', '<', $today)
            ->where('status', 'confirmed')
            ->orderByDesc('booked_at_date')
            ->limit(15)
            ->get();

        // Simple analytics
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $analytics = [
            'total_this_month' => Booking::whereBetween('booked_at_date', [$startOfMonth, $endOfMonth])->count(),
            'held_this_month' => Booking::whereBetween('booked_at_date', [$startOfMonth, $endOfMonth])->whereDate('booked_at_date', '<', $today)->count(),
            'upcoming_this_month' => Booking::whereBetween('booked_at_date', [$startOfMonth, $endOfMonth])->whereDate('booked_at_date', '>=', $today)->count(),
            'cancelled_this_month' => Booking::whereBetween('booked_at_date', [$startOfMonth, $endOfMonth])->where('status', 'cancelled')->count(),
            'sessions_last_7_days' => Booking::whereBetween('booked_at_date', [Carbon::now()->subDays(6)->toDateString(), $today])->count(),
        ];

        // Bookings last 7 days (labels and data)
        $bookingsLast7 = [];
        $bookingsLast7Labels = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = Carbon::now()->subDays($i)->toDateString();
            $bookingsLast7Labels[] = Carbon::parse($d)->format('d M');
            $bookingsLast7[] = Booking::whereDate('booked_at_date', $d)->count();
        }

        // Bookings last 6 months (including current)
        $bookingsLast6Months = [];
        $bookingsLast6Labels = [];
        for ($m = 5; $m >= 0; $m--) {
            $start = Carbon::now()->subMonths($m)->startOfMonth();
            $end = Carbon::now()->subMonths($m)->endOfMonth();
            $bookingsLast6Labels[] = $start->format('M Y');
            $bookingsLast6Months[] = Booking::whereBetween('booked_at_date', [$start->toDateString(), $end->toDateString()])->count();
        }

        // Payments last 6 months totals
        $paymentsLast6 = [];
        $paymentsLast6Labels = [];
        for ($m = 5; $m >= 0; $m--) {
            $start = Carbon::now()->subMonths($m)->startOfMonth();
            $end = Carbon::now()->subMonths($m)->endOfMonth();
            $paymentsLast6Labels[] = $start->format('M Y');
            $paymentsLast6[] = Payment::whereBetween('created_at', [$start->toDateString(), $end->toDateString()])->sum('amount');
        }

        return view('admin.dashboard', compact(
            'upcoming', 'past', 'analytics',
            'bookingsLast7Labels', 'bookingsLast7',
            'bookingsLast6Labels', 'bookingsLast6Months',
            'paymentsLast6Labels', 'paymentsLast6'
        ));
    }

    /**
     * Export sessions between a start and end date as CSV
     */
    public function exportSessions(Request $request)
    {
        $start = $request->input('start') ?: '1970-01-01';
        $end = $request->input('end') ?: Carbon::now()->toDateString();

        $rows = Booking::with('event')
            ->whereBetween('booked_at_date', [$start, $end])
            ->orderBy('booked_at_date')
            ->get();

        $filename = "sessions_{$start}_to_{$end}.csv";

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Event', 'Booker Name', 'Booker Email', 'Date', 'Time', 'Status']);
            foreach ($rows as $r) {
                fputcsv($file, [
                    $r->id,
                    optional($r->event)->title,
                    $r->booker_name,
                    $r->booker_email,
                    $r->booked_at_date,
                    $r->booked_at_time,
                    $r->status,
                ]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
