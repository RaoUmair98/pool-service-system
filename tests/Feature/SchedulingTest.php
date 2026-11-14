<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Pool;
use App\Models\ServiceSchedule;
use App\Models\User;
use App\Models\Visit;
use App\Services\VisitGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulingTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_pool_schedule_and_generate_visits(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00', 'America/New_York')); // Monday

        $company = \App\Models\Company::factory()->create(['timezone' => 'America/New_York']);
        $admin = User::factory()->admin()->create(['company_id' => $company->id]);
        $tech = User::factory()->technician()->create(['company_id' => $company->id]);
        $customer = Customer::factory()->create(['company_id' => $company->id]);
        $pool = Pool::factory()->create([
            'company_id' => $company->id,
            'customer_id' => $customer->id,
        ]);

        $this->actingAs($admin)->put(route('pools.schedule.update', $pool), [
            'weekday' => 1, // Monday
            'technician_user_id' => $tech->id,
            'is_active' => '1',
        ])->assertRedirect(route('pools.show', $pool));

        $schedule = $pool->fresh()->schedule;
        $this->assertNotNull($schedule);
        $this->assertSame(1, $schedule->weekday);
        $this->assertSame($tech->id, $schedule->technician_user_id);

        // From Mon 16 Nov through +14 days inclusive → Mondays 16, 23, 30 Nov = 3 visits
        $this->assertSame(3, Visit::where('pool_id', $pool->id)->count());
        $this->assertTrue(Visit::where('pool_id', $pool->id)->whereDate('scheduled_date', '2026-11-16')->exists());

        Carbon::setTestNow();
    }

    public function test_visit_generation_is_idempotent(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00', 'UTC'));

        $company = \App\Models\Company::factory()->create(['timezone' => 'UTC']);
        $tech = User::factory()->technician()->create(['company_id' => $company->id]);
        $pool = Pool::factory()->create(['company_id' => $company->id]);

        ServiceSchedule::factory()->create([
            'company_id' => $company->id,
            'pool_id' => $pool->id,
            'technician_user_id' => $tech->id,
            'weekday' => 1,
        ]);

        $generator = app(VisitGenerator::class);
        $first = $generator->generateForCompany($company);
        $second = $generator->generateForCompany($company);

        $this->assertGreaterThan(0, $first['created']);
        $this->assertSame(0, $second['created']);
        $this->assertSame($first['created'], $second['skipped']);
        $this->assertSame($first['created'], Visit::where('company_id', $company->id)->count());

        Carbon::setTestNow();
    }

    public function test_admin_can_reassign_reschedule_and_skip_visit(): void
    {
        $admin = User::factory()->admin()->create();
        $techA = User::factory()->technician()->create(['company_id' => $admin->company_id, 'name' => 'Tech A']);
        $techB = User::factory()->technician()->create(['company_id' => $admin->company_id, 'name' => 'Tech B']);
        $pool = Pool::factory()->create(['company_id' => $admin->company_id]);

        $visit = Visit::factory()->create([
            'company_id' => $admin->company_id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $techA->id,
            'scheduled_date' => '2026-11-20',
        ]);

        $this->actingAs($admin)->post(route('visits.reassign', $visit), [
            'technician_user_id' => $techB->id,
        ])->assertRedirect();
        $this->assertSame($techB->id, $visit->fresh()->technician_user_id);

        $this->actingAs($admin)->post(route('visits.reschedule', $visit), [
            'scheduled_date' => '2026-11-21',
        ])->assertRedirect();
        $this->assertSame('2026-11-21', $visit->fresh()->scheduled_date->toDateString());

        $this->actingAs($admin)->post(route('visits.skip', $visit), [
            'skip_reason' => 'Customer on holiday',
        ])->assertRedirect();

        $visit->refresh();
        $this->assertSame(Visit::STATUS_SKIPPED, $visit->status);
        $this->assertSame('Customer on holiday', $visit->skip_reason);

        // Skipped visits cannot be changed again.
        $this->actingAs($admin)->post(route('visits.reassign', $visit), [
            'technician_user_id' => $techA->id,
        ])->assertSessionHas('error');
    }

    public function test_skipped_visit_is_not_overwritten_by_generator(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-16 10:00:00', 'UTC'));

        $company = \App\Models\Company::factory()->create(['timezone' => 'UTC']);
        $tech = User::factory()->technician()->create(['company_id' => $company->id]);
        $pool = Pool::factory()->create(['company_id' => $company->id]);

        $schedule = ServiceSchedule::factory()->create([
            'company_id' => $company->id,
            'pool_id' => $pool->id,
            'technician_user_id' => $tech->id,
            'weekday' => 1,
        ]);

        Visit::factory()->skipped()->create([
            'company_id' => $company->id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $tech->id,
            'service_schedule_id' => $schedule->id,
            'scheduled_date' => '2026-11-16',
        ]);

        app(VisitGenerator::class)->generateForCompany($company);

        $this->assertSame(1, Visit::where('pool_id', $pool->id)->whereDate('scheduled_date', '2026-11-16')->count());
        $this->assertSame(Visit::STATUS_SKIPPED, Visit::where('pool_id', $pool->id)->whereDate('scheduled_date', '2026-11-16')->value('status'));

        Carbon::setTestNow();
    }

    public function test_technician_cannot_manage_schedules_or_visits(): void
    {
        $tech = User::factory()->technician()->create();
        $pool = Pool::factory()->create(['company_id' => $tech->company_id]);
        $visit = Visit::factory()->create([
            'company_id' => $tech->company_id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $tech->id,
        ]);

        $this->actingAs($tech)->get(route('visits.index'))->assertForbidden();
        $this->actingAs($tech)->get(route('pools.schedule.edit', $pool))->assertForbidden();
        $this->actingAs($tech)->get(route('visits.show', $visit))->assertForbidden();
    }

    public function test_admin_cannot_touch_another_companys_visits(): void
    {
        $admin = User::factory()->admin()->create();
        $other = Visit::factory()->create();

        $this->actingAs($admin)->get(route('visits.show', $other))->assertNotFound();
        $this->actingAs($admin)->post(route('visits.skip', $other), [
            'skip_reason' => 'Nope',
        ])->assertNotFound();
    }

    public function test_pool_with_visits_cannot_be_deleted(): void
    {
        $admin = User::factory()->admin()->create();
        $pool = Pool::factory()->create(['company_id' => $admin->company_id]);
        Visit::factory()->create([
            'company_id' => $admin->company_id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => User::factory()->technician()->create(['company_id' => $admin->company_id])->id,
        ]);

        $this->actingAs($admin)->from(route('pools.show', $pool))
            ->delete(route('pools.destroy', $pool))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertNotNull($pool->fresh());
    }
}
