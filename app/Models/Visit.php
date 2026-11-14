<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A single service stop on a given day. Created by the visit generator from a
 * ServiceSchedule; admin can reassign, reschedule or skip without changing the schedule.
 * Technicians start/complete via Today's Route.
 */
class Visit extends Model
{
    use BelongsToCompany, HasFactory, LogsActivity;

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_SKIPPED = 'skipped';

    /** status => [label, badge colour, icon] */
    public const STATUSES = [
        self::STATUS_SCHEDULED => ['Scheduled', 'info', 'mdi-calendar'],
        self::STATUS_IN_PROGRESS => ['In progress', 'warning', 'mdi-progress-clock'],
        self::STATUS_COMPLETED => ['Completed', 'success', 'mdi-check-circle'],
        self::STATUS_SKIPPED => ['Skipped', 'secondary', 'mdi-cancel'],
    ];

    /** checklist key => label */
    public const CHECKLIST_ITEMS = [
        'skim' => 'Skim',
        'brush' => 'Brush',
        'vacuum' => 'Vacuum',
        'empty_baskets' => 'Empty baskets',
        'clean_filter' => 'Clean filter',
    ];

    protected $fillable = [
        'company_id',
        'pool_id',
        'customer_id',
        'technician_user_id',
        'service_schedule_id',
        'scheduled_date',
        'status',
        'started_at',
        'completed_at',
        'skip_reason',
        'notes',
        'checklist',
        'reading_ph',
        'reading_free_chlorine',
        'reading_alkalinity',
    ];

    protected $attributes = [
        'status' => self::STATUS_SCHEDULED,
    ];

    protected function casts(): array
    {
        return [
            'scheduled_date' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'checklist' => 'array',
            'reading_ph' => 'decimal:2',
            'reading_free_chlorine' => 'decimal:2',
            'reading_alkalinity' => 'decimal:1',
        ];
    }

    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_user_id');
    }

    public function serviceSchedule(): BelongsTo
    {
        return $this->belongsTo(ServiceSchedule::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(VisitPhoto::class)->orderBy('id');
    }

    /**
     * @return array<string, bool>
     */
    public function checklistItems(): array
    {
        $saved = $this->checklist ?? [];
        $items = [];

        foreach (self::CHECKLIST_ITEMS as $key => $label) {
            $items[$key] = (bool) ($saved[$key] ?? false);
        }

        return $items;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? 'secondary';
    }

    public function getStatusIconAttribute(): string
    {
        return self::STATUSES[$this->status][2] ?? 'mdi-circle-small';
    }

    public function isOpen(): bool
    {
        return in_array($this->status, [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS], true);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn($this->qualifyColumn('status'), [self::STATUS_SCHEDULED, self::STATUS_IN_PROGRESS]);
    }

    public function activityLabel(): string
    {
        $pool = $this->pool?->displayName() ?? 'Pool';
        $date = $this->scheduled_date?->format('d M Y') ?? '';

        return trim("$pool — $date");
    }

    protected function activityIgnored(): array
    {
        return ['company_id', 'checklist'];
    }

    protected function activityRelated(): array
    {
        return [
            'pool_id' => [Pool::class, null],
            'customer_id' => [Customer::class, 'name'],
            'technician_user_id' => [User::class, 'name'],
        ];
    }
}
