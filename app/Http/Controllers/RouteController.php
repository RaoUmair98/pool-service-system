<?php

namespace App\Http\Controllers;

use App\Models\Visit;
use App\Models\VisitPhoto;
use App\Services\VisitService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Technician mobile flow: today's route, start visit, checklist/readings/photos, complete.
 */
class RouteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $timezone = $user->company->timezone ?: 'UTC';
        $today = Carbon::now($timezone)->toDateString();

        $visits = Visit::query()
            ->forCompany($user->company_id)
            ->where('technician_user_id', $user->id)
            ->whereDate('scheduled_date', $today)
            ->whereIn('status', [Visit::STATUS_SCHEDULED, Visit::STATUS_IN_PROGRESS, Visit::STATUS_COMPLETED])
            ->with(['customer', 'pool'])
            ->orderByRaw("FIELD(status, 'in_progress', 'scheduled', 'completed')")
            ->orderBy('id')
            ->get();

        return view('route.index', [
            'visits' => $visits,
            'today' => $today,
            'remaining' => $visits->whereIn('status', [Visit::STATUS_SCHEDULED, Visit::STATUS_IN_PROGRESS])->count(),
            'done' => $visits->where('status', Visit::STATUS_COMPLETED)->count(),
        ]);
    }

    public function show(Request $request, Visit $visit)
    {
        $this->ensureOwnVisit($request, $visit);

        $visit->load(['customer', 'pool.equipment', 'photos']);

        $lastVisit = Visit::query()
            ->where('pool_id', $visit->pool_id)
            ->where('status', Visit::STATUS_COMPLETED)
            ->where('id', '!=', $visit->id)
            ->latest('completed_at')
            ->first();

        return view('route.show', [
            'visit' => $visit,
            'lastVisit' => $lastVisit,
            'checklist' => $visit->checklistItems(),
        ]);
    }

    public function start(Request $request, Visit $visit, VisitService $service)
    {
        $this->ensureOwnVisit($request, $visit);
        $service->start($visit);

        return redirect()->route('route.show', $visit)->with('success', 'Visit started.');
    }

    public function update(Request $request, Visit $visit, VisitService $service)
    {
        $this->ensureOwnVisit($request, $visit);
        $service->saveProgress($visit, $this->validatedProgress($request));

        return redirect()->route('route.show', $visit)->with('success', 'Progress saved.');
    }

    public function complete(Request $request, Visit $visit, VisitService $service)
    {
        $this->ensureOwnVisit($request, $visit);
        $service->complete($visit, $this->validatedProgress($request));

        return redirect()->route('route.index')->with('success', $visit->customer->name.' — visit completed.');
    }

    public function storePhoto(Request $request, Visit $visit, VisitService $service)
    {
        $this->ensureOwnVisit($request, $visit);

        $data = $request->validate([
            'photo' => ['required', 'image', 'max:8192'],
            'kind' => ['required', Rule::in(array_keys(VisitPhoto::KINDS))],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $service->addPhoto($visit, $request->file('photo'), $data['kind'], $data['caption'] ?? null);

        return redirect()->route('route.show', $visit)->with('success', 'Photo added.');
    }

    public function destroyPhoto(Request $request, Visit $visit, VisitPhoto $photo, VisitService $service)
    {
        $this->ensureOwnVisit($request, $visit);
        abort_unless($photo->visit_id === $visit->id, 404);

        $service->deletePhoto($photo);

        return redirect()->route('route.show', $visit)->with('success', 'Photo removed.');
    }

    protected function ensureOwnVisit(Request $request, Visit $visit): void
    {
        abort_unless(
            (int) $visit->company_id === (int) $request->user()->company_id
            && (int) $visit->technician_user_id === (int) $request->user()->id,
            404
        );
    }

    /**
     * @return array{checklist: array<string, bool>, reading_ph: mixed, reading_free_chlorine: mixed, reading_alkalinity: mixed, notes: ?string}
     */
    protected function validatedProgress(Request $request): array
    {
        $data = $request->validate([
            'checklist' => ['nullable', 'array'],
            'checklist.*' => ['nullable'],
            'reading_ph' => ['nullable', 'numeric', 'min:0', 'max:14'],
            'reading_free_chlorine' => ['nullable', 'numeric', 'min:0', 'max:50'],
            'reading_alkalinity' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $checklist = [];
        foreach (array_keys(Visit::CHECKLIST_ITEMS) as $key) {
            $checklist[$key] = (bool) data_get($data, "checklist.$key", false);
        }

        return [
            'checklist' => $checklist,
            'reading_ph' => $data['reading_ph'] ?? null,
            'reading_free_chlorine' => $data['reading_free_chlorine'] ?? null,
            'reading_alkalinity' => $data['reading_alkalinity'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];
    }
}
