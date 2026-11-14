<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One outbound email attempt (service report, invoice, …).
 */
class EmailLog extends Model
{
    use BelongsToCompany;

    public const TYPE_SERVICE_REPORT = 'service_report';

    public const TYPE_INVOICE = 'invoice';

    public const STATUS_QUEUED = 'queued';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    public const STATUS_SKIPPED = 'skipped';

    /** status => [label, badge] */
    public const STATUSES = [
        self::STATUS_QUEUED => ['Queued', 'info'],
        self::STATUS_SENT => ['Sent', 'success'],
        self::STATUS_FAILED => ['Failed', 'danger'],
        self::STATUS_SKIPPED => ['Skipped', 'secondary'],
    ];

    /** type => label */
    public const TYPES = [
        self::TYPE_SERVICE_REPORT => 'Service report',
        self::TYPE_INVOICE => 'Invoice',
    ];

    protected $fillable = [
        'company_id',
        'customer_id',
        'related_type',
        'related_id',
        'to_email',
        'subject',
        'type',
        'status',
        'error',
        'sent_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status][0] ?? $this->status;
    }

    public function getStatusBadgeAttribute(): string
    {
        return self::STATUSES[$this->status][1] ?? 'secondary';
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }
}
