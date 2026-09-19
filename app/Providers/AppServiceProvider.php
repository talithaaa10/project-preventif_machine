<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Auth;
use App\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Blade directive: @hasPermission('key') ... @endhasPermission
        Blade::if('hasPermission', function ($permissionKey) {
            $user = Auth::user();
            if (!$user) {
                $roleName = session('user_role');
                if ($roleName === 'admin') {
                    return true;
                }
                if (!$roleName) {
                    return false;
                }
                $role = Role::where('name', $roleName)->first();
                return $role && is_array($role->permissions) && in_array($permissionKey, $role->permissions, true);
            }
            return $user->hasPermission($permissionKey);
        });
    }
}
