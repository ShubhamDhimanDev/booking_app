<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LinkedWithGoogleMiddleware
{
  /**
   * Handle an incoming request.
   * Ensures user has valid Google authentication and refreshes token if needed
   *
   * @param  \Illuminate\Http\Request  $request
   * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
   * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
   */
  public function handle(Request $request, Closure $next)
  {
    $user = $request->user();


    // Check if user has Google authentication
    if (!$user || !$user->hasGoogleAuth()) {
      return redirect()->route('admin.google.auth')->with([
        'alert_type' => 'warning',
        'alert_message' => 'Please link your Google account to continue.'
      ]);
    }

    // Check if token is expired or will expire soon, and proactively refresh
    if ($user->isGoogleTokenExpired()) {
      try {
        // This will automatically refresh the token
        $user->googleCalendar()->getAuthenticatedClient($user);
      } catch (\Exception $e) {
        Log::error('Failed to refresh Google token in middleware', [
          'user_id' => $user->id,
          'error' => $e->getMessage()
        ]);

        // If refresh fails, redirect to re-authenticate
        return redirect()->route('admin.google.auth')->with([
          'alert_type' => 'error',
          'alert_message' => 'Your Google session has expired. Please reconnect your account.'
        ]);
      }
    }

    return $next($request);
  }
}

