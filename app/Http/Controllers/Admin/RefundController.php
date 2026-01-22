<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use App\Models\Booking;
use App\Jobs\ProcessRefundJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RefundController extends Controller
{
    /**
     * Display a listing of refunds
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $query = Refund::with(['booking.event', 'booking.booker', 'payment', 'initiatedBy'])
            ->latest();

        // Apply filters
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('gateway') && $request->gateway !== 'all') {
            $query->where('gateway', $request->gateway);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('booking', function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%");
            });
        }

        $refunds = $query->paginate(20);

        // Calculate statistics
        $stats = [
            'total' => Refund::count(),
            'pending' => Refund::where('status', 'pending')->count(),
            'processing' => Refund::where('status', 'processing')->count(),
            'completed' => Refund::where('status', 'completed')->count(),
            'failed' => Refund::where('status', 'failed')->count(),
            'total_amount' => Refund::where('status', 'completed')->sum('net_refund_amount'),
        ];

        return view('admin.refunds.index', compact('refunds', 'stats'));
    }

    /**
     * Show refund details
     *
     * @param Refund $refund
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Refund $refund)
    {
        $refund->load(['booking.event', 'booking.booker', 'payment', 'initiatedBy']);

        return view('admin.refunds.show', compact('refund'));
    }

    /**
     * Retry a failed refund
     *
     * @param Refund $refund
     * @return \Illuminate\Http\RedirectResponse
     */
    public function retry(Refund $refund)
    {
        if (!in_array($refund->status, ['failed', 'pending'])) {
            return back()->with([
                'alert_type' => 'error',
                'alert_message' => 'Only failed or pending refunds can be retried.',
            ]);
        }

        // Reset status to pending
        $refund->update([
            'status' => 'pending',
            'failure_reason' => null,
        ]);

        // Dispatch job again
        ProcessRefundJob::dispatch($refund);

        Log::info('Admin manually retried refund', [
            'refund_id' => $refund->id,
            'admin_id' => auth()->id(),
        ]);

        return back()->with([
            'alert_type' => 'success',
            'alert_message' => 'Refund has been queued for retry.',
        ]);
    }

    /**
     * Export refunds to CSV
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $query = Refund::with(['booking.event', 'booking.booker', 'payment'])
            ->latest();

        // Apply same filters as index
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('gateway') && $request->gateway !== 'all') {
            $query->where('gateway', $request->gateway);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $refunds = $query->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="refunds_' . date('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($refunds) {
            $file = fopen('php://output', 'w');

            // CSV headers
            fputcsv($file, [
                'Refund ID',
                'Booking ID',
                'Event',
                'Customer',
                'Amount',
                'Gateway Charges',
                'Net Refund',
                'Status',
                'Gateway',
                'Gateway Refund ID',
                'Initiated By',
                'Created At',
                'Processed At',
                'Failure Reason',
            ]);

            // CSV rows
            foreach ($refunds as $refund) {
                fputcsv($file, [
                    $refund->id,
                    $refund->booking_id,
                    $refund->booking->event->title ?? 'N/A',
                    $refund->booking->booker->name ?? 'N/A',
                    number_format($refund->amount, 2),
                    number_format($refund->gateway_charges, 2),
                    number_format($refund->net_refund_amount, 2),
                    $refund->status,
                    $refund->gateway,
                    $refund->gateway_refund_id ?? 'N/A',
                    ucfirst($refund->initiated_by),
                    $refund->created_at->format('Y-m-d H:i:s'),
                    $refund->processed_at ? $refund->processed_at->format('Y-m-d H:i:s') : 'N/A',
                    $refund->failure_reason ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
