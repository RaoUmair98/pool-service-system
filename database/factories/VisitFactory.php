<?php

namespace Database\Factories;

use App\Models\Pool;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Visit>
 */
class VisitFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pool_id' => Pool::factory(),
            'company_id' => fn (array $attributes) => Pool::find($attributes['pool_id'])->company_id,
            'customer_id' => fn (array $attributes) => Pool::find($attributes['pool_id'])->customer_id,
            'technician_user_id' => fn (array $attributes) => User::factory()->technician()->create([
                'company_id' => Pool::find($attributes['pool_id'])->company_id,
            ])->id,
            'service_schedule_id' => null,
            'scheduled_date' => fake()->dateTimeBetween('now', '+14 days')->format('Y-m-d'),
            'status' => Visit::STATUS_SCHEDULED,
        ];
    }

    public function skipped(): static
    {
        return $this->state(fn () => [
            'status' => Visit::STATUS_SKIPPED,
            'skip_reason' => 'Customer away',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => Visit::STATUS_COMPLETED,
            'started_at' => now()->subHour(),
            'completed_at' => now(),
        ]);
    }
}
