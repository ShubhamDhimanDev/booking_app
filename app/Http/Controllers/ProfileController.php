<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use App\Models\SystemSetting;
use App\Services\ThemeService;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function edit(Request $request)
    {
      return view('profile.edit');
    }

    /**
     * Update the user's profile information.
     *
     * @param  \App\Http\Requests\ProfileUpdateRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();

        $user->fill($request->only('name', 'email'));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();



        return Redirect::route('profile.edit')->with([
          'alert_type' => 'success',
          'alert_message' => 'Profile updated successfully'
        ]);
    }

    /**
     * Delete the user's account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current-password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * Toggle theme (dark/light mode) for authenticated user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleTheme(Request $request)
    {
        $request->validate([
            'dark_mode' => 'required|boolean',
        ]);

        $settings = SystemSetting::getSettings(auth()->id());
        $settings->update([
            'dark_mode' => $request->dark_mode,
        ]);

        // Clear theme cache
        ThemeService::clearCache(auth()->id());

        return response()->json([
            'success' => true,
            'dark_mode' => $request->dark_mode
        ]);
    }
}
