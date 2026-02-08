<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    /**
     * Display organization settings
     */
    public function index()
    {
        $organization = auth()->user()->organization;

        return view('organization.settings.index', compact('organization'));
    }

    /**
     * Update general settings
     */
    public function updateGeneral(Request $request)
    {
        $this->authorize('update', auth()->user()->organization);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|unique:organizations,subdomain,' . auth()->user()->organization_id,
            'website' => 'nullable|url|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        auth()->user()->organization->update($validated);

        return back()->with('success', 'General settings updated successfully!');
    }

    /**
     * Update branding settings
     */
    public function updateBranding(Request $request)
    {
        $this->authorize('update', auth()->user()->organization);

        $validated = $request->validate([
            'logo' => 'nullable|image|max:2048',
            'primary_color' => 'nullable|string|max:7',
            'secondary_color' => 'nullable|string|max:7',
        ]);

        $organization = auth()->user()->organization;

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($organization->logo) {
                Storage::disk('public')->delete($organization->logo);
            }

            $validated['logo'] = $request->file('logo')->store('organizations/logos', 'public');
        }

        $organization->update($validated);

        return back()->with('success', 'Branding settings updated successfully!');
    }

    /**
     * Update notification settings
     */
    public function updateNotifications(Request $request)
    {
        $validated = $request->validate([
            'email_notifications' => 'boolean',
            'booking_notifications' => 'boolean',
            'payment_notifications' => 'boolean',
        ]);

        // Update user or organization notification preferences
        auth()->user()->update([
            'notification_preferences' => $validated,
        ]);

        return back()->with('success', 'Notification settings updated successfully!');
    }

    /**
     * Delete organization
     */
    public function deleteOrganization(Request $request)
    {
        $this->authorize('delete', auth()->user()->organization);

        $request->validate([
            'confirmation' => 'required|in:DELETE',
        ]);

        $organization = auth()->user()->organization;

        // Only owner can delete
        if ($organization->owner_id !== auth()->id()) {
            return back()->with('error', 'Only the organization owner can delete the organization!');
        }

        // Delete organization and cascade
        $organization->delete();

        return redirect()->route('home')->with('success', 'Organization deleted successfully!');
    }
}
