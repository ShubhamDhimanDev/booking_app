<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of all events (platform-wide)
     */
    public function index(Request $request)
    {
        $events = Event::with(['organization', 'user'])
            ->withCount('bookings')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->when($request->organization, function ($query, $orgId) {
                $query->where('organization_id', $orgId);
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(20);

        return view('super-admin.events.index', compact('events'));
    }

    /**
     * Display the specified event
     */
    public function show(Event $event)
    {
        $event->load(['organization', 'user', 'bookings']);

        return view('super-admin.events.show', compact('event'));
    }

    /**
     * Remove the specified event
     */
    public function destroy(Event $event)
    {
        $event->delete();

        return redirect()
            ->route('super-admin.events.index')
            ->with('success', 'Event deleted successfully!');
    }
}
