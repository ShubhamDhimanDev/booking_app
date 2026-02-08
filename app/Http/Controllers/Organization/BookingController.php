<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings
     */
    public function index(Request $request)
    {
        $organization = auth()->user()->organization;

        $stats = [
            'total' => $organization->bookings()->count(),
            'confirmed' => $organization->bookings()->where('status', 'confirmed')->count(),
            'pending' => $organization->bookings()->where('status', 'pending')->count(),
            'cancelled' => $organization->bookings()->where('status', 'cancelled')->count(),
        ];

        $events = $organization->events;

        $bookings = $organization->bookings()
            ->with(['event', 'user'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($request->event, function ($query, $eventId) {
                $query->where('event_id', $eventId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->date_from, function ($query, $date) {
                $query->whereDate('booked_at_date', '>=', $date);
            })
            ->latest('booked_at_date')
            ->paginate(20);

        return view('organization.bookings.index', compact('bookings', 'stats', 'events'));
    }

    /**
     * Display the specified booking
     */
    public function show(Booking $booking)
    {
        $this->authorize('view', $booking);

        $booking->load(['event', 'user', 'payment']);

        return view('organization.bookings.show', compact('booking'));
    }

    /**
     * Confirm a booking
     */
    public function confirm(Booking $booking)
    {
        $this->authorize('update', $booking);

        $booking->update(['status' => 'confirmed']);

        // Dispatch notification job
        // dispatch(new SendBookingConfirmation($booking));

        return back()->with('success', 'Booking confirmed successfully!');
    }

    /**
     * Cancel a booking
     */
    public function cancel(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $booking->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['reason'] ?? null,
            'cancelled_at' => now(),
        ]);

        // Handle refund if applicable
        if ($booking->payment_status === 'completed') {
            // dispatch(new ProcessRefund($booking));
        }

        return back()->with('success', 'Booking cancelled successfully!');
    }

    /**
     * Reschedule a booking
     */
    public function reschedule(Request $request, Booking $booking)
    {
        $this->authorize('update', $booking);

        $validated = $request->validate([
            'booked_at_date' => 'required|date|after:today',
            'booked_at_time' => 'required|date_format:H:i',
        ]);

        $booking->update($validated);

        // Dispatch notification
        // dispatch(new SendBookingRescheduled($booking));

        return back()->with('success', 'Booking rescheduled successfully!');
    }
}
