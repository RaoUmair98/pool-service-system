<?php

namespace Database\Factories;

use App\Models\Pool;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServiceSchedule>
 */
class ServiceScheduleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'pool_id' => Pool::factory(),
            'company_id' => fn (array $attributes) => Pool::find($attributes['pool_id'])->company_id,
            'technician_user_id' => fn (array $attributes) => User::factory()->technician()->create([
                'company_id' => Pool::find($attributes['pool_id'])->company_id,
            ])->id,
            'weekday' => fake()->numberBetween(0, 6),
            'is_active' => true,
        ];
    }
}
