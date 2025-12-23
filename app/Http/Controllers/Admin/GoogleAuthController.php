<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Laravel\Socialite\Facades\Socialite;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GoogleAuthController extends Controller
{
  public function googleAuth()
  {
    return view('admin.google.auth');
  }
  /**
   * Google auth redirect
   *
   * @return void
   */
  public function googleAuthRedirect()
  {
    return Socialite::driver('google')
      ->scopes([
        'email',
        'https://www.googleapis.com/auth/calendar',
        'https://www.googleapis.com/auth/calendar.events'
      ])
      ->with(["access_type" => "offline", "prompt" => "consent select_account"])
      ->redirect();
  }


  /**
   * Google auth callback
   *
   * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
   */
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
                return redirect()->route('admin.login')->with([
                    'alert_type' => 'error',
                    'alert_message' => $msg
                ]);
            }
        }

        $email = $socialUser->getEmail();
        if (! $email) {
            return redirect()->route('admin.login')->with([
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
                $user->setGoogleAuthMetadata(
                    $socialUser->getId() ?: null,
                    $socialUser->token ?? null,
                    $socialUser->refreshToken ?? null,
                    $socialUser->expiresIn ?? null
                );
            } catch (Exception $e) {
                // non-fatal, continue
            }
        }

        // Ensure at least 'team-member' role exists and is assigned if user has no roles
        Role::firstOrCreate(['name' => 'team-member']);
        if ($user->roles->isEmpty()) {
            $user->assignRole('team-member');
        }

        auth()->login($user, true);

        return redirect()->route('admin.dashboard');
  }
}
