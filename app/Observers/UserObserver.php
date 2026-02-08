<?php

namespace App\Observers;

use App\Models\User;
use App\Services\UsageTrackingService;
use Illuminate\Support\Facades\Log;

/**
 * UserObserver
 *
 * Tracks team member addition for usage limits
 */
class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Skip if app is not fully booted or in console without organization context
        if (!app()->isBooted() || (app()->runningInConsole() && !app()->has('currentOrganization'))) {
            return;
        }

        try {
            // Only track if user belongs to an organization (not super-admin)
            if ($user->organization) {
                app(UsageTrackingService::class)->recordUsage(
                    $user->organization,
                    'team_members_added',
                    1
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to track user creation', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Skip if app is not fully booted or in console without organization context
        if (!app()->isBooted() || (app()->runningInConsole() && !app()->has('currentOrganization'))) {
            return;
        }

        try {
            if ($user->organization && $user->organization->subscription) {
                // Decrement the team members used counter
                $user->organization->subscription->decrement('team_members_used');
            }
        } catch (\Exception $e) {
            Log::error('Failed to track user deletion', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
