<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Contracts\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     *
     * @return View
     */
    public function create()
    {
        return view('auth.register');
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
            'phone' => 'nullable|string|max:20',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        try {
            // Create user as guest
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->password),
            ]);

            // Assign guest role
            $user->assignRole('guest');

            event(new Registered($user));

            Auth::login($user);

            // Redirect to smart dashboard route
            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Registration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'registration' => 'Registration failed. Please try again.'
            ])->withInput($request->except('password', 'password_confirmation'));
        }
    }
}
