<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Pool;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Pool>
 */
class PoolFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'company_id' => fn (array $attributes) => Customer::find($attributes['customer_id'])->company_id,
            'name' => fake()->randomElement(['Main', 'Spa', 'Backyard', null]),
            'type' => fake()->randomElement([Pool::TYPE_CHLORINE, Pool::TYPE_SALT]),
            'gallons' => fake()->randomElement([10000, 15000, 20000, 25000, null]),
            'monthly_rate' => fake()->randomElement([89.00, 120.00, 149.50, 199.00]),
            'is_active' => true,
        ];
    }

    public function chlorine(): static
    {
        return $this->state(fn () => ['type' => Pool::TYPE_CHLORINE]);
    }

    public function salt(): static
    {
        return $this->state(fn () => ['type' => Pool::TYPE_SALT]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
