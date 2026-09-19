<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RoleController extends Controller
{
    /**
     * Memastikan hanya admin yang bisa mengakses RoleController
     */
    protected function checkAdmin()
    {
        $role = Auth::user()?->role ?? session('user_role');
        if (strtolower($role ?? '') !== 'admin') {
            abort(403, 'Akses Ditolak: Hanya Administrator yang berhak mengakses dan mengubah pengaturan Role & Hak Akses tombol.');
        }
    }

    /**
     * Menampilkan daftar role dan matriks hak akses tombol
     */
    public function index(Request $request)
    {
        $this->checkAdmin();

        $roles = Role::all();
        $availablePermissions = Role::availablePermissions();

        // Tentukan role yang sedang dipilih untuk diedit permissions-nya
        $selectedRoleId = $request->query('role_id', optional($roles->firstWhere('name', 'user') ?? $roles->first())->id);
        $selectedRole = Role::find($selectedRoleId) ?? $roles->first();

        // Hitung total pengguna untuk setiap role
        $roleUserCounts = User::selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        return view('content.roles.index', compact('roles', 'availablePermissions', 'selectedRole', 'roleUserCounts'));
    }

    /**
     * Memperbarui daftar hak akses tombol untuk role tertentu
     */
    public function updatePermissions(Request $request, $id)
    {
        $this->checkAdmin();

        $role = Role::findOrFail($id);

        // Jika admin, pastikan minimal memiliki role_manage agar tidak terkunci sendiri
        $permissions = $request->input('permissions', []);
        if (!is_array($permissions)) {
            $permissions = [];
        }

        if ($role->name === 'admin' && !in_array('role_manage', $permissions, true)) {
            $permissions[] = 'role_manage';
        }

        $role->permissions = array_values($permissions);
        $role->save();

        return redirect()->route('roles.index', ['role_id' => $role->id])
            ->with('success', "Hak akses tombol untuk role '{$role->display_name}' berhasil diperbarui!");
    }

    /**
     * Menambahkan role baru
     */
    public function store(Request $request)
    {
        $this->checkAdmin();
        $request->validate([
            'name' => 'required|string|alpha_dash|unique:roles,name',
            'display_name' => 'required|string|max:100',
            'description' => 'nullable|string|max:255',
        ], [
            'name.unique' => 'Kode / Identitas role tersebut sudah digunakan!',
            'name.alpha_dash' => 'Kode role hanya boleh berupa huruf, angka, tanda hubung (-) dan garis bawah (_).'
        ]);

        $role = Role::create([
            'name' => strtolower($request->name),
            'display_name' => $request->display_name,
            'description' => $request->description,
            'permissions' => $request->input('permissions', []),
        ]);

        return redirect()->route('roles.index', ['role_id' => $role->id])
            ->with('success', "Role baru '{$role->display_name}' berhasil ditambahkan!");
    }
}
