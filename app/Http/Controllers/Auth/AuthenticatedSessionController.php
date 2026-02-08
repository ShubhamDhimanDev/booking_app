<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        if (auth()->check()) {
            // Redirect to smart dashboard route
            return redirect()->route('dashboard');
        }

        return view('auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request (SaaS-ready).
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Check if user's organization is active (if they belong to one)
        $user = $request->user();
        if ($user->organization_id) {
            $organization = $user->organization;
            if ($organization && $organization->status !== 'active') {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your organization account is ' . $organization->status . '. Please contact support.'
                ]);
            }
        }

        // Redirect to smart dashboard route (handles role-based routing)
        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
