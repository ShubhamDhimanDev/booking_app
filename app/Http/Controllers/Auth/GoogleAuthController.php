<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleAuthController extends Controller
{
    public function googleAuth()
    {
        return Socialite::driver('google')->redirect();
    }

    public function googleAuthRedirect()
    {
        return $this->googleAuth();
    }

    public function googleAuthCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = \App\Models\User::where('email', $googleUser->getEmail())->first();

            if (! $user) {
                // Optional: create a user or block
                return redirect()->route('login')->withErrors([
                    'login' => 'No account is linked to this Google email.',
                ]);
            }

            Auth::login($user, true);
            $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('admin');
            return redirect($isAdmin ? '/bookings' : '/user/bookings');
        } catch (Exception $e) {
            Log::error('Google login failed', ['error' => $e->getMessage()]);
            return redirect()->route('login')->withErrors([
                'login' => 'Google authentication failed. Please try again.',
            ]);
        }
    }
}
