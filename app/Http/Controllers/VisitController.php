<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Admin visit board: upcoming stops by day/technician, with reassign / reschedule / skip.
 */
class VisitController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $request->user()->company_id;
        $timezone = $request->user()->company->timezone ?: 'UTC';
        $today = Carbon::now($timezone)->toDateString();

        $dateFrom = $request->input('date_from', $today);
        $dateTo = $request->input('date_to', Carbon::parse($today)->addDays(14)->toDateString());

        $visits = Visit::query()
            ->forCompany($companyId)
            ->with(['customer', 'pool', 'technician'])
            ->when($dateFrom, fn ($q) => $q->whereDate('scheduled_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('scheduled_date', '<=', $dateTo))
            ->when($request->technician_user_id, fn ($q, $id) => $q->where('technician_user_id', $id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('scheduled_date')
            ->orderBy('id')
            ->paginate($this->perPage($request, 25))
            ->withQueryString();

        $technicians = User::query()
            ->where('company_id', $companyId)
            ->role(User::ROLE_TECHNICIAN)
            ->orderBy('name')
            ->get();

        return $this->infiniteOrView(
            $request,
            'visits.index',
            'visits.rows',
            compact('visits', 'technicians', 'dateFrom', 'dateTo'),
            'visits',
        );
    }

    public function show(Request $request, Visit $visit)
    {
        $this->ensureSameCompany($request, $visit);
        $visit->load(['customer', 'pool.equipment', 'technician', 'serviceSchedule', 'photos']);

        $technicians = User::query()
            ->where('company_id', $request->user()->company_id)
            ->role(User::ROLE_TECHNICIAN)
            ->selectable($visit->technician_user_id)
            ->orderBy('name')
            ->get();

        return view('visits.show', compact('visit', 'technicians'));
    }

    public function reassign(Request $request, Visit $visit)
    {
        $this->ensureSameCompany($request, $visit);

        if ($response = $this->rejectIfClosed($visit)) {
            return $response;
        }

        $data = $request->validate([
            'technician_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q
                    ->where('company_id', $request->user()->company_id)
                    ->where('is_active', true)),
            ],
        ]);

        $technician = User::query()
            ->where('company_id', $request->user()->company_id)
            ->whereKey($data['technician_user_id'])
            ->firstOrFail();

        abort_unless($technician->isTechnician(), 422, 'Assignee must be a technician.');

        $visit->update(['technician_user_id' => $technician->id]);

        return back()->with('success', "Visit reassigned to {$technician->name}.");
    }

    public function reschedule(Request $request, Visit $visit)
    {
        $this->ensureSameCompany($request, $visit);

        if ($response = $this->rejectIfClosed($visit)) {
            return $response;
        }

        $data = $request->validate([
            'scheduled_date' => ['required', 'date'],
        ]);

        $taken = Visit::query()
            ->where('pool_id', $visit->pool_id)
            ->whereDate('scheduled_date', $data['scheduled_date'])
            ->where('id', '!=', $visit->id)
            ->exists();

        if ($taken) {
            throw ValidationException::withMessages([
                'scheduled_date' => 'That pool already has a visit on this date.',
            ]);
        }

        $visit->update(['scheduled_date' => $data['scheduled_date']]);

        return back()->with('success', 'Visit moved to '.$visit->fresh()->scheduled_date->format('d M Y').'.');
    }

    public function skip(Request $request, Visit $visit)
    {
        $this->ensureSameCompany($request, $visit);

        if ($response = $this->rejectIfClosed($visit)) {
            return $response;
        }

        $data = $request->validate([
            'skip_reason' => ['required', 'string', 'max:2000'],
        ]);

        $visit->update([
            'status' => Visit::STATUS_SKIPPED,
            'skip_reason' => $data['skip_reason'],
        ]);

        return back()->with('success', 'Visit marked as skipped.');
    }

    protected function rejectIfClosed(Visit $visit)
    {
        if ($visit->isOpen()) {
            return null;
        }

        return back()->with('error', 'Only scheduled or in-progress visits can be changed.');
    }
}
