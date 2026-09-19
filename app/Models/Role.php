<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Helper daftar semua permission keys yang tersedia di aplikasi
     */
    public static function availablePermissions(): array
    {
        return [
            'Hak Akses Tombol Operasional' => [
                'battery_input' => [
                    'label' => 'Input Penggantian Battery',
                    'description' => 'Tombol "Input Penggantian Battery" (ke halaman input batre) dan tombol Simpan Data Battery di form',
                ],
                'master_input' => [
                    'label' => 'Input Data Master',
                    'description' => 'Tombol "Input Data Master Battery" & "Tambah Data Mesin Manual"',
                ],
                'export_import' => [
                    'label' => 'Export dan Import',
                    'description' => 'Semua tombol Export ke Excel (.xlsx) dan Import / Upload file Excel di seluruh modul',
                ],
                'edit_delete' => [
                    'label' => 'Hapus & Edit',
                    'description' => 'Tombol Edit / Update riwayat data dan tombol Hapus data',
                ],
            ],
            'Administrasi Sistem' => [
                'user_manage' => [
                    'label' => 'Akses & Kelola User Management',
                    'description' => 'Menu sidebar dan manajemen akun pengguna',
                ],
                'role_manage' => [
                    'label' => 'Akses & Kelola Role & Hak Akses',
                    'description' => 'Menu sidebar dan konfigurasi hak akses tombol',
                ],
            ],
        ];
    }
}
