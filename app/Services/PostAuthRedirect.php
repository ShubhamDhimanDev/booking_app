<?php

namespace App\Services;

use App\Models\User;

class PostAuthRedirect
{
    /**
     * Where a user should land immediately after authentication — login,
     * registration, password confirmation, email verification, or an
     * already-authenticated visit to a guest-only route (e.g. /login).
     *
     * Mirrors the exact role check used by the IsAdmin middleware, so a user
     * who is allowed into the admin area is also sent there right after auth
     * (admin/owner/team-member -> admin dashboard, everyone else -> their
     * bookings list).
     */
    public static function url(?User $user): string
    {
        if ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin', 'owner', 'team-member'])) {
            return route('admin.dashboard');
        }

        return route('user.bookings.index');
    }
}
