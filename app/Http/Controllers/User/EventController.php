<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    /**
     * Display available events for an organization
     */
    public function index(Organization $organization)
    {
        $events = $organization->events()
            ->where('status', 'active')
            ->orderBy('title')
            ->get();

        return view('user.events.index', compact('organization', 'events'));
    }

    /**
     * Display event details and booking form
     */
    public function show(Organization $organization, Event $event)
    {
        // Ensure event belongs to this organization
        if ($event->organization_id !== $organization->id) {
            abort(404);
        }

        if ($event->status !== 'active') {
            abort(404, 'This event is no longer available.');
        }

        return view('user.events.show', compact('organization', 'event'));
    }
}
