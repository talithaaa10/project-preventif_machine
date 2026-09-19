<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Role;

class RolePermissionTest extends TestCase
{
    public function test_roles_index_page_can_be_rendered(): void
    {
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::create([
                'name' => 'admin_test',
                'password' => 'secret',
                'role' => 'admin'
            ]);
        }

        $response = $this->actingAs($admin)->get('/roles');
        $response->assertStatus(200);
        $response->assertSee('Pengaturan Hak Akses Tombol');
    }

    public function test_user_has_correct_permissions(): void
    {
        $admin = User::where('role', 'admin')->first();
        $user = User::where('role', 'user')->first();

        $this->assertNotNull($admin);
        $this->assertNotNull($user);

        // Admin selalu punya hak akses
        $this->assertTrue($admin->hasPermission('machine_create'));
        $this->assertTrue($admin->hasPermission('battery_delete'));
        $this->assertTrue($admin->hasPermission('battery_input'));

        // User dengan izin battery_input
        $userRole = Role::where('name', 'user')->first();
        $userRole->permissions = ['battery_input'];
        $userRole->save();

        $this->assertTrue($user->hasPermission('battery_input'));
        $this->assertTrue($user->hasPermission('battery_create')); // mapping
        $this->assertFalse($user->hasPermission('battery_delete'));
        $this->assertFalse($user->hasPermission('machine_import'));
    }

    public function test_roles_update_permissions(): void
    {
        $userRole = Role::where('name', 'user')->first();
        $this->assertNotNull($userRole);

        $newPerms = ['battery_input', 'export_import'];

        $admin = User::where('role', 'admin')->first();
        $response = $this->actingAs($admin)->post("/roles/{$userRole->id}/permissions", [
            'permissions' => $newPerms,
        ]);

        $response->assertRedirect();

        $userRole->refresh();
        $this->assertEquals($newPerms, $userRole->permissions);

        // Kembalikan ke battery_input
        $userRole->permissions = ['battery_input'];
        $userRole->save();
    }

    public function test_buttons_visibility_based_on_role(): void
    {
        $admin = User::where('role', 'admin')->first();
        $user = User::where('role', 'user')->first();

        $userRole = Role::where('name', 'user')->first();
        $userRole->permissions = ['battery_input'];
        $userRole->save();

        // 1. Cek Admin di /machines (harus melihat tombol Tambah Manual & Import Excel)
        $responseAdmin = $this->actingAs($admin)->get('/machines');
        $responseAdmin->assertStatus(200);
        $responseAdmin->assertSee('Tambah Manual');
        $responseAdmin->assertSee('Import Excel');

        // 2. Cek User di /machines (Tampilan sama, tapi tombol Tambah Manual & Import Excel TIDAK ADA karena belum dicentang)
        $responseUser = $this->actingAs($user)->get('/machines');
        $responseUser->assertStatus(200);
        $responseUser->assertDontSee('Tambah Manual');
        $responseUser->assertDontSee('Import Excel');
    }

    public function test_battery_save_button_visibility(): void
    {
        $user = User::where('role', 'user')->first();
        $userRole = Role::where('name', 'user')->first();

        // 1. Ketika role user punya battery_input -> tombol Simpan Data Battery MUNCUL
        $userRole->permissions = ['battery_input'];
        $userRole->save();

        $responseWithPerm = $this->actingAs($user)->get('/input/battery');
        $responseWithPerm->assertStatus(200);
        $responseWithPerm->assertSee('Simpan Data Battery');

        // 2. Ketika role user TIDAK punya battery_input -> tombol Simpan Data Battery HILANG (kasus Gambar 1)
        $userRole->permissions = [];
        $userRole->save();

        $responseWithoutPerm = $this->actingAs($user)->get('/input/battery');
        $responseWithoutPerm->assertStatus(200);
        $responseWithoutPerm->assertDontSee('Simpan Data Battery');

        // Kembalikan ke battery_input
        $userRole->permissions = ['battery_input'];
        $userRole->save();
    }

    public function test_regular_user_cannot_see_or_access_roles(): void
    {
        $admin = User::where('role', 'admin')->first();
        $user = User::where('role', 'user')->first();

        // 1. User mencoba akses halaman /roles langsung -> harus 403 Forbidden
        $responseUser = $this->actingAs($user)->get('/roles');
        $responseUser->assertStatus(403);

        // 2. User melihat dashboard -> menu Role & Hak Akses dan Administrasi tidak muncul
        $responseDashboard = $this->actingAs($user)->get('/dashboard');
        $responseDashboard->assertStatus(200);
        $responseDashboard->assertDontSee('Role &amp; Hak Akses', false);
        $responseDashboard->assertDontSee('Role & Hak Akses');
        $responseDashboard->assertDontSee('route(\'roles.index\')', false);

        // 3. Admin dapat mengakses /roles dan melihat menunya
        $responseAdmin = $this->actingAs($admin)->get('/roles');
        $responseAdmin->assertStatus(200);

        $responseAdminDashboard = $this->actingAs($admin)->get('/dashboard');
        $responseAdminDashboard->assertStatus(200);
        $responseAdminDashboard->assertSee('Role & Hak Akses', false);
    }
}
