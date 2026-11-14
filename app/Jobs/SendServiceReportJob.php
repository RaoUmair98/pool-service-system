<?php

namespace App\Jobs;

use App\Models\Visit;
use App\Services\ServiceReportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendServiceReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $visitId) {}

    public function handle(ServiceReportService $reports): void
    {
        $visit = Visit::query()->find($this->visitId);

        if (! $visit || $visit->status !== Visit::STATUS_COMPLETED) {
            return;
        }

        $reports->sendNow($visit);
    }
}
