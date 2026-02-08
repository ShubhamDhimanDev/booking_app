<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display a listing of events
     */
    public function index(Request $request)
    {
        $organization = auth()->user()->organization;

        $events = $organization->events()
            ->withCount('bookings')
            ->when($request->search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->type, function ($query, $type) {
                $query->where('type', $type);
            })
            ->latest()
            ->paginate(15);

        return view('organization.events.index', compact('events'));
    }

    /**
     * Show the form for creating a new event
     */
    public function create()
    {
        $this->authorize('create', Event::class);
        return view('organization.events.create');
    }

    /**
     * Store a newly created event
     */
    public function store(Request $request)
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:one_on_one,group',
            'duration' => 'required|integer|min:15',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
            // Add other fields as needed
        ]);

        $event = auth()->user()->organization->events()->create($validated);

        return redirect()
            ->route('organization.events.show', $event)
            ->with('success', 'Event created successfully!');
    }

    /**
     * Display the specified event
     */
    public function show(Event $event)
    {
        $this->authorize('view', $event);

        $event->load(['bookings' => function ($query) {
            $query->latest()->take(10);
        }]);

        return view('organization.events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified event
     */
    public function edit(Event $event)
    {
        $this->authorize('update', $event);
        return view('organization.events.edit', compact('event'));
    }

    /**
     * Update the specified event
     */
    public function update(Request $request, Event $event)
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:one_on_one,group',
            'duration' => 'required|integer|min:15',
            'price' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $event->update($validated);

        return redirect()
            ->route('organization.events.show', $event)
            ->with('success', 'Event updated successfully!');
    }

    /**
     * Remove the specified event
     */
    public function destroy(Event $event)
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()
            ->route('organization.events.index')
            ->with('success', 'Event deleted successfully!');
    }

    /**
     * Duplicate an event
     */
    public function duplicate(Event $event)
    {
        $this->authorize('create', Event::class);

        $newEvent = $event->replicate();
        $newEvent->title = $event->title . ' (Copy)';
        $newEvent->status = 'inactive';
        $newEvent->save();

        return redirect()
            ->route('organization.events.edit', $newEvent)
            ->with('success', 'Event duplicated successfully!');
    }

    /**
     * Toggle event status
     */
    public function toggleStatus(Event $event)
    {
        $this->authorize('update', $event);

        $event->update([
            'status' => $event->status === 'active' ? 'inactive' : 'active'
        ]);

        return back()->with('success', 'Event status updated!');
    }
}
