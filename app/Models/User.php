<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi ke model Role
     */
    public function roleData()
    {
        return $this->belongsTo(Role::class, 'role', 'name');
    }

    /**
     * Periksa apakah user memiliki hak akses terhadap tombol/fitur tertentu
     */
    public function hasPermission(string $permissionKey): bool
    {
        // Admin selalu memiliki akses penuh ke semua tombol
        if (strtolower($this->role ?? '') === 'admin') {
            return true;
        }

        $role = Role::where('name', $this->role)->first();
        if (!$role || !is_array($role->permissions)) {
            return false;
        }

        // Langsung cocok
        if (in_array($permissionKey, $role->permissions, true)) {
            return true;
        }

        // Mapping 4 grup hak akses operasional (Export/Import, Input Master, Input Battery, Hapus/Edit)
        $groupMappings = [
            'export_import' => ['export_import', 'battery_export', 'battery_import', 'machine_import', 'breakdown_import'],
            'master_input'  => ['master_input', 'machine_create', 'master_create'],
            'battery_input' => ['battery_input', 'battery_create'],
            'edit_delete'   => ['edit_delete', 'battery_edit', 'battery_delete', 'breakdown_delete'],
        ];

        // 1. Jika role punya group key (misal 'battery_input'), dan tombol meminta 'battery_create'
        foreach ($groupMappings as $groupKey => $mappedKeys) {
            if (in_array($groupKey, $role->permissions, true)) {
                if (in_array($permissionKey, $mappedKeys, true)) {
                    return true;
                }
            }
        }

        // 2. Kebalikannya: jika role punya specific key (misal 'battery_create') dan tombol meminta 'battery_input'
        if (isset($groupMappings[$permissionKey])) {
            foreach ($groupMappings[$permissionKey] as $mappedKey) {
                if (in_array($mappedKey, $role->permissions, true)) {
                    return true;
                }
            }
        }

        return false;
    }
}