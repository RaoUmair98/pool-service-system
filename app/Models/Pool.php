<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasActiveStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

/**
 * A body of water at a customer's site. One customer can have several pools;
 * each has a monthly rate used later for invoicing.
 */
class Pool extends Model
{
    use BelongsToCompany, HasActiveStatus, HasFactory, LogsActivity;

    public const TYPE_CHLORINE = 'chlorine';

    public const TYPE_SALT = 'salt';

    /** type => [label, badge colour, icon] */
    public const TYPES = [
        self::TYPE_CHLORINE => ['Chlorine', 'primary', 'mdi-water'],
        self::TYPE_SALT => ['Salt', 'info', 'mdi-shaker-outline'],
    ];

    protected $fillable = [
        'company_id',
        'customer_id',
        'name',
        'type',
        'gallons',
        'photo_path',
        'monthly_rate',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
        'type' => self::TYPE_CHLORINE,
    ];

    protected function casts(): array
    {
        return [
            'gallons' => 'integer',
            'monthly_rate' => 'decimal:2',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function equipment(): HasMany
    {
        return $this->hasMany(PoolEquipment::class)->orderBy('id');
    }

    public function schedule(): HasOne
    {
        return $this->hasOne(ServiceSchedule::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type][0] ?? $this->type;
    }

    public function displayName(): string
    {
        return $this->name ?: 'Pool';
    }

    public function activityLabel(): string
    {
        $customer = $this->customer?->name ?? 'Customer #'.$this->customer_id;

        return $this->displayName().' — '.$customer;
    }

    protected function activityIgnored(): array
    {
        return ['company_id', 'photo_path'];
    }

    protected function activityRelated(): array
    {
        return [
            'customer_id' => [Customer::class, 'name'],
        ];
    }
}
