<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Pool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PoolTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_a_pool_with_equipment(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = Customer::factory()->create(['company_id' => $admin->company_id]);

        $response = $this->actingAs($admin)->post(route('customers.pools.store', $customer), [
            'name' => 'Main',
            'type' => Pool::TYPE_CHLORINE,
            'gallons' => 15000,
            'monthly_rate' => '120.50',
            'is_active' => '1',
            'equipment' => [
                ['name' => 'Pump', 'notes' => 'Hayward'],
                ['name' => '', 'notes' => 'ignored'],
                ['name' => 'Filter', 'notes' => ''],
            ],
        ]);

        $pool = Pool::where('customer_id', $customer->id)->where('name', 'Main')->sole();

        $response->assertRedirect(route('pools.show', $pool));
        $this->assertSame($admin->company_id, $pool->company_id);
        $this->assertSame(Pool::TYPE_CHLORINE, $pool->type);
        $this->assertSame('120.50', $pool->monthly_rate);
        $this->assertCount(2, $pool->equipment);
        $this->assertSame('Pump', $pool->equipment->first()->name);
    }

    public function test_admin_can_update_toggle_and_delete_pool(): void
    {
        Storage::fake('public');

        $admin = User::factory()->admin()->create();
        $pool = Pool::factory()->create([
            'company_id' => $admin->company_id,
            'customer_id' => Customer::factory()->create(['company_id' => $admin->company_id])->id,
        ]);

        $this->actingAs($admin)->put(route('pools.update', $pool), [
            'name' => 'Backyard',
            'type' => Pool::TYPE_SALT,
            'gallons' => 20000,
            'monthly_rate' => '199.00',
            'is_active' => '1',
            'photo' => UploadedFile::fake()->image('pool.jpg'),
            'equipment' => [
                ['name' => 'Heater', 'notes' => 'Gas'],
            ],
        ])->assertRedirect(route('pools.show', $pool));

        $pool->refresh();
        $this->assertSame('Backyard', $pool->name);
        $this->assertSame(Pool::TYPE_SALT, $pool->type);
        $this->assertNotNull($pool->photo_path);
        Storage::disk('public')->assertExists($pool->photo_path);
        $this->assertCount(1, $pool->equipment);

        $this->actingAs($admin)->patch(route('pools.toggle', $pool));
        $this->assertFalse($pool->fresh()->is_active);

        $customerId = $pool->customer_id;
        $this->actingAs($admin)->delete(route('pools.destroy', $pool))
            ->assertRedirect(route('customers.show', $customerId));

        $this->assertNull(Pool::find($pool->id));
    }

    public function test_technician_cannot_manage_pools(): void
    {
        $tech = User::factory()->technician()->create();
        $customer = Customer::factory()->create(['company_id' => $tech->company_id]);
        $pool = Pool::factory()->create([
            'company_id' => $tech->company_id,
            'customer_id' => $customer->id,
        ]);

        $this->actingAs($tech)->get(route('customers.pools.create', $customer))->assertForbidden();
        $this->actingAs($tech)->get(route('pools.show', $pool))->assertForbidden();
        $this->actingAs($tech)->post(route('customers.pools.store', $customer), [
            'type' => Pool::TYPE_CHLORINE,
            'monthly_rate' => '10',
        ])->assertForbidden();
    }

    public function test_admin_cannot_see_or_change_another_companys_pools(): void
    {
        $admin = User::factory()->admin()->create();
        $strangerCustomer = Customer::factory()->create(['name' => 'Other Co']);
        $strangerPool = Pool::factory()->create([
            'company_id' => $strangerCustomer->company_id,
            'customer_id' => $strangerCustomer->id,
            'name' => 'Secret Pool',
        ]);

        $this->actingAs($admin)->get(route('pools.show', $strangerPool))->assertNotFound();
        $this->actingAs($admin)->get(route('pools.edit', $strangerPool))->assertNotFound();
        $this->actingAs($admin)->get(route('customers.pools.create', $strangerCustomer))->assertNotFound();
        $this->actingAs($admin)->put(route('pools.update', $strangerPool), [
            'name' => 'Hacked',
            'type' => Pool::TYPE_CHLORINE,
            'monthly_rate' => '1',
            'is_active' => '1',
        ])->assertNotFound();
        $this->actingAs($admin)->delete(route('pools.destroy', $strangerPool))->assertNotFound();

        $this->assertSame('Secret Pool', $strangerPool->fresh()->name);
    }

    public function test_customer_with_pools_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $customer = Customer::factory()->create(['company_id' => $admin->company_id]);
        Pool::factory()->create([
            'company_id' => $admin->company_id,
            'customer_id' => $customer->id,
        ]);

        $this->actingAs($admin)->from(route('customers.index'))
            ->delete(route('customers.destroy', $customer))
            ->assertRedirect(route('customers.index'))
            ->assertSessionHas('error');

        $this->assertNotNull($customer->fresh());
    }
}
