<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Event;
use App\Models\Booking;
use App\Services\PaymentGatewayFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    protected $paymentGatewayFactory;

    public function __construct(PaymentGatewayFactory $paymentGatewayFactory)
    {
        $this->paymentGatewayFactory = $paymentGatewayFactory;
    }

    /**
     * Show booking form
     */
    public function create(Organization $organization, Event $event)
    {
        // Ensure event belongs to organization and is active
        if ($event->organization_id !== $organization->id || $event->status !== 'active') {
            abort(404);
        }

        // Check usage limits if organization has subscription
        if ($organization->activeSubscription) {
            $plan = $organization->activeSubscription->plan;

            if ($plan->max_bookings_per_month) {
                $currentBookings = $organization->bookings()
                    ->whereMonth('created_at', now()->month)
                    ->count();

                if ($currentBookings >= $plan->max_bookings_per_month) {
                    return back()->with('error', 'This organization has reached its booking limit for this month.');
                }
            }
        }

        return view('user.bookings.create', compact('organization', 'event'));
    }

    /**
     * Store a new booking
     */
    public function store(Request $request, Organization $organization, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => 'nullable|string|max:20',
            'booked_at_date' => 'required|date|after:today',
            'booked_at_time' => 'required|date_format:H:i',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Create booking
        $booking = $organization->bookings()->create([
            'event_id' => $event->id,
            'user_id' => auth()->id(),
            'booking_number' => 'BK-' . strtoupper(Str::random(8)),
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'booked_at_date' => $validated['booked_at_date'],
            'booked_at_time' => $validated['booked_at_time'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
            'amount' => $event->price,
            'payment_status' => $event->price > 0 ? 'pending' : 'not_required',
        ]);

        // If event is paid, redirect to payment
        if ($event->price > 0) {
            return redirect()->route('user.payments.create', [
                'booking' => $booking->id,
                'organization' => $organization->subdomain
            ]);
        }

        // If free event, confirm immediately
        $booking->update(['status' => 'confirmed']);

        // Dispatch notification
        // dispatch(new SendBookingConfirmation($booking));

        return redirect()->route('user.bookings.confirmation', $booking);
    }

    /**
     * Show booking confirmation
     */
    public function confirmation(Booking $booking)
    {
        return view('user.bookings.confirmation', compact('booking'));
    }

    /**
     * Show booking management page (via email link)
     */
    public function manage(Booking $booking)
    {
        // Verify access token or email-based authentication
        return view('user.bookings.manage', compact('booking'));
    }

    /**
     * Reschedule booking (guest action)
     */
    public function reschedule(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'booked_at_date' => 'required|date|after:today',
            'booked_at_time' => 'required|date_format:H:i',
        ]);

        $booking->update($validated);

        // Dispatch notification
        // dispatch(new SendBookingRescheduled($booking));

        return back()->with('success', 'Booking rescheduled successfully!');
    }

    /**
     * Cancel booking (guest action)
     */
    public function cancel(Request $request, Booking $booking)
    {
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        // Handle refund if applicable
        if ($booking->payment_status === 'completed') {
            // dispatch(new ProcessRefund($booking));
        }

        return back()->with('success', 'Booking cancelled successfully!');
    }

    /**
     * Payment gateway callback
     */
    public function paymentCallback(Request $request, $gateway)
    {
        // Handle payment success/failure callback
        return view('user.payments.callback');
    }

    /**
     * Payment gateway webhook
     */
    public function paymentWebhook(Request $request, $gateway)
    {
        // Handle payment gateway webhook for async notifications
        $gatewayService = $this->paymentGatewayFactory->make($gateway);

        // Process webhook
        // Implementation depends on gateway

        return response()->json(['status' => 'success']);
    }

    /**
     * Display user's bookings (if authenticated)
     */
    public function userBookings(Request $request)
    {
        $bookings = auth()->user()->bookings()
            ->with(['event', 'organization'])
            ->latest('booked_at_date')
            ->paginate(10);

        return view('user.bookings.list', compact('bookings'));
    }
}
