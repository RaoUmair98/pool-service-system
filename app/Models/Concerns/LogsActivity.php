<?php

namespace App\Models\Concerns;

use App\Models\ActivityLog;
use Illuminate\Support\Str;

/**
 * Writes an activity log line whenever the record is added, changed,
 * turned on or off, or deleted, whichever screen the change came from.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            ActivityLog::record('created', $model, $model->activityChanges(created: true));
        });

        static::updated(function ($model) {
            $changes = $model->activityChanges();

            if (! $changes) {
                return;
            }

            // The on/off switch on its own reads better as its own kind of entry.
            $action = array_keys($changes) === ['Active']
                ? ($model->is_active ? 'turned_on' : 'turned_off')
                : 'updated';

            ActivityLog::record($action, $model, $changes);
        });

        static::deleted(function ($model) {
            ActivityLog::record('deleted', $model);
        });
    }

    /**
     * Name of the record as a person would say it. Models override this where the plain name is not enough.
     */
    public function activityLabel(): string
    {
        return (string) $this->name;
    }

    /**
     * Fields that change behind the scenes and would only add noise.
     */
    protected function activityIgnored(): array
    {
        return [];
    }

    /**
     * [field label => ['old' => ..., 'new' => ...]] for what was just saved.
     */
    public function activityChanges(bool $created = false): array
    {
        $ignored = array_merge(['id', 'created_at', 'updated_at'], $this->activityIgnored());
        $attributes = $created ? $this->getAttributes() : $this->getChanges();
        $changes = [];

        foreach ($attributes as $field => $new) {
            if (in_array($field, $ignored, true) || ($created && ($new === null || $new === ''))) {
                continue;
            }

            $changes[$this->activityFieldLabel($field)] = [
                'old' => $created ? null : $this->activityValue($field, $this->getOriginal($field)),
                'new' => $this->activityValue($field, $new),
            ];
        }

        return $changes;
    }

    /**
     * [foreign key => [model class, attribute to show or null for its activityLabel()]], so ids are logged as names.
     */
    protected function activityRelated(): array
    {
        return [];
    }

    protected function activityFieldLabel(string $field): string
    {
        return [
            'is_active' => 'Active',
        ][$field] ?? Str::of($field)->beforeLast('_id')->replace('_', ' ')->title()->toString();
    }

    /**
     * Ids become names and switches become Yes / No, so the log reads without looking anything up.
     */
    protected function activityValue(string $field, $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($field === 'is_active') {
            return $value ? 'Yes' : 'No';
        }

        $related = $this->activityRelated()[$field] ?? null;

        if ($related) {
            $record = $related[0]::find($value);

            return $record ? ($related[1] ? $record->{$related[1]} : $record->activityLabel()) : "#$value";
        }

        return (string) $value;
    }
}
