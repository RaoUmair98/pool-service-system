<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $year = (int) now()->format('Y');
        $month = (int) now()->format('n');

        return [
            'customer_id' => Customer::factory(),
            'company_id' => fn (array $attributes) => Customer::find($attributes['customer_id'])->company_id,
            'invoice_number' => 'INV-'.fake()->unique()->numerify('######'),
            'period_year' => $year,
            'period_month' => $month,
            'status' => Invoice::STATUS_DRAFT,
            'subtotal' => 149.00,
            'total' => 149.00,
            'currency' => 'USD',
            'issued_at' => now()->toDateString(),
            'due_at' => now()->addDays(14)->toDateString(),
        ];
    }

    public function sent(): static
    {
        return $this->state(fn () => [
            'status' => Invoice::STATUS_SENT,
            'sent_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'status' => Invoice::STATUS_PAID,
            'sent_at' => now()->subDay(),
            'paid_at' => now(),
            'payment_method' => 'bank_transfer',
        ]);
    }
}
