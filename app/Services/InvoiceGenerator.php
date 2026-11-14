<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

/**
 * Creates draft monthly invoices from active pool rates (one per customer per period).
 */
class InvoiceGenerator
{
    /**
     * @return array{created: int, skipped: int}
     */
    public function generate(?Company $company = null, ?int $year = null, ?int $month = null): array
    {
        $year ??= (int) now()->format('Y');
        $month ??= (int) now()->format('n');

        $created = 0;
        $skipped = 0;

        $companies = $company
            ? collect([$company])
            : Company::query()->orderBy('id')->get();

        foreach ($companies as $one) {
            $result = $this->generateForCompany($one, $year, $month);
            $created += $result['created'];
            $skipped += $result['skipped'];
        }

        return compact('created', 'skipped');
    }

    /**
     * @return array{created: int, skipped: int}
     */
    public function generateForCompany(Company $company, int $year, int $month): array
    {
        $created = 0;
        $skipped = 0;

        $customers = Customer::query()
            ->forCompany($company->id)
            ->active()
            ->with(['pools' => fn ($q) => $q->active()->where('monthly_rate', '>', 0)])
            ->get()
            ->filter(fn (Customer $customer) => $customer->pools->isNotEmpty());

        foreach ($customers as $customer) {
            $exists = Invoice::query()
                ->where('company_id', $company->id)
                ->where('customer_id', $customer->id)
                ->where('period_year', $year)
                ->where('period_month', $month)
                ->exists();

            if ($exists) {
                $skipped++;

                continue;
            }

            DB::transaction(function () use ($company, $customer, $year, $month) {
                $subtotal = round((float) $customer->pools->sum('monthly_rate'), 2);
                $issued = now($company->timezone ?: 'UTC')->toDateString();

                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'customer_id' => $customer->id,
                    'invoice_number' => $this->nextNumber($company, $year, $month),
                    'period_year' => $year,
                    'period_month' => $month,
                    'status' => Invoice::STATUS_DRAFT,
                    'subtotal' => $subtotal,
                    'total' => $subtotal,
                    'currency' => $company->currency ?: 'USD',
                    'issued_at' => $issued,
                    'due_at' => now($company->timezone ?: 'UTC')->addDays(14)->toDateString(),
                ]);

                foreach ($customer->pools as $pool) {
                    $invoice->items()->create([
                        'pool_id' => $pool->id,
                        'description' => 'Monthly service — '.$pool->displayName(),
                        'amount' => $pool->monthly_rate,
                    ]);
                }
            });

            $created++;
        }

        return compact('created', 'skipped');
    }

    protected function nextNumber(Company $company, int $year, int $month): string
    {
        $prefix = sprintf('INV-%d-%04d%02d-', $company->id, $year, $month);

        $last = Invoice::query()
            ->where('company_id', $company->id)
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $seq = 1;
        if ($last && preg_match('/-(\d+)$/', $last, $m)) {
            $seq = ((int) $m[1]) + 1;
        }

        return $prefix.str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }
}
