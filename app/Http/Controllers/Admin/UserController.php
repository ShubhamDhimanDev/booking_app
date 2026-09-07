<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('roles');

        // Search by name, username, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();
        $roles = Role::pluck('name');

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'nullable|string'
        ]);

        // Determine allowed roles for the currently authenticated user
        $allowedRoles = Role::pluck('name')->toArray();
        if (!auth()->user()->hasRole('owner')) {
            // Admins may only assign team-member and user
            $allowedRoles = array_intersect($allowedRoles, ['team-member', 'user']);
        }

        $requestedRole = $data['role'] ?? null;
        if ($requestedRole && !in_array($requestedRole, $allowedRoles)) {
            return back()->withErrors(['role' => 'You are not allowed to assign the selected role.'])->withInput();
        }

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'] ?? null,
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);

        if (!empty($data['role'])) {
            $user->assignRole($data['role']);
        }

        return redirect()->route('admin.users.index')->with([
            'alert_type' => 'success',
            'alert_message' => 'User created successfully'
        ]);
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
            'role' => 'nullable|string'
        ]);

        $user->name = $data['name'];
        $user->username = $data['username'] ?? null;
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        // Enforce role assignment limits similar to store
        $allowedRoles = Role::pluck('name')->toArray();
        if (!auth()->user()->hasRole('owner')) {
            $allowedRoles = array_intersect($allowedRoles, ['team-member', 'user']);
        }

        $requestedRole = $data['role'] ?? null;
        if ($requestedRole && !in_array($requestedRole, $allowedRoles)) {
            return back()->withErrors(['role' => 'You are not allowed to assign the selected role.'])->withInput();
        }

        if (!empty($data['role'])) {
            $user->syncRoles([$data['role']]);
        }

        return redirect()->route('admin.users.index')->with([
            'alert_type' => 'success',
            'alert_message' => 'User updated successfully'
        ]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with([
            'alert_type' => 'success',
            'alert_message' => 'User removed successfully'
        ]);
    }
}
