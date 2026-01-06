<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'attendee_name' => 'required|string|max:255',
            'attendee_email' => 'required|email|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $event = Event::findOrFail($validated['event_id']);

        // Check if the slot is available for this specific event
        $existingBooking = Booking::where('event_id', $event->id)
            ->where('booking_date', $validated['booking_date'])
            ->where('status', 'confirmed')
            ->where(function ($query) use ($validated) {
                $query->where(function ($q) use ($validated) {
                    $q->where('start_time', '<', $validated['end_time'])
                      ->where('end_time', '>', $validated['start_time']);
                });
            })
            ->exists();

        if ($existingBooking) {
            return response()->json([
                'message' => 'This time slot is already booked for this event.',
                'error' => 'slot_unavailable'
            ], 422);
        }

        // Check if event owner already has a confirmed booking at the same time across all their events
        $ownerDoubleBooking = Booking::whereHas('event', function ($query) use ($event) {
                $query->where('user_id', $event->user_id);
            })
            ->where('booking_date', $validated['booking_date'])
            ->where('status', 'confirmed')
            ->where(function ($query) use ($validated) {
                $query->where(function ($q) use ($validated) {
                    $q->where('start_time', '<', $validated['end_time'])
                      ->where('end_time', '>', $validated['start_time']);
                });
            })
            ->exists();

        if ($ownerDoubleBooking) {
            return response()->json([
                'message' => 'The event owner already has a confirmed booking at this time for another event.',
                'error' => 'owner_double_booking'
            ], 422);
        }

        // Create the booking
        $booking = Booking::create([
            'event_id' => $event->id,
            'booking_date' => $validated['booking_date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'attendee_name' => $validated['attendee_name'],
            'attendee_email' => $validated['attendee_email'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'confirmed',
            'created_by' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Booking created successfully.',
            'booking' => $booking->load('event'),
        ], 201);
    }

    public function index(Request $request)
    {
        $query = Booking::with('event');

        if ($request->has('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('booking_date')) {
            $query->where('booking_date', $request->booking_date);
        }

        $bookings = $query->orderBy('booking_date', 'desc')
                         ->orderBy('start_time', 'desc')
                         ->paginate(15);

        return response()->json($bookings);
    }

    public function show($id)
    {
        $booking = Booking::with('event')->findOrFail($id);

        return response()->json($booking);
    }

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'booking_date' => 'sometimes|required|date|after_or_equal:today',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'attendee_name' => 'sometimes|required|string|max:255',
            'attendee_email' => 'sometimes|required|email|max:255',
            'notes' => 'nullable|string|max:1000',
            'status' => 'sometimes|required|in:confirmed,cancelled,pending',
        ]);

        // If updating time/date, check for conflicts
        if (isset($validated['booking_date']) || isset($validated['start_time']) || isset($validated['end_time'])) {
            $checkDate = $validated['booking_date'] ?? $booking->booking_date;
            $checkStartTime = $validated['start_time'] ?? $booking->start_time;
            $checkEndTime = $validated['end_time'] ?? $booking->end_time;
            $checkStatus = $validated['status'] ?? $booking->status;

            if ($checkStatus === 'confirmed') {
                // Check for slot availability for this specific event
                $existingBooking = Booking::where('event_id', $booking->event_id)
                    ->where('id', '!=', $booking->id)
                    ->where('booking_date', $checkDate)
                    ->where('status', 'confirmed')
                    ->where(function ($query) use ($checkStartTime, $checkEndTime) {
                        $query->where(function ($q) use ($checkStartTime, $checkEndTime) {
                            $q->where('start_time', '<', $checkEndTime)
                              ->where('end_time', '>', $checkStartTime);
                        });
                    })
                    ->exists();

                if ($existingBooking) {
                    return response()->json([
                        'message' => 'This time slot is already booked for this event.',
                        'error' => 'slot_unavailable'
                    ], 422);
                }

                // Check if event owner already has a confirmed booking at the same time across all their events
                $event = $booking->event;
                $ownerDoubleBooking = Booking::whereHas('event', function ($query) use ($event) {
                        $query->where('user_id', $event->user_id);
                    })
                    ->where('id', '!=', $booking->id)
                    ->where('booking_date', $checkDate)
                    ->where('status', 'confirmed')
                    ->where(function ($query) use ($checkStartTime, $checkEndTime) {
                        $query->where(function ($q) use ($checkStartTime, $checkEndTime) {
                            $q->where('start_time', '<', $checkEndTime)
                              ->where('end_time', '>', $checkStartTime);
                        });
                    })
                    ->exists();

                if ($ownerDoubleBooking) {
                    return response()->json([
                        'message' => 'The event owner already has a confirmed booking at this time for another event.',
                        'error' => 'owner_double_booking'
                    ], 422);
                }
            }
        }

        $booking->update($validated);

        return response()->json([
            'message' => 'Booking updated successfully.',
            'booking' => $booking->fresh()->load('event'),
        ]);
    }

    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return response()->json([
            'message' => 'Booking deleted successfully.',
        ]);
    }

    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Booking cancelled successfully.',
            'booking' => $booking->fresh()->load('event'),
        ]);
    }
}
