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
     * @return \Inertia\Response
     */
    public function create()
    {
        if (auth()->check()) {
            // redirect based on role if already logged in
            $user = auth()->user();
            $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('admin');
            return redirect($isAdmin ? '/bookings' : '/user/bookings');
        }

        return view('admin.auth.login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

            // Redirect based on role: admin -> /bookings, other users -> /user/bookings
            $isAdmin = $request->user() && method_exists($request->user(), 'hasRole') && $request->user()->hasRole('admin');
            $default = $isAdmin ? '/bookings' : '/user/bookings';

            // Respect intended URL where safe: only allow redirecting to admin paths when user is admin.
            $intended = $request->session()->pull('url.intended');
            if ($intended) {
                $path = parse_url($intended, PHP_URL_PATH) ?: '';
                // If intended path is under /bookings but current user is not admin, ignore it
                if (! $isAdmin && str_starts_with($path, '/bookings')) {
                    return redirect($default);
                }

                return redirect()->to($intended);
            }

            return redirect($default);
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
