<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_customer(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->post('/customers', [
            'name' => 'Sam Owner',
            'phone' => '555-0101',
            'email' => 'sam@example.com',
            'service_address' => '10 Pool Lane',
            'access_notes' => 'Gate code 99',
            'is_active' => '1',
        ])->assertRedirect();

        $customer = Customer::where('email', 'sam@example.com')->sole();

        $this->assertSame($admin->company_id, $customer->company_id);
        $this->assertSame('Sam Owner', $customer->name);
        $this->assertSame('Gate code 99', $customer->access_notes);
        $this->assertTrue($customer->is_active);
    }

    public function test_admin_can_view_edit_toggle_and_delete_own_customer(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = Customer::factory()->create(['company_id' => $admin->company_id]);

        $this->actingAs($admin)->get("/customers/{$customer->id}")->assertOk()->assertSee($customer->name);

        $this->actingAs($admin)->put("/customers/{$customer->id}", [
            'name' => 'Updated Name',
            'phone' => $customer->phone,
            'email' => $customer->email,
            'service_address' => $customer->service_address,
            'access_notes' => 'New note',
            'is_active' => '1',
        ])->assertRedirect(route('customers.show', $customer));

        $this->assertSame('Updated Name', $customer->fresh()->name);

        $this->actingAs($admin)->patch("/customers/{$customer->id}/toggle");
        $this->assertFalse($customer->fresh()->is_active);

        $this->actingAs($admin)->delete("/customers/{$customer->id}")
            ->assertRedirect(route('customers.index'));

        $this->assertNull(Customer::find($customer->id));
    }

    public function test_technician_cannot_manage_customers(): void
    {
        $tech = User::factory()->technician()->create();
        $customer = Customer::factory()->create(['company_id' => $tech->company_id]);

        $this->actingAs($tech)->get('/customers')->assertForbidden();
        $this->actingAs($tech)->get("/customers/{$customer->id}")->assertForbidden();
        $this->actingAs($tech)->post('/customers', [
            'name' => 'Nope',
            'service_address' => 'Nowhere',
        ])->assertForbidden();
    }

    public function test_admin_cannot_see_or_change_another_companys_customers(): void
    {
        $admin = User::factory()->admin()->create();
        $stranger = Customer::factory()->create(['name' => 'Other Co Customer']);

        $this->actingAs($admin)->get('/customers')->assertOk()->assertDontSee('Other Co Customer');
        $this->actingAs($admin)->get("/customers/{$stranger->id}")->assertNotFound();
        $this->actingAs($admin)->get("/customers/{$stranger->id}/edit")->assertNotFound();
        $this->actingAs($admin)->put("/customers/{$stranger->id}", [
            'name' => 'Hacked',
            'service_address' => 'Nope',
            'is_active' => '1',
        ])->assertNotFound();
        $this->actingAs($admin)->patch("/customers/{$stranger->id}/toggle")->assertNotFound();
        $this->actingAs($admin)->delete("/customers/{$stranger->id}")->assertNotFound();

        $this->assertSame('Other Co Customer', $stranger->fresh()->name);
    }

    public function test_admin_can_update_company_currency_timezone_and_details(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->put('/company', [
            'name' => 'Crystal Clear Pools',
            'phone' => '555-0199',
            'address' => '1 Main Street',
            'currency' => 'GBP',
            'timezone' => 'Europe/London',
        ])->assertRedirect(route('company.edit'));

        $company = $admin->company->fresh();

        $this->assertSame('Crystal Clear Pools', $company->name);
        $this->assertSame('GBP', $company->currency);
        $this->assertSame('Europe/London', $company->timezone);
    }
}
