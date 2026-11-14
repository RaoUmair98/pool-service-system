<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Services\VisitGenerator;
use Illuminate\Console\Command;

class GenerateVisitsCommand extends Command
{
    protected $signature = 'visits:generate {--company= : Limit to a company id}';

    protected $description = 'Create scheduled visits for the next 14 days from active weekly schedules';

    public function handle(VisitGenerator $generator): int
    {
        $companyId = $this->option('company');
        $company = $companyId ? Company::find($companyId) : null;

        if ($companyId && ! $company) {
            $this->error("Company #{$companyId} not found.");

            return self::FAILURE;
        }

        $result = $generator->generate($company);

        $this->info("Created {$result['created']} visit(s); {$result['skipped']} already existed.");

        return self::SUCCESS;
    }
}
