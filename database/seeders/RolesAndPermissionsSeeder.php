<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public const PERMISSIONS = [
        'view activity log',
    ];

    public function run()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (self::PERMISSIONS as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(self::PERMISSIONS);

        // Someone has to be able to log in and manage the business: the first
        // user becomes admin, or a default admin account is created.
        if (! User::role('admin')->exists()) {
            $user = User::orderBy('id')->first() ?? User::create([
                'name' => 'Admin',
                'email' => env('ADMIN_EMAIL', 'admin@example.com'),
                'password' => env('ADMIN_PASSWORD', 'password'),
            ]);

            $user->assignRole($admin);
        }
    }
}
