<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Exception;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class GoogleAuthController extends Controller
{
    /**
     * Redirect to Google OAuth with proper scopes for calendar access
     * 
     * CRITICAL: Using 'prompt' => 'consent' ensures we ALWAYS get a refresh_token
     * This prevents token expiration issues and re-authentication loops
     */
    public function googleAuth()
    {
        return Socialite::driver('google')
            ->scopes([
                'email',
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/calendar.events'
            ])
            ->with([
                'access_type' => 'offline',      // Required to get refresh token
                'prompt' => 'consent',           // Force consent screen to always get refresh token
            ])
            ->redirect();
    }

    public function googleAuthRedirect()
    {
        return $this->googleAuth();
    }

    public function googleAuthCallback()
    {
        $socialUser = null;
        try {
            // First try normal flow (uses session state)
            $socialUser = Socialite::driver('google')->user();
        } catch (Exception $ex) {
            // If it fails often due to state mismatch, retry using stateless (no session state)
            try {
                $socialUser = Socialite::driver('google')->stateless()->user();
            } catch (Exception $ex2) {
                $msg = 'SSO failed. Please try again.';
                if (config('app.debug')) {
                    $msg = $ex2->getMessage();
                }
                return redirect()->route('login')->with([
                    'alert_type' => 'error',
                    'alert_message' => $msg
                ]);
            }
        }

        $email = $socialUser->getEmail();
        if (! $email) {
            return redirect()->route('login')->with([
                'alert_type' => 'error',
                'alert_message' => 'No email returned by provider.'
            ]);
        }

        // create or fetch user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $socialUser->getName() ?: $email,
                'username' => Str::slug(explode('@', $email)[0]) . '-' . Str::random(4),
                'password' => Hash::make(Str::random(40))
            ]
        );

        // persist Google auth metadata if available
        if (method_exists($user, 'setGoogleAuthMetadata')) {
            try {
                $refreshToken = $socialUser->refreshToken;
                
                // CRITICAL: If no refresh token, keep existing one (for re-auth scenarios)
                // New OAuth flow with 'consent' should always provide refresh token
                if (!$refreshToken && $user->google_auth_metadata && isset($user->google_auth_metadata['refresh_token'])) {
                    $refreshToken = $user->google_auth_metadata['refresh_token'];
                }
                
                if (!$refreshToken) {
                    // This should not happen with 'prompt' => 'consent', but handle gracefully
                    \Illuminate\Support\Facades\Log::warning('No refresh token received from Google OAuth', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                }
                
                $user->setGoogleAuthMetadata(
                    $socialUser->getId() ?: null,
                    $socialUser->token ?? null,
                    $refreshToken,
                    $socialUser->expiresIn ?? 3600
                );
            } catch (Exception $e) {
                // non-fatal, continue
                \Illuminate\Support\Facades\Log::error('Failed to set Google auth metadata', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Ensure at least 'team-member' role exists and is assigned if user has no roles
        Role::firstOrCreate(['name' => 'user']);
        if ($user->roles->isEmpty()) {
            $user->assignRole('user');
        }

        auth()->login($user, true);

        return redirect()->route('admin.dashboard');
    }
}
