<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Pool;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $companyId = $user->company_id;
        $timezone = $user->company->timezone ?: 'UTC';
        $now = Carbon::now($timezone);
        $today = $now->toDateString();
        $weekStart = $now->copy()->startOfWeek();
        $weekEnd = $now->copy()->endOfWeek();
        $currency = $user->company->currency ?? 'USD';

        $stats = [
            'currency' => $currency,
            'today' => $today,
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
        ];

        $charts = [
            'visits_week' => null,
            'invoice_status' => null,
            'tech_workload' => null,
        ];

        if ($user->can('manage schedules') || $user->can('view dashboard') || $user->can('view visits')) {
            $visitBase = Visit::query()->forCompany($companyId);
            $todayVisits = (clone $visitBase)->whereDate('scheduled_date', $today);

            $stats['visits_today_total'] = (clone $todayVisits)->count();
            $stats['visits_today_done'] = (clone $todayVisits)->where('status', Visit::STATUS_COMPLETED)->count();
            $stats['visits_today_pending'] = (clone $todayVisits)
                ->whereIn('status', [Visit::STATUS_SCHEDULED, Visit::STATUS_IN_PROGRESS])
                ->count();

            $stats['visits_overdue'] = (clone $visitBase)
                ->whereIn('status', [Visit::STATUS_SCHEDULED, Visit::STATUS_IN_PROGRESS])
                ->whereDate('scheduled_date', '<', $today)
                ->count();

            $stats['visits_open'] = (clone $visitBase)
                ->whereIn('status', [Visit::STATUS_SCHEDULED, Visit::STATUS_IN_PROGRESS])
                ->whereDate('scheduled_date', '>=', $today)
                ->count();

            $weekRows = (clone $visitBase)
                ->select('scheduled_date', 'status', DB::raw('count(*) as total'))
                ->whereDate('scheduled_date', '>=', $weekStart->toDateString())
                ->whereDate('scheduled_date', '<=', $weekEnd->toDateString())
                ->groupBy('scheduled_date', 'status')
                ->get();

            $labels = [];
            $completed = [];
            $pending = [];
            $skipped = [];
            for ($day = $weekStart->copy(); $day->lte($weekEnd); $day->addDay()) {
                $key = $day->toDateString();
                $labels[] = $day->format('D');
                $dayRows = $weekRows->where('scheduled_date', $key);
                // scheduled_date may be Carbon cast
                if ($dayRows->isEmpty()) {
                    $dayRows = $weekRows->filter(fn ($row) => Carbon::parse($row->scheduled_date)->toDateString() === $key);
                }
                $completed[] = (int) $dayRows->where('status', Visit::STATUS_COMPLETED)->sum('total');
                $pending[] = (int) $dayRows->whereIn('status', [Visit::STATUS_SCHEDULED, Visit::STATUS_IN_PROGRESS])->sum('total');
                $skipped[] = (int) $dayRows->where('status', Visit::STATUS_SKIPPED)->sum('total');
            }

            $charts['visits_week'] = [
                'labels' => $labels,
                'completed' => $completed,
                'pending' => $pending,
                'skipped' => $skipped,
            ];

            $stats['visits_week_done'] = array_sum($completed);
            $stats['visits_week_pending'] = array_sum($pending);
            $stats['visits_week_total'] = $stats['visits_week_done'] + $stats['visits_week_pending'] + array_sum($skipped);
        }

        if ($user->can('manage customers') || $user->can('view dashboard')) {
            $stats['customers_active'] = Customer::query()->forCompany($companyId)->active()->count();
        }

        if ($user->can('manage pools') || $user->can('view dashboard')) {
            $pools = Pool::query()->forCompany($companyId)->active();
            $stats['pools_active'] = (clone $pools)->count();
            $stats['mrr'] = (float) (clone $pools)->sum('monthly_rate');
        }

        if ($user->can('manage invoices') || $user->can('view dashboard')) {
            $invoices = Invoice::query()->forCompany($companyId);
            $stats['invoices_draft'] = (clone $invoices)->where('status', Invoice::STATUS_DRAFT)->count();
            $stats['invoices_sent'] = (clone $invoices)->where('status', Invoice::STATUS_SENT)->count();
            $stats['invoices_paid'] = (clone $invoices)->where('status', Invoice::STATUS_PAID)->count();
            $stats['unpaid_invoices'] = $stats['invoices_sent'];
            $stats['unpaid_amount'] = (float) (clone $invoices)->where('status', Invoice::STATUS_SENT)->sum('total');

            $charts['invoice_status'] = [
                'labels' => ['Draft', 'Unpaid', 'Paid'],
                'values' => [
                    $stats['invoices_draft'],
                    $stats['invoices_sent'],
                    $stats['invoices_paid'],
                ],
            ];
        }

        if ($user->can('manage team') || $user->can('view dashboard')) {
            $stats['technicians_active'] = User::where('company_id', $companyId)
                ->role(User::ROLE_TECHNICIAN)
                ->active()
                ->count();
        }

        if ($user->can('manage schedules') || $user->can('manage team')) {
            $workload = Visit::query()
                ->forCompany($companyId)
                ->select('technician_user_id', DB::raw('count(*) as total'))
                ->whereDate('scheduled_date', '>=', $weekStart->toDateString())
                ->whereDate('scheduled_date', '<=', $weekEnd->toDateString())
                ->whereIn('status', [Visit::STATUS_SCHEDULED, Visit::STATUS_IN_PROGRESS, Visit::STATUS_COMPLETED])
                ->groupBy('technician_user_id')
                ->with('technician:id,name')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            $charts['tech_workload'] = [
                'labels' => $workload->map(fn ($row) => $row->technician?->name ?? 'Unassigned')->values()->all(),
                'values' => $workload->map(fn ($row) => (int) $row->total)->values()->all(),
            ];
        }

        $upcomingVisits = ($user->can('manage schedules') || $user->can('view visits'))
            ? Visit::query()
                ->forCompany($companyId)
                ->with(['customer', 'pool', 'technician'])
                ->whereIn('status', [Visit::STATUS_SCHEDULED, Visit::STATUS_IN_PROGRESS])
                ->whereDate('scheduled_date', '>=', $today)
                ->orderBy('scheduled_date')
                ->orderBy('id')
                ->limit(8)
                ->get()
            : collect();

        $recentActivity = $user->can('view activity log')
            ? ActivityLog::query()
                ->forCompany($companyId)
                ->with('user')
                ->latest()
                ->latest('id')
                ->limit(8)
                ->get()
            : collect();

        return view('dashboard', compact('stats', 'charts', 'upcomingVisits', 'recentActivity'));
    }
}
