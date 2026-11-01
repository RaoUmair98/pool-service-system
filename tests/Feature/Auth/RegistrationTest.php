<?php

namespace Tests\Feature\Auth;

use App\Models\Company;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_company_can_register_and_owner_becomes_admin(): void
    {
        $response = $this->post('/register', [
            'company_name' => 'Blue Water Pools',
            'company_phone' => '0300 1234567',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $company = Company::sole();
        $user = User::sole();

        $this->assertSame('Blue Water Pools', $company->name);
        $this->assertSame($company->id, $user->company_id);
        $this->assertTrue($user->isAdmin());
    }

    public function test_company_name_is_required(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('company_name');

        $this->assertGuest();
        $this->assertSame(0, Company::count());
    }
}
