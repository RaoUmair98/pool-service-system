<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\HasActiveStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A pool owner's contact and service site. Customers have no login in v1;
 * they receive reports and invoices by email.
 */
class Customer extends Model
{
    use BelongsToCompany, HasActiveStatus, HasFactory, LogsActivity;

    protected $fillable = [
        'company_id',
        'name',
        'phone',
        'email',
        'service_address',
        'access_notes',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
    ];

    public function pools(): HasMany
    {
        return $this->hasMany(Pool::class)->orderBy('name');
    }

    protected function activityIgnored(): array
    {
        return ['company_id'];
    }
}
