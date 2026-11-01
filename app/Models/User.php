<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;
    use LogsActivity {
        activityChanges as baseActivityChanges;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function activityLabel(): string
    {
        return "$this->name ($this->email)";
    }

    protected function activityIgnored(): array
    {
        return ['password', 'remember_token', 'email_verified_at'];
    }

    /**
     * Name and email changes are logged with their values; a new password only as "changed".
     */
    public function activityChanges(bool $created = false): array
    {
        $changes = $this->baseActivityChanges($created);

        if (! $created && $this->wasChanged('password')) {
            $changes['Password'] = ['old' => null, 'new' => 'changed'];
        }

        return $changes;
    }
}
