<?php

namespace App\Observers;

use App\Models\Booking;
use App\Services\UsageTrackingService;
use Illuminate\Support\Facades\Log;

/**
 * BookingObserver
 *
 * Tracks booking creation for usage limits
 */
class BookingObserver
{
    /**
     * Handle the Booking "created" event.
     */
    public function created(Booking $booking): void
    {
        // Skip if app is not fully booted or in console without organization context
        if (!app()->isBooted() || (app()->runningInConsole() && !app()->has('currentOrganization'))) {
            return;
        }

        try {
            if ($booking->organization) {
                app(UsageTrackingService::class)->recordUsage(
                    $booking->organization,
                    'bookings_created',
                    1
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to track booking creation', [
                'booking_id' => $booking->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
