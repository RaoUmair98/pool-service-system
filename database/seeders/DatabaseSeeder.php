<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Pool;
use App\Models\ServiceSchedule;
use App\Models\User;
use App\Services\InvoiceGenerator;
use App\Services\VisitGenerator;
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

        $company = Company::firstOrCreate(
            ['name' => 'Demo Pool Service'],
            [
                'email' => 'office@example.com',
                'currency' => 'USD',
                'timezone' => 'America/New_York',
            ]
        );

        $admin = null;
        $tech = null;

        foreach ([
            [User::ROLE_ADMIN, 'Demo Admin', env('ADMIN_EMAIL', 'admin@example.com')],
            [User::ROLE_TECHNICIAN, 'Demo Technician', 'tech@example.com'],
        ] as [$role, $name, $email]) {
            $user = User::firstOrCreate(['email' => $email], [
                'company_id' => $company->id,
                'name' => $name,
                'password' => env('ADMIN_PASSWORD', 'password'),
            ]);
            $user->syncRoles($role);

            if ($role === User::ROLE_ADMIN) {
                $admin = $user;
            } else {
                $tech = $user;
            }
        }

        $customer = Customer::firstOrCreate(
            ['company_id' => $company->id, 'email' => 'jordan@example.com'],
            [
                'name' => 'Jordan Rivera',
                'phone' => '555-0142',
                'service_address' => '42 Palm Court, Miami, FL 33101',
                'access_notes' => 'Gate code 4821. Dog is friendly but loud — leave gate closed.',
                'is_active' => true,
            ]
        );

        $main = Pool::firstOrCreate(
            ['customer_id' => $customer->id, 'name' => 'Main'],
            [
                'company_id' => $company->id,
                'type' => Pool::TYPE_CHLORINE,
                'gallons' => 18000,
                'monthly_rate' => 149.00,
                'is_active' => true,
            ]
        );

        if ($main->equipment()->count() === 0) {
            $main->equipment()->createMany([
                ['name' => 'Pump', 'notes' => 'Pentair SuperFlo'],
                ['name' => 'Filter', 'notes' => 'Sand filter — backwash monthly'],
            ]);
        }

        $spa = Pool::firstOrCreate(
            ['customer_id' => $customer->id, 'name' => 'Spa'],
            [
                'company_id' => $company->id,
                'type' => Pool::TYPE_SALT,
                'gallons' => 800,
                'monthly_rate' => 59.00,
                'is_active' => true,
            ]
        );

        if ($spa->equipment()->count() === 0) {
            $spa->equipment()->create(['name' => 'Salt cell', 'notes' => 'Check plates each visit']);
        }

        // Monday for Main, Wednesday for Spa — both assigned to the demo technician.
        ServiceSchedule::updateOrCreate(
            ['pool_id' => $main->id],
            [
                'company_id' => $company->id,
                'technician_user_id' => $tech->id,
                'weekday' => 1,
                'is_active' => true,
            ]
        );

        ServiceSchedule::updateOrCreate(
            ['pool_id' => $spa->id],
            [
                'company_id' => $company->id,
                'technician_user_id' => $tech->id,
                'weekday' => 3,
                'is_active' => true,
            ]
        );

        app(VisitGenerator::class)->generateForCompany($company);
        app(InvoiceGenerator::class)->generateForCompany(
            $company,
            (int) now()->format('Y'),
            (int) now()->format('n'),
        );
    }
}
