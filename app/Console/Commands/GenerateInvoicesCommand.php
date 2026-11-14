<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\InvoiceGenerator;
use Illuminate\Console\Command;

class GenerateInvoicesCommand extends Command
{
    protected $signature = 'invoices:generate
                            {--company= : Limit to a company id}
                            {--year= : Period year (default current)}
                            {--month= : Period month 1-12 (default current)}';

    protected $description = 'Create draft monthly invoices from active pool rates';

    public function handle(InvoiceGenerator $generator): int
    {
        $companyId = $this->option('company');
        $company = $companyId ? Company::find($companyId) : null;

        if ($companyId && ! $company) {
            $this->error("Company #{$companyId} not found.");

            return self::FAILURE;
        }

        $year = $this->option('year') ? (int) $this->option('year') : null;
        $month = $this->option('month') ? (int) $this->option('month') : null;

        if ($month !== null && ($month < 1 || $month > 12)) {
            $this->error('Month must be between 1 and 12.');

            return self::FAILURE;
        }

        $result = $generator->generate($company, $year, $month);

        $this->info("Created {$result['created']} invoice(s); {$result['skipped']} already existed.");

        return self::SUCCESS;
    }
}
