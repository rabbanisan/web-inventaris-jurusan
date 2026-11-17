<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserManagementController extends Controller
{
    /**
     * Display a paginated list of users.
     */
    public function index(): Response
    {
        $users = User::orderBy('name')->paginate(15);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role'     => 'required|in:admin,user',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => $data['password'], // cast 'hashed' di model yang meng-hash
        ]);

        // 'role' TIDAK fillable (lihat App\Models\User) → wajib forceFill(),
        // kalau tidak, role dibuang diam-diam dan jatuh ke default 'user'.
        // email_verified_at diisi karena akun ini dibuat admin, bukan didaftarkan sendiri.
        $user->forceFill([
            'role'              => $data['role'],
            'email_verified_at' => now(),
        ])->save();

        return redirect()->route('admin.users.index')
                         ->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'role'     => 'required|in:admin,user',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Cegah admin mengunci dirinya sendiri di luar panel admin.
        if (auth()->id() === $user->id && $data['role'] !== 'admin') {
            return back()->with('error', 'Anda tidak dapat menurunkan role akun Anda sendiri.');
        }

        $role = $data['role'];
        unset($data['role']); // bukan fillable → dipisah, di-set lewat forceFill()

        if (empty($data['password'])) {
            unset($data['password']); // '' sudah jadi null oleh ConvertEmptyStringsToNull
        }

        $user->update($data); // hanya name, email, (password)
        $user->forceFill(['role' => $role])->save();

        return redirect()->route('admin.users.index')
                         ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        // Prevent admin from deleting themselves
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
                         ->with('success', 'User deleted.');
    }
}
