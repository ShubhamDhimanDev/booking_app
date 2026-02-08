<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified (with signature error handling).
     *
     * @param  \Illuminate\Foundation\Auth\EmailVerificationRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function __invoke(EmailVerificationRequest $request)
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                return redirect()->route('dashboard')->with('success', 'Your email is already verified.');
            }

            if ($request->user()->markEmailAsVerified()) {
                event(new Verified($request->user()));
            }

            return redirect()->route('dashboard')->with('success', 'Email verified successfully!');

        } catch (\Illuminate\Routing\Exceptions\InvalidSignatureException $e) {
            Log::warning('Email verification signature error', [
                'user_id' => $request->user()?->id,
                'url' => $request->fullUrl(),
                'error' => $e->getMessage()
            ]);

            return redirect()->route('verification.notice')->withErrors([
                'verification' => 'The verification link is invalid or expired. Please request a new verification email.'
            ]);
        } catch (\Exception $e) {
            Log::error('Email verification failed', [
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('verification.notice')->withErrors([
                'verification' => 'Email verification failed. Please try again or request a new verification email.'
            ]);
        }
    }
}
