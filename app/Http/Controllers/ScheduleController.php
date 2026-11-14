<?php

namespace App\Http\Controllers;

use App\Models\Pool;
use App\Models\ServiceSchedule;
use App\Models\User;
use App\Services\VisitGenerator;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Weekly service day + technician for a pool. Saving also generates upcoming visits.
 */
class ScheduleController extends Controller
{
    public function edit(Request $request, Pool $pool)
    {
        $this->ensureSameCompany($request, $pool);
        $pool->load(['schedule.technician', 'customer']);

        return view('schedules.edit', [
            'pool' => $pool,
            'schedule' => $pool->schedule ?? new ServiceSchedule([
                'is_active' => true,
                'weekday' => 1,
            ]),
            'technicians' => $this->techniciansFor($request, $pool->schedule?->technician_user_id),
        ]);
    }

    public function update(Request $request, Pool $pool, VisitGenerator $generator)
    {
        $this->ensureSameCompany($request, $pool);

        $data = $request->validate([
            'weekday' => ['required', 'integer', Rule::in(array_keys(ServiceSchedule::WEEKDAYS))],
            'technician_user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q
                    ->where('company_id', $request->user()->company_id)
                    ->where('is_active', true)),
            ],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $technician = User::query()
            ->where('company_id', $request->user()->company_id)
            ->whereKey($data['technician_user_id'])
            ->firstOrFail();

        abort_unless($technician->isTechnician(), 422, 'Assignee must be a technician.');

        $schedule = $pool->schedule()->updateOrCreate(
            ['pool_id' => $pool->id],
            [
                'company_id' => $pool->company_id,
                'technician_user_id' => $technician->id,
                'weekday' => $data['weekday'],
                'is_active' => $request->boolean('is_active', true),
            ]
        );

        $result = $generator->generateForCompany($request->user()->company);

        return redirect()
            ->route('pools.show', $pool)
            ->with('success', "Schedule saved ({$schedule->weekday_label} · {$technician->name}). Generated {$result['created']} new visit(s).");
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     */
    protected function techniciansFor(Request $request, ?int $includeId = null)
    {
        return User::query()
            ->where('company_id', $request->user()->company_id)
            ->role(User::ROLE_TECHNICIAN)
            ->selectable($includeId)
            ->orderBy('name')
            ->get();
    }
}
