<?php

namespace App\Http\Controllers\Organization;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class TeamController extends Controller
{
    /**
     * Display a listing of team members
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $organization = auth()->user()->organization;

        $stats = [
            'total' => $organization->users()->count(),
            'active' => $organization->users()->where('status', 'active')->count(),
            'pending' => 0, // Implement invitation system
        ];

        $roles = Role::where('name', '!=', 'super-admin')->get();

        $members = $organization->users()
            ->withCount('events')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role, function ($query, $role) {
                $query->role($role);
            })
            ->latest()
            ->paginate(15);

        return view('organization.members.index', compact('members', 'stats', 'roles'));
    }

    /**
     * Show invite form
     */
    public function showInviteForm()
    {
        $this->authorize('create', User::class);

        $roles = Role::where('name', '!=', 'super-admin')->get();

        return view('organization.members.invite', compact('roles'));
    }

    /**
     * Invite a new team member
     */
    public function invite(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|exists:roles,name',
        ]);

        $organization = auth()->user()->organization;

        // Create user with temporary password
        $tempPassword = Str::random(16);

        $user = $organization->users()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($tempPassword),
            'organization_id' => $organization->id,
            'email_verified_at' => null,
        ]);

        // Assign role
        $user->assignRole($validated['role']);

        // Send invitation email with credentials
        // dispatch(new SendNewUserCredentials($user, $tempPassword));

        return redirect()
            ->route('organization.team.index')
            ->with('success', 'Team member invited successfully!');
    }

    /**
     * Display the specified team member
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['events', 'bookings']);

        return view('organization.members.show', compact('user'));
    }

    /**
     * Show the form for editing team member
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $roles = Role::where('name', '!=', 'super-admin')->get();

        return view('organization.members.edit', compact('user', 'roles'));
    }

    /**
     * Update team member
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|exists:roles,name',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        // Update role
        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('organization.team.show', $user)
            ->with('success', 'Team member updated successfully!');
    }

    /**
     * Remove team member
     */
    public function remove(User $user)
    {
        $this->authorize('delete', $user);

        // Prevent removing organization owner
        if ($user->id === auth()->user()->organization->owner_id) {
            return back()->with('error', 'Cannot remove organization owner!');
        }

        $user->delete();

        return redirect()
            ->route('organization.team.index')
            ->with('success', 'Team member removed successfully!');
    }
}
