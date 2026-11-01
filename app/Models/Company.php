<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A pool service business. Created at signup together with its owner, who becomes its first admin.
 */
class Company extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = ['name', 'email', 'phone', 'address'];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
