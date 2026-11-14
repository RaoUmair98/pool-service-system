<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Pool;
use App\Models\User;
use App\Models\Visit;
use App\Models\VisitPhoto;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_technician_lands_on_todays_route_after_login(): void
    {
        $tech = User::factory()->technician()->create();

        $this->actingAs($tech)->get('/')->assertRedirect(route('route.index'));
    }

    public function test_technician_sees_only_own_visits_for_today(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-16 09:00:00', 'UTC'));

        $company = \App\Models\Company::factory()->create(['timezone' => 'UTC']);
        $tech = User::factory()->technician()->create(['company_id' => $company->id]);
        $other = User::factory()->technician()->create(['company_id' => $company->id]);
        $pool = Pool::factory()->create(['company_id' => $company->id]);

        $mine = Visit::factory()->create([
            'company_id' => $company->id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $tech->id,
            'scheduled_date' => '2026-11-16',
        ]);

        Visit::factory()->create([
            'company_id' => $company->id,
            'pool_id' => Pool::factory()->create(['company_id' => $company->id])->id,
            'customer_id' => Customer::factory()->create(['company_id' => $company->id])->id,
            'technician_user_id' => $other->id,
            'scheduled_date' => '2026-11-16',
        ]);

        $this->actingAs($tech)->get(route('route.index'))
            ->assertOk()
            ->assertSee($mine->customer->name)
            ->assertDontSee($other->name);

        Carbon::setTestNow();
    }

    public function test_technician_can_start_save_and_complete_visit_with_photo(): void
    {
        Storage::fake('public');
        Carbon::setTestNow(Carbon::parse('2026-11-16 09:00:00', 'UTC'));

        $company = \App\Models\Company::factory()->create(['timezone' => 'UTC']);
        $tech = User::factory()->technician()->create(['company_id' => $company->id]);
        $pool = Pool::factory()->create(['company_id' => $company->id]);
        $visit = Visit::factory()->create([
            'company_id' => $company->id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $tech->id,
            'scheduled_date' => '2026-11-16',
            'status' => Visit::STATUS_SCHEDULED,
        ]);

        $this->actingAs($tech)->post(route('route.start', $visit))->assertRedirect(route('route.show', $visit));
        $this->assertSame(Visit::STATUS_IN_PROGRESS, $visit->fresh()->status);
        $this->assertNotNull($visit->fresh()->started_at);

        $this->actingAs($tech)->post(route('route.update', $visit), [
            'checklist' => ['skim' => '1', 'brush' => '1'],
            'reading_ph' => '7.4',
            'reading_free_chlorine' => '2.0',
            'reading_alkalinity' => '100',
            'notes' => 'Added chlorine tabs',
        ])->assertRedirect(route('route.show', $visit));

        $visit->refresh();
        $this->assertTrue($visit->checklistItems()['skim']);
        $this->assertTrue($visit->checklistItems()['brush']);
        $this->assertFalse($visit->checklistItems()['vacuum']);
        $this->assertSame('7.40', $visit->reading_ph);
        $this->assertSame('Added chlorine tabs', $visit->notes);

        $this->actingAs($tech)->post(route('route.photos.store', $visit), [
            'photo' => UploadedFile::fake()->image('before.jpg'),
            'kind' => VisitPhoto::KIND_BEFORE,
        ])->assertRedirect(route('route.show', $visit));

        $this->assertSame(1, $visit->photos()->count());

        $this->actingAs($tech)->post(route('route.complete', $visit), [
            'checklist' => ['skim' => '1', 'brush' => '1', 'vacuum' => '1', 'empty_baskets' => '1', 'clean_filter' => '1'],
            'reading_ph' => '7.4',
            'reading_free_chlorine' => '2.0',
            'reading_alkalinity' => '100',
            'notes' => 'All good',
        ])->assertRedirect(route('route.index'));

        $visit->refresh();
        $this->assertSame(Visit::STATUS_COMPLETED, $visit->status);
        $this->assertNotNull($visit->completed_at);
        $this->assertTrue($visit->checklistItems()['clean_filter']);

        Carbon::setTestNow();
    }

    public function test_technician_cannot_open_another_techs_visit(): void
    {
        $tech = User::factory()->technician()->create();
        $other = User::factory()->technician()->create(['company_id' => $tech->company_id]);
        $pool = Pool::factory()->create(['company_id' => $tech->company_id]);
        $visit = Visit::factory()->create([
            'company_id' => $tech->company_id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $other->id,
        ]);

        $this->actingAs($tech)->get(route('route.show', $visit))->assertNotFound();
        $this->actingAs($tech)->post(route('route.start', $visit))->assertNotFound();
    }

    public function test_admin_without_complete_permission_cannot_use_route(): void
    {
        // Admin role does not include "complete visits".
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('route.index'))->assertForbidden();
    }

    public function test_cannot_complete_skipped_visit(): void
    {
        $tech = User::factory()->technician()->create();
        $pool = Pool::factory()->create(['company_id' => $tech->company_id]);
        $visit = Visit::factory()->skipped()->create([
            'company_id' => $tech->company_id,
            'pool_id' => $pool->id,
            'customer_id' => $pool->customer_id,
            'technician_user_id' => $tech->id,
        ]);

        $this->actingAs($tech)->post(route('route.complete', $visit), [
            'notes' => 'Nope',
        ])->assertSessionHasErrors('status');
    }
}
