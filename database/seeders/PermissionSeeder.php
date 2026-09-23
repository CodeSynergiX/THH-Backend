<?php

namespace Database\Seeders;

use App\Domains\Users\Permissions\PermissionCatalog;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public static function syncCatalog(): void
    {
        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();

        foreach (['web', 'sanctum'] as $guard) {
            $permissions = [];

            foreach (PermissionCatalog::all() as $name) {
                $permissions[$name] = Permission::findOrCreate($name, $guard);
            }

            $registrar->forgetCachedPermissions();

            foreach (PermissionCatalog::roleGrants() as $roleName => $permissionNames) {
                $role = Role::findOrCreate($roleName, $guard);
                $role->syncPermissions(
                    collect($permissionNames)
                        ->map(fn (string $name) => $permissions[$name])
                        ->all()
                );
            }
        }

        $registrar->forgetCachedPermissions();
    }

    public function run(): void
    {
        self::syncCatalog();
    }
}
