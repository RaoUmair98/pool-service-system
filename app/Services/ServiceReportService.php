<?php

namespace App\Services;

use App\Jobs\SendServiceReportJob;
use App\Mail\ServiceReportMail;
use App\Models\EmailLog;
use App\Models\Visit;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Builds and sends (or queues) the customer service report for a completed visit.
 */
class ServiceReportService
{
    public function queue(Visit $visit): void
    {
        SendServiceReportJob::dispatch($visit->id);
    }

    public function sendNow(Visit $visit): EmailLog
    {
        $visit->loadMissing(['customer', 'pool', 'technician', 'company', 'photos']);

        $to = trim((string) $visit->customer?->email);
        $subject = (new ServiceReportMail($visit))->envelope()->subject;

        $log = EmailLog::create([
            'company_id' => $visit->company_id,
            'customer_id' => $visit->customer_id,
            'related_type' => $visit->getMorphClass(),
            'related_id' => $visit->id,
            'to_email' => $to !== '' ? $to : '(none)',
            'subject' => $subject,
            'type' => EmailLog::TYPE_SERVICE_REPORT,
            'status' => EmailLog::STATUS_QUEUED,
        ]);

        if ($to === '') {
            $log->update([
                'status' => EmailLog::STATUS_SKIPPED,
                'error' => 'Customer has no email address.',
            ]);

            return $log->fresh();
        }

        try {
            Mail::to($to)->send(new ServiceReportMail($visit));

            $log->update([
                'status' => EmailLog::STATUS_SENT,
                'sent_at' => now(),
                'error' => null,
            ]);
        } catch (Throwable $e) {
            $log->update([
                'status' => EmailLog::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
        }

        return $log->fresh();
    }
}
