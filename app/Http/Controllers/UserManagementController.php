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
        $locations = MasterLocation::orderBy('name')->get();

        return view('admin.users.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', Rule::in(User::ROLES)],
            'master_location_id' => ['nullable', 'required_if:role,Admin Kapal', 'exists:master_locations,id'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            // Kapal hanya berarti untuk Admin Kapal.
            'master_location_id' => $validated['role'] === 'Admin Kapal' ? $validated['master_location_id'] : null,
        ]);

        return redirect('/admin/users')
            ->with('success', 'User berhasil ditambah');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect('/admin/users')
            ->with('success', 'User berhasil dihapus');
    }
}
