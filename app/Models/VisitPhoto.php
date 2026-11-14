<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VisitPhoto extends Model
{
    use BelongsToCompany;

    public const KIND_BEFORE = 'before';

    public const KIND_AFTER = 'after';

    public const KIND_OTHER = 'other';

    public const KINDS = [
        self::KIND_BEFORE => 'Before',
        self::KIND_AFTER => 'After',
        self::KIND_OTHER => 'Other',
    ];

    protected $fillable = [
        'visit_id',
        'company_id',
        'path',
        'kind',
        'caption',
    ];

    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
