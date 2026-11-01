<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Roles come from their migration; this adds a demo company with one admin
     * and one technician so there is something to log in with locally.
     */
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $company = Company::firstOrCreate(['name' => 'Demo Pool Service'], ['email' => 'office@example.com']);

        foreach ([
            [User::ROLE_ADMIN, 'Demo Admin', env('ADMIN_EMAIL', 'admin@example.com')],
            [User::ROLE_TECHNICIAN, 'Demo Technician', 'tech@example.com'],
        ] as [$role, $name, $email]) {
            User::firstOrCreate(['email' => $email], [
                'company_id' => $company->id,
                'name' => $name,
                'password' => env('ADMIN_PASSWORD', 'password'),
            ])->syncRoles($role);
        }
    }
}
