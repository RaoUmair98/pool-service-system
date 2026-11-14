<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use BelongsToCompany, HasFactory, LogsActivity;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SENT = 'sent';

    public const STATUS_PAID = 'paid';

    /** status => [label, badge, icon] */
    public const STATUSES = [
        self::STATUS_DRAFT => ['Draft', 'secondary', 'mdi-file-document-outline'],
        self::STATUS_SENT => ['Sent', 'info', 'mdi-email-outline'],
        self::STATUS_PAID => ['Paid', 'success', 'mdi-check-circle'],
    ];

    protected $fillable = [
        'company_id',
        'customer_id',
        'invoice_number',
        'period_year',
        'period_month',
        'status',
        'subtotal',
        'total',
        'currency',
        'issued_at',
        'due_at',
        'sent_at',
        'paid_at',
        'payment_method',
        'payment_note',
        'notes',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
    ];

    protected function casts(): array
    {
        return [
            'period_year' => 'integer',
            'period_month' => 'integer',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'issued_at' => 'date',
            'due_at' => 'date',
            'sent_at' => 'datetime',
            'paid_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('id');
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

    public function getPeriodLabelAttribute(): string
    {
        return sprintf('%04d-%02d', $this->period_year, $this->period_month);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isUnpaid(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function activityLabel(): string
    {
        return $this->invoice_number.' ('.$this->period_label.')';
    }

    protected function activityIgnored(): array
    {
        return ['company_id'];
    }

    protected function activityRelated(): array
    {
        return [
            'customer_id' => [Customer::class, 'name'],
        ];
    }
}
