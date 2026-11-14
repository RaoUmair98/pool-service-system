<?php

namespace App\Services;

use App\Models\Visit;
use App\Models\VisitPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Technician visit lifecycle: start → save checklist/readings/photos → complete.
 */
class VisitService
{
    public function start(Visit $visit): Visit
    {
        if ($visit->status === Visit::STATUS_IN_PROGRESS) {
            return $visit;
        }

        if ($visit->status !== Visit::STATUS_SCHEDULED) {
            throw ValidationException::withMessages([
                'status' => 'Only scheduled visits can be started.',
            ]);
        }

        $visit->update([
            'status' => Visit::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        return $visit->fresh();
    }

    /**
     * @param  array{checklist?: array<string, bool>, reading_ph?: mixed, reading_free_chlorine?: mixed, reading_alkalinity?: mixed, notes?: ?string}  $data
     */
    public function saveProgress(Visit $visit, array $data): Visit
    {
        if (! $visit->isOpen()) {
            throw ValidationException::withMessages([
                'status' => 'This visit can no longer be edited.',
            ]);
        }

        if ($visit->status === Visit::STATUS_SCHEDULED) {
            $this->start($visit);
            $visit->refresh();
        }

        $visit->update([
            'checklist' => $data['checklist'] ?? $visit->checklist,
            'reading_ph' => $data['reading_ph'] ?? null,
            'reading_free_chlorine' => $data['reading_free_chlorine'] ?? null,
            'reading_alkalinity' => $data['reading_alkalinity'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        return $visit->fresh();
    }

    /**
     * @param  array{checklist?: array<string, bool>, reading_ph?: mixed, reading_free_chlorine?: mixed, reading_alkalinity?: mixed, notes?: ?string}  $data
     */
    public function complete(Visit $visit, array $data = []): Visit
    {
        if (! $visit->isOpen()) {
            throw ValidationException::withMessages([
                'status' => 'This visit is already finished.',
            ]);
        }

        $completed = DB::transaction(function () use ($visit, $data) {
            if ($data) {
                $this->saveProgress($visit, $data);
                $visit->refresh();
            } elseif ($visit->status === Visit::STATUS_SCHEDULED) {
                $this->start($visit);
                $visit->refresh();
            }

            $visit->update([
                'status' => Visit::STATUS_COMPLETED,
                'completed_at' => now(),
                'started_at' => $visit->started_at ?? now(),
            ]);

            return $visit->fresh();
        });

        // Outside the transaction so the queue sees a committed completed visit.
        app(ServiceReportService::class)->queue($completed);

        return $completed;
    }

    public function addPhoto(Visit $visit, UploadedFile $file, string $kind = VisitPhoto::KIND_OTHER, ?string $caption = null): VisitPhoto
    {
        if (! $visit->isOpen()) {
            throw ValidationException::withMessages([
                'status' => 'Photos can only be added while the visit is open.',
            ]);
        }

        if ($visit->status === Visit::STATUS_SCHEDULED) {
            $this->start($visit);
            $visit->refresh();
        }

        $path = $file->store('companies/'.$visit->company_id.'/visits/'.$visit->id, 'public');

        return $visit->photos()->create([
            'company_id' => $visit->company_id,
            'path' => $path,
            'kind' => $kind,
            'caption' => $caption,
        ]);
    }

    public function deletePhoto(VisitPhoto $photo): void
    {
        $visit = $photo->visit;

        if (! $visit || ! $visit->isOpen()) {
            throw ValidationException::withMessages([
                'status' => 'Photos can only be removed while the visit is open.',
            ]);
        }

        Storage::disk('public')->delete($photo->path);
        $photo->delete();
    }
}
