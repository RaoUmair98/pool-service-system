<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasActiveStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One weekly service day + technician per pool. The nightly generator
 * turns this into Visit rows for the coming two weeks.
 */
class ServiceSchedule extends Model
{
    use BelongsToCompany, HasActiveStatus, HasFactory, LogsActivity;

    /** Carbon dayOfWeek: 0 = Sunday … 6 = Saturday */
    public const WEEKDAYS = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday',
    ];

    protected $fillable = [
        'company_id',
        'pool_id',
        'technician_user_id',
        'weekday',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    protected function casts(): array
    {
        return [
            'weekday' => 'integer',
        ];
    }

    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_user_id');
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function getWeekdayLabelAttribute(): string
    {
        return self::WEEKDAYS[$this->weekday] ?? (string) $this->weekday;
    }

    public function activityLabel(): string
    {
        return ($this->pool?->activityLabel() ?? 'Pool').' — '.$this->weekday_label;
    }

    protected function activityIgnored(): array
    {
        return ['company_id'];
    }

    protected function activityRelated(): array
    {
        return [
            'pool_id' => [Pool::class, null],
            'technician_user_id' => [User::class, 'name'],
        ];
    }
}
