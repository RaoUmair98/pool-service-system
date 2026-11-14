<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A piece of gear on a pool (pump, filter, heater…). Simple name + notes list for v1.
 */
class PoolEquipment extends Model
{
    protected $table = 'pool_equipment';

    protected $fillable = [
        'pool_id',
        'name',
        'notes',
    ];

    public function pool(): BelongsTo
    {
        return $this->belongsTo(Pool::class);
    }
}
