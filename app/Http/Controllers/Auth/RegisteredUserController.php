<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default 'user' role if the roles package is available
        if (method_exists($user, 'assignRole')) {
            try {
                $user->assignRole('user');
            } catch (\Exception $e) {
                // log and continue if role assignment fails for any reason
                \Illuminate\Support\Facades\Log::warning('Failed to assign role to new user: ' . $e->getMessage());
            }
        }

        event(new Registered($user));

        Auth::login($user);

        // After registration redirect based on role (admin vs regular user)
        $isAdmin = method_exists($user, 'hasRole') && $user->hasRole('admin');
        $default = $isAdmin ? '/bookings' : '/user/bookings';

        return redirect($default);
    }
}
