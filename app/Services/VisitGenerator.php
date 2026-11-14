<?php

namespace App\Services;

use App\Models\Company;
use App\Models\ServiceSchedule;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Creates upcoming Visit rows from active weekly schedules.
 * Idempotent: (pool_id, scheduled_date) is unique, so reruns are safe.
 */
class VisitGenerator
{
    public const HORIZON_DAYS = 14;

    /**
     * @return array{created: int, skipped: int}
     */
    public function generate(?Company $company = null): array
    {
        $created = 0;
        $skipped = 0;

        $companies = $company
            ? collect([$company])
            : Company::query()->orderBy('id')->get();

        foreach ($companies as $one) {
            $result = $this->generateForCompany($one);
            $created += $result['created'];
            $skipped += $result['skipped'];
        }

        return compact('created', 'skipped');
    }

    /**
     * @return array{created: int, skipped: int}
     */
    public function generateForCompany(Company $company): array
    {
        $created = 0;
        $skipped = 0;

        $timezone = $company->timezone ?: 'UTC';
        $today = Carbon::now($timezone)->startOfDay();
        $end = $today->copy()->addDays(self::HORIZON_DAYS);

        $schedules = ServiceSchedule::query()
            ->forCompany($company->id)
            ->active()
            ->with(['pool.customer'])
            ->whereHas('pool', fn ($q) => $q->active())
            ->whereHas('pool.customer', fn ($q) => $q->active())
            ->get();

        foreach ($schedules as $schedule) {
            foreach ($this->matchingDates($today, $end, $schedule->weekday) as $date) {
                $visit = Visit::query()->firstOrCreate(
                    [
                        'pool_id' => $schedule->pool_id,
                        'scheduled_date' => $date->toDateString(),
                    ],
                    [
                        'company_id' => $company->id,
                        'customer_id' => $schedule->pool->customer_id,
                        'technician_user_id' => $schedule->technician_user_id,
                        'service_schedule_id' => $schedule->id,
                        'status' => Visit::STATUS_SCHEDULED,
                    ]
                );

                if ($visit->wasRecentlyCreated) {
                    $created++;
                } else {
                    $skipped++;
                }
            }
        }

        return compact('created', 'skipped');
    }

    /**
     * @return Collection<int, Carbon>
     */
    protected function matchingDates(Carbon $start, Carbon $end, int $weekday): Collection
    {
        $dates = collect();
        $cursor = $start->copy();

        while ($cursor->lte($end)) {
            if ((int) $cursor->dayOfWeek === $weekday) {
                $dates->push($cursor->copy());
            }
            $cursor->addDay();
        }

        return $dates;
    }
}
