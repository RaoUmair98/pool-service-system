<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_technician_to_their_company(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/team', [
            'role' => User::ROLE_TECHNICIAN,
            'name' => 'Tom Tech',
            'email' => 'tom@example.com',
            'phone' => '555-0100',
            'password' => 'secret123',
            'is_active' => '1',
        ])->assertRedirect(route('team.index'));

        $tech = User::where('email', 'tom@example.com')->sole();

        $this->assertSame($admin->company_id, $tech->company_id);
        $this->assertTrue($tech->isTechnician());
        $this->assertTrue($tech->is_active);

        // The new technician can log in with the password the admin gave them.
        auth()->logout();
        $this->post('/login', ['email' => 'tom@example.com', 'password' => 'secret123']);
        $this->assertAuthenticatedAs($tech);
    }

    public function test_admin_can_change_a_members_role(): void
    {
        $admin = User::factory()->admin()->create();
        $tech = User::factory()->technician()->create(['company_id' => $admin->company_id]);

        $this->actingAs($admin)->put("/team/{$tech->id}", [
            'role' => User::ROLE_ADMIN,
            'name' => $tech->name,
            'email' => $tech->email,
            'is_active' => '1',
        ])->assertRedirect(route('team.index'));

        $this->assertTrue($tech->fresh()->isAdmin());
        $this->assertTrue(ActivityLog::where('subject_id', $tech->id)->where('action', 'updated')->exists());
    }

    public function test_technician_cannot_manage_team_or_company(): void
    {
        $tech = User::factory()->technician()->create();

        $this->actingAs($tech)->get('/team')->assertForbidden();
        $this->actingAs($tech)->get('/company')->assertForbidden();
        $this->actingAs($tech)->get('/activity-log')->assertForbidden();
        $this->actingAs($tech)->get('/dashboard')->assertOk();
    }

    public function test_admin_cannot_see_or_change_another_companys_people(): void
    {
        $admin = User::factory()->admin()->create();
        $stranger = User::factory()->technician()->create();

        $this->actingAs($admin)->get('/team')->assertOk()->assertDontSee($stranger->email);
        $this->actingAs($admin)->get("/team/{$stranger->id}/edit")->assertNotFound();
        $this->actingAs($admin)->patch("/team/{$stranger->id}/toggle")->assertNotFound();
        $this->actingAs($admin)->delete("/team/{$stranger->id}")->assertNotFound();

        $this->assertTrue($stranger->fresh()->is_active);
    }

    public function test_admin_cannot_lock_themselves_out(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->patch("/team/{$admin->id}/toggle");
        $this->actingAs($admin)->delete("/team/{$admin->id}");
        $this->actingAs($admin)->put("/team/{$admin->id}", [
            'role' => User::ROLE_TECHNICIAN,
            'name' => 'Still Me',
            'email' => $admin->email,
        ]);

        $admin->refresh();
        $this->assertTrue($admin->is_active);
        $this->assertTrue($admin->isAdmin());
        $this->assertSame('Still Me', $admin->name);
    }

    public function test_turned_off_member_cannot_log_in_and_is_signed_out(): void
    {
        $admin = User::factory()->admin()->create();
        $tech = User::factory()->technician()->create(['company_id' => $admin->company_id]);

        $this->actingAs($admin)->patch("/team/{$tech->id}/toggle");
        $this->assertFalse($tech->fresh()->is_active);

        // Already signed in: thrown out on the next page.
        $this->actingAs($tech->fresh())->get('/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();

        // Trying to sign in again.
        $this->post('/login', ['email' => $tech->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_activity_log_only_shows_own_company(): void
    {
        $admin = User::factory()->admin()->create();
        $otherAdmin = User::factory()->admin()->create();

        ActivityLog::record('login', label: 'mine@example.com', user: $admin);
        ActivityLog::record('login', label: 'theirs@example.com', user: $otherAdmin);

        $this->actingAs($admin)->get('/activity-log')
            ->assertOk()
            ->assertSee('mine@example.com')
            ->assertDontSee('theirs@example.com');
    }

    public function test_only_admin_cannot_delete_their_own_account(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->from('/profile')->delete('/profile', ['password' => 'password'])
            ->assertSessionHasErrorsIn('userDeletion', 'password');

        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_can_update_company_details(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put('/company', [
            'name' => 'Crystal Clear Pools',
            'phone' => '555-0199',
            'address' => '1 Main Street',
        ])->assertRedirect(route('company.edit'));

        $this->assertSame('Crystal Clear Pools', $admin->company->fresh()->name);
    }
}
