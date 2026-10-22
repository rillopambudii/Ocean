<?php

namespace App\Http\Controllers;

use App\Models\MasterLocation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index()
    {
        $data = User::with('location')->get();

        return view('admin.users.index', compact('data'));
    }

    public function create()
    {
        return $this->form(null);
    }

    public function store(Request $request)
    {
        $validated = $this->validateUser($request, null);

        User::create($validated);

        return redirect('/admin/users')
            ->with('success', 'User berhasil ditambah');
    }

    public function edit(User $user)
    {
        return $this->form($user);
    }

    public function update(Request $request, User $user)
    {
        $user->update($this->validateUser($request, $user));

        return redirect('/admin/users')
            ->with('success', 'User berhasil diperbarui');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect('/admin/users')
            ->with('success', 'User berhasil dihapus');
    }

    private function form(?User $user)
    {
        $locations = MasterLocation::orderBy('name')->get();

        return view('admin.users.form', compact('user', 'locations'));
    }

    private function validateUser(Request $request, ?User $user): array
    {
        // Role akun sendiri dikunci supaya Super Admin tidak mengunci dirinya keluar.
        if ($user?->is($request->user())) {
            $request->merge(['role' => $user->role]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::in(User::ROLES)],
            'master_location_id' => ['nullable', 'required_if:role,Admin Kapal', 'exists:master_locations,id'],
        ]);

        if (filled($validated['password'] ?? null)) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Kapal hanya berarti untuk Admin Kapal.
        if ($validated['role'] !== 'Admin Kapal') {
            $validated['master_location_id'] = null;
        }

        return $validated;
    }
}
