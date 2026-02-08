<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations
     */
    public function index(Request $request)
    {
        $stats = [
            'total' => Organization::count(),
            'active' => Organization::where('status', 'active')->count(),
            'subscribed' => Organization::whereHas('activeSubscription')->count(),
            'this_month' => Organization::whereMonth('created_at', now()->month)->count(),
        ];

        $organizations = Organization::with(['owner', 'activeSubscription.plan'])
            ->withCount(['users', 'events'])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%");
            })
            ->when($request->status, function ($query, $status) {
                $query->where('status', $status);
            })
            ->when($request->has_subscription === '1', function ($query) {
                $query->whereHas('activeSubscription');
            })
            ->when($request->has_subscription === '0', function ($query) {
                $query->whereDoesntHave('activeSubscription');
            })
            ->latest()
            ->paginate(20);

        return view('super-admin.organizations.index', compact('organizations', 'stats'));
    }

    /**
     * Show the form for creating a new organization
     */
    public function create()
    {
        return view('super-admin.organizations.create');
    }

    /**
     * Store a newly created organization
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|unique:organizations,subdomain',
            'owner_name' => 'required|string|max:255',
            'owner_email' => 'required|email|unique:users,email',
            'website' => 'nullable|url|max:255',
        ]);

        // Create organization
        $organization = Organization::create([
            'name' => $validated['name'],
            'subdomain' => $validated['subdomain'],
            'website' => $validated['website'] ?? null,
            'status' => 'active',
        ]);

        // Create owner user
        $owner = User::create([
            'name' => $validated['owner_name'],
            'email' => $validated['owner_email'],
            'password' => Hash::make('password'), // Temporary password
            'organization_id' => $organization->id,
            'email_verified_at' => now(),
        ]);

        $owner->assignRole('org-owner');

        // Set organization owner
        $organization->update(['owner_id' => $owner->id]);

        return redirect()
            ->route('super-admin.organizations.show', $organization)
            ->with('success', 'Organization created successfully!');
    }

    /**
     * Display the specified organization
     */
    public function show(Organization $organization)
    {
        $organization->load([
            'owner',
            'users',
            'events',
            'subscriptions',
            'activeSubscription.plan'
        ]);

        $stats = [
            'total_users' => $organization->users()->count(),
            'total_events' => $organization->events()->count(),
            'total_bookings' => $organization->bookings()->count(),
            'total_revenue' => $organization->payments()->where('status', 'completed')->sum('amount'),
        ];

        return view('super-admin.organizations.show', compact('organization', 'stats'));
    }

    /**
     * Show the form for editing the specified organization
     */
    public function edit(Organization $organization)
    {
        return view('super-admin.organizations.edit', compact('organization'));
    }

    /**
     * Update the specified organization
     */
    public function update(Request $request, Organization $organization)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:63|unique:organizations,subdomain,' . $organization->id,
            'website' => 'nullable|url|max:255',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $organization->update($validated);

        return redirect()
            ->route('super-admin.organizations.show', $organization)
            ->with('success', 'Organization updated successfully!');
    }

    /**
     * Remove the specified organization
     */
    public function destroy(Organization $organization)
    {
        $organization->delete();

        return redirect()
            ->route('super-admin.organizations.index')
            ->with('success', 'Organization deleted successfully!');
    }

    /**
     * Suspend organization
     */
    public function suspend(Organization $organization)
    {
        $organization->update(['status' => 'suspended']);

        return back()->with('success', 'Organization suspended successfully!');
    }

    /**
     * Activate organization
     */
    public function activate(Organization $organization)
    {
        $organization->update(['status' => 'active']);

        return back()->with('success', 'Organization activated successfully!');
    }
}
