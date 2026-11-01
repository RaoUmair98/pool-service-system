<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * The two roles every company has. Safe to run again: it only adds what is missing
 * and resets each role to the permissions listed here.
 */
class RolesAndPermissionsSeeder extends Seeder
{
    /** role => permissions */
    public const ROLES = [
        User::ROLE_ADMIN => [
            'manage company',
            'manage team',
            'view activity log',
        ],
        User::ROLE_TECHNICIAN => [],
    ];

    public function run()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (array_unique(array_merge(...array_values(self::ROLES))) as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        foreach (self::ROLES as $role => $permissions) {
            Role::firstOrCreate(['name' => $role])->syncPermissions($permissions);
        }
    }
}
