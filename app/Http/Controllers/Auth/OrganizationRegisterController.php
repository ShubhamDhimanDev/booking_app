<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Contracts\View\View;

class OrganizationRegisterController extends Controller
{
    /**
     * Display the organization registration view.
     *
     * @return View
     */
    public function create()
    {
        return view('auth.organization-register');
    }

    /**
     * Handle an incoming organization registration request.
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
            'organization_name' => 'required|string|max:255',
            'organization_website' => 'nullable|url|max:255',
            'organization_description' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->input('phone'),
                'password' => Hash::make($request->password),
            ]);

            // Create organization
            $orgName = $request->organization_name;
            $slug = Str::slug($orgName);

            // Ensure unique slug
            $originalSlug = $slug;
            $counter = 1;
            while (Organization::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $organization = Organization::create([
                'name' => $orgName,
                'slug' => $slug,
                'owner_id' => $user->id,
                'contact_email' => $user->email,
                'contact_phone' => $user->phone,
                'website' => $request->input('organization_website'),
                'description' => $request->input('organization_description'),
                'status' => 'active',
                'timezone' => config('app.timezone', 'UTC'),
                'currency' => 'USD',
                'locale' => 'en',
                'trial_ends_at' => now()->addDays(14), // 14-day trial
            ]);

            // Assign organization to user
            $user->organization_id = $organization->id;
            $user->save();

            // Assign org-owner role
            $user->assignRole('org-owner');

            DB::commit();

            event(new Registered($user));

            Auth::login($user);

            // Redirect to dashboard with welcome message
            return redirect()->route('dashboard')->with('success', 'Welcome! Your 14-day free trial has started.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Organization registration failed: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'registration' => 'Registration failed. Please try again.'
            ])->withInput($request->except('password', 'password_confirmation'));
        }
    }
}
