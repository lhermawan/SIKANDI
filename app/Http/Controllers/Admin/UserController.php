<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with(['organization', 'roles'])->latest()->paginate(15);
        $roles = Role::all();
        $organizations = Organization::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'roles', 'organizations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'organization_id' => 'nullable|exists:organizations,id',
            'phone' => 'nullable|string|max:50',
            'nip' => 'nullable|string|max:50',
            'role' => 'required|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'organization_id' => $validated['organization_id'],
            'phone' => $validated['phone'],
            'nip' => $validated['nip'],
            'is_active' => true,
        ]);

        $user->assignRole($validated['role']);

        return back()->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }
}
