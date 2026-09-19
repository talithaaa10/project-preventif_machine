<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Memastikan hanya administrator yang berhak mengakses dan mengelola pengguna
     */
    protected function checkAdmin()
    {
        $role = Auth::user()?->role ?? session('user_role');
        if (strtolower($role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator yang berhak mengakses dan mengelola pengguna.');
        }
    }

    // Menampilkan daftar user
    public function index()
    {
        $this->checkAdmin();
        $users = User::all();
        $roles = Role::all();
        return view('content.users.index', compact('users', 'roles'));
    }

    // Menyimpan user baru
    public function store(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|unique:users,name',
            'password' => 'required',
            'role' => 'required|exists:roles,name'
        ], [
            'name.unique' => 'Username/Nama tersebut sudah digunakan!',
            'role.exists' => 'Role yang dipilih tidak valid!'
        ]);

        User::create([
            'name' => $request->name,
            'password' => $request->password,
            'role' => $request->role,
        ]);

        return back()->with('success', "Akun '{$request->name}' berhasil ditambahkan!");
    }

    // Mengubah role user yang sudah ada
    public function updateRole(Request $request, $id)
    {
        $this->checkAdmin();
        $request->validate([
            'role' => 'required|exists:roles,name'
        ], [
            'role.exists' => 'Role yang dipilih tidak valid!'
        ]);

        $user = User::findOrFail($id);
        $user->update([
            'role' => $request->role
        ]);

        return back()->with('success', "Role untuk {$user->name} berhasil diperbarui!");
    }
}