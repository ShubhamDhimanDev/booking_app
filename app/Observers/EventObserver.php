<?php

namespace App\Observers;

use App\Models\Event;
use App\Services\UsageTrackingService;
use Illuminate\Support\Facades\Log;

/**
 * EventObserver
 *
 * Tracks event creation for usage limits
 */
class EventObserver
{
    /**
     * Handle the Event "created" event.
     */
    public function created(Event $event): void
    {
        // Skip if app is not fully booted or in console without organization context
        if (!app()->isBooted() || (app()->runningInConsole() && !app()->has('currentOrganization'))) {
            return;
        }

        try {
            if ($event->organization) {
                app(UsageTrackingService::class)->recordUsage(
                    $event->organization,
                    'events_created',
                    1
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to track event creation', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Event "deleted" event.
     */
    public function deleted(Event $event): void
    {
        // Skip if app is not fully booted or in console without organization context
        if (!app()->isBooted() || (app()->runningInConsole() && !app()->has('currentOrganization'))) {
            return;
        }

        try {
            if ($event->organization && $event->organization->subscription) {
                // Decrement the events used counter
                $event->organization->subscription->decrement('events_used');
            }
        } catch (\Exception $e) {
            Log::error('Failed to track event deletion', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
