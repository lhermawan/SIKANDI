<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Password rule yang kuat: min 12 karakter, huruf besar, angka, simbol.
     */
    private function strongPasswordRule(bool $required = true): array
    {
        $rule = Password::min(12)
            ->mixedCase()
            ->numbers()
            ->symbols()
            ->uncompromised();

        return $required ? ['required', 'confirmed', $rule] : ['nullable', 'confirmed', $rule];
    }

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
            'name'            => 'required|string|max:255',
            'username'        => ['required', 'string', 'max:50', 'unique:users,username', 'regex:/^[a-zA-Z0-9\._\-]+$/'],
            'email'           => 'required|email:rfc,dns|max:255|unique:users,email',
            'password'        => $this->strongPasswordRule(true),
            'organization_id' => 'nullable|exists:organizations,id',
            'phone'           => 'nullable|string|max:50',
            'nip'             => 'nullable|string|max:50',
            'role'            => 'required|exists:roles,name',
        ], [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, strip, dan garis bawah tanpa spasi.'
        ]);

        $user = User::create([
            'name'            => $validated['name'],
            'username'        => $validated['username'],
            'email'           => $validated['email'],
            'password'        => Hash::make($validated['password']),
            'organization_id' => $validated['organization_id'] ?? null,
            'phone'           => $validated['phone'] ?? null,
            'nip'             => $validated['nip'] ?? null,
            'is_active'       => true,
        ]);

        $user->assignRole($validated['role']);

        return back()->with('success', "Pengguna {$user->name} berhasil ditambahkan.");
    }

    /**
     * Kembalikan data user sebagai JSON untuk diisi ke modal edit.
     */
    public function edit(User $user): JsonResponse
    {
        return response()->json([
            'id'              => $user->id,
            'name'            => $user->name,
            'username'        => $user->username,
            'email'           => $user->email,
            'phone'           => $user->phone,
            'nip'             => $user->nip,
            'organization_id' => $user->organization_id,
            'is_active'       => $user->is_active,
            'role'            => $user->roles->first()?->name,
            'locked_until'    => $user->locked_until?->toIso8601String(),
            'failed_login_count' => $user->failed_login_count ?? 0,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'username'        => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9\._\-]+$/', 'unique:users,username,' . $user->id],
            'email'           => 'required|email:rfc|max:255|unique:users,email,' . $user->id,
            'password'        => $this->strongPasswordRule(false), // nullable saat edit
            'organization_id' => 'nullable|exists:organizations,id',
            'phone'           => 'nullable|string|max:50',
            'nip'             => 'nullable|string|max:50',
            'role'            => 'required|exists:roles,name',
        ], [
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik, strip, dan garis bawah tanpa spasi.'
        ]);

        $updateData = [
            'name'            => $validated['name'],
            'username'        => $validated['username'],
            'email'           => $validated['email'],
            'organization_id' => $validated['organization_id'] ?? null,
            'phone'           => $validated['phone'] ?? null,
            'nip'             => $validated['nip'] ?? null,
        ];

        // Hanya update password jika field diisi
        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
            // Reset lockout saat password diganti admin
            $updateData['failed_login_count'] = 0;
            $updateData['locked_until'] = null;
        }

        $user->update($updateData);
        $user->syncRoles([$validated['role']]);

        return back()->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user): RedirectResponse
    {
        // Cegah hapus diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $name = $user->name;
        $user->delete(); // SoftDelete

        return back()->with('success', "Pengguna {$name} berhasil dihapus.");
    }

    public function toggleActive(User $user): RedirectResponse
    {
        // Cegah menonaktifkan diri sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update([
            'is_active'       => !$user->is_active,
            'locked_until'    => null,   // Buka kunci saat admin mengaktifkan
            'failed_login_count' => 0,
        ]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun {$user->name} berhasil {$status}.");
    }

    /**
     * Buka kunci akun yang sedang terkunci (lockout reset).
     */
    public function unlock(User $user): RedirectResponse
    {
        $user->update([
            'failed_login_count' => 0,
            'locked_until'       => null,
        ]);

        return back()->with('success', "Kunci akun {$user->name} berhasil dibuka.");
    }
}
