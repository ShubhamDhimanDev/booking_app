<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $users = User::with(['organization', 'roles'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->organization, function ($query, $orgId) {
                $query->where('organization_id', $orgId);
            })
            ->when($request->role, function ($query, $role) {
                $query->role($role);
            })
            ->latest()
            ->paginate(20);

        $organizations = Organization::orderBy('name')->get();
        $roles = Role::all();

        return view('super-admin.users.index', compact('users', 'organizations', 'roles'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $organizations = Organization::orderBy('name')->get();
        $roles = Role::all();

        return view('super-admin.users.create', compact('organizations', 'roles'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'organization_id' => 'nullable|exists:organizations,id',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'organization_id' => $validated['organization_id'] ?? null,
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        return redirect()
            ->route('super-admin.users.show', $user)
            ->with('success', 'User created successfully!');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['organization', 'roles', 'events', 'bookings']);

        return view('super-admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $organizations = Organization::orderBy('name')->get();
        $roles = Role::all();

        return view('super-admin.users.edit', compact('user', 'organizations', 'roles'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'organization_id' => 'nullable|exists:organizations,id',
            'role' => 'required|exists:roles,name',
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'organization_id' => $validated['organization_id'] ?? null,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $user->syncRoles([$validated['role']]);

        return redirect()
            ->route('super-admin.users.show', $user)
            ->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting super admins
        if ($user->hasRole('super-admin')) {
            return back()->with('error', 'Cannot delete super admin users!');
        }

        $user->delete();

        return redirect()
            ->route('super-admin.users.index')
            ->with('success', 'User deleted successfully!');
    }

    /**
     * Impersonate user (login as user)
     */
    public function impersonate(User $user)
    {
        session(['impersonate_user_id' => $user->id]);
        session(['original_user_id' => auth()->id()]);

        auth()->loginUsingId($user->id);

        return redirect()->route('dashboard');
    }
}
