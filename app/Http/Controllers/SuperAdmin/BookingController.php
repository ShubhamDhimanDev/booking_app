<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of all bookings (platform-wide)
     */
    public function index(Request $request)
    {
        $bookings = Booking::with(['event', 'organization', 'user'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
                });
            })
            ->when($request->organization, function ($query, $orgId) {
                $query->where('organization_id', $orgId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->payment_status, function ($query, $status) {
                $query->where('payment_status', $status);
            })
            ->latest('booked_at_date')
            ->paginate(20);

        return view('super-admin.bookings.index', compact('bookings'));
    }

    /**
     * Display the specified booking
     */
    public function show(Booking $booking)
    {
        $booking->load(['event', 'organization', 'user', 'payment']);

        return view('super-admin.bookings.show', compact('booking'));
    }
}
