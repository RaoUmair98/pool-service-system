<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Concerns\HasActiveStatus;
use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles, HasActiveStatus;
    use LogsActivity {
        activityChanges as baseActivityChanges;
    }

    /** Owner and office staff: run the company, manage the team. */
    public const ROLE_ADMIN = 'admin';

    /** Field staff who do the pool visits. */
    public const ROLE_TECHNICIAN = 'technician';

    /** role => [label, badge colour, icon] */
    public const ROLES = [
        self::ROLE_ADMIN => ['Admin', 'primary', 'mdi-shield-account-outline'],
        self::ROLE_TECHNICIAN => ['Technician', 'info', 'mdi-account-hard-hat'],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'company_id',
        'name',
        'email',
        'phone',
        'password',
        'is_active',
    ];

    protected $attributes = [
        'is_active' => true,
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

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    public function isTechnician(): bool
    {
        return $this->hasRole(self::ROLE_TECHNICIAN);
    }

    /**
     * The user's role name (admin / technician). Each user has exactly one.
     */
    public function getRoleNameAttribute(): ?string
    {
        return $this->getRoleNames()->first();
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role_name][0] ?? '-';
    }

    public function activityLabel(): string
    {
        return "$this->name ($this->email)";
    }

    protected function activityIgnored(): array
    {
        return ['password', 'remember_token', 'email_verified_at', 'company_id'];
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
