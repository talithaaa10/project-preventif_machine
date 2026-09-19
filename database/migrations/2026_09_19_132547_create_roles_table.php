<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        // Seed default roles
        $allPermissions = [
            'machine_create',
            'machine_import',
            'battery_create',
            'battery_edit',
            'battery_delete',
            'battery_import',
            'battery_export',
            'breakdown_create',
            'breakdown_delete',
            'breakdown_import',
            'user_manage',
            'role_manage',
        ];

        \Illuminate\Support\Facades\DB::table('roles')->insert([
            [
                'name' => 'admin',
                'display_name' => 'Administrator',
                'description' => 'Akses penuh ke semua fitur dan tombol administrasi sistem',
                'permissions' => json_encode($allPermissions),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'user',
                'display_name' => 'User / Operator',
                'description' => 'Pengguna standar dengan hak akses tombol terbatas',
                'permissions' => json_encode([
                    'battery_create',
                    'battery_export',
                    'breakdown_create',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
