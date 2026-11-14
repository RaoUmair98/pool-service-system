<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Services\ServiceReportService;
use Illuminate\Http\Request;

/**
 * Admin preview and resend of the customer service report for a completed visit.
 */
class ServiceReportController extends Controller
{
    public function show(Request $request, Visit $visit)
    {
        $this->ensureSameCompany($request, $visit);
        abort_unless($visit->status === Visit::STATUS_COMPLETED, 404);

        $visit->load(['customer', 'pool', 'technician', 'company', 'photos']);

        return view('visits.report', [
            'visit' => $visit,
            'checklist' => $visit->checklistItems(),
        ]);
    }

    public function resend(Request $request, Visit $visit, ServiceReportService $reports)
    {
        $this->ensureSameCompany($request, $visit);
        abort_unless($visit->status === Visit::STATUS_COMPLETED, 404);

        $log = $reports->sendNow($visit->load(['customer', 'pool', 'technician', 'company', 'photos']));

        $message = match ($log->status) {
            'sent' => 'Service report emailed to '.$log->to_email.'.',
            'skipped' => 'Report not sent: '.$log->error,
            'failed' => 'Failed to send report: '.$log->error,
            default => 'Report queued.',
        };

        $flash = $log->status === 'sent' ? 'success' : 'error';

        return back()->with($flash, $message);
    }
}
