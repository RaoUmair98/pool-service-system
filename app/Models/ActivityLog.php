<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * One line of the activity log: who did what, to what, when and why.
 * Rows are only ever added; nothing in the app edits or deletes them.
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    /** action => [label, badge colour, icon, group] */
    public const ACTIONS = [
        'created' => ['Added', 'success', 'mdi-plus-circle', 'setup'],
        'updated' => ['Changed', 'primary', 'mdi-pencil-circle', 'setup'],
        'deleted' => ['Deleted', 'danger', 'mdi-delete-circle', 'setup'],
        'turned_on' => ['Turned on', 'success', 'mdi-eye-outline', 'setup'],
        'turned_off' => ['Turned off', 'secondary', 'mdi-eye-off-outline', 'setup'],
        'login' => ['Logged in', 'primary', 'mdi-login', 'login'],
        'logout' => ['Logged out', 'secondary', 'mdi-logout', 'login'],
        'login_failed' => ['Login failed', 'danger', 'mdi-lock-alert', 'login'],
    ];

    public const GROUPS = [
        'setup' => 'Setup changes (add, edit, delete, on/off)',
        'login' => 'Logins',
    ];

    protected $fillable = [
        'company_id', 'user_id', 'user_name', 'action', 'subject_type', 'subject_id', 'subject_label',
        'description', 'changes', 'reason', 'ip_address',
    ];

    protected $casts = ['changes' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }

    /**
     * Write one line. $subject is the record acted on; $label names it when there is no record (a failed login).
     * The line belongs to the company of whoever did it, or else of the record itself.
     */
    public static function record(string $action, ?Model $subject = null, ?array $changes = null, ?string $reason = null, ?string $label = null, ?User $user = null, ?int $companyId = null): self
    {
        $user ??= Auth::user();
        $label ??= $subject && method_exists($subject, 'activityLabel') ? $subject->activityLabel() : null;
        $kind = $subject ? Str::of(class_basename($subject))->snake(' ')->toString() : null;
        $companyId ??= $user?->company_id ?? ($subject instanceof Company ? $subject->getKey() : $subject?->company_id);

        return static::create([
            'company_id' => $companyId,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'subject_label' => $label ? Str::limit($label, 250) : null,
            'description' => Str::limit(trim(implode(' ', array_filter([
                self::ACTIONS[$action][0] ?? $action,
                in_array(self::ACTIONS[$action][3] ?? null, ['setup'], true) ? $kind : null,
                $label ? '- '.$label : null,
            ]))), 250),
            'changes' => $changes ?: null,
            'reason' => $reason,
            'ip_address' => request()?->ip(),
        ]);
    }

    public function getActionLabelAttribute(): string
    {
        return self::ACTIONS[$this->action][0] ?? $this->action;
    }

    public function getActionBadgeAttribute(): string
    {
        return self::ACTIONS[$this->action][1] ?? 'secondary';
    }

    public function getActionIconAttribute(): string
    {
        return self::ACTIONS[$this->action][2] ?? 'mdi-circle-small';
    }

    public function scopeForCompany(Builder $query, ?int $companyId): Builder
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['user_id'] ?? null, fn (Builder $q, $value) => $q->where('user_id', $value))
            ->when($filters['action'] ?? null, fn (Builder $q, $value) => $q->where('action', $value))
            ->when($filters['group'] ?? null, function (Builder $q, $value) {
                $q->whereIn('action', array_keys(array_filter(self::ACTIONS, fn ($action) => $action[3] === $value)));
            })
            ->when($filters['date_from'] ?? null, fn (Builder $q, $value) => $q->whereDate('created_at', '>=', $value))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $value) => $q->whereDate('created_at', '<=', $value))
            ->when($filters['search'] ?? null, function (Builder $q, $value) {
                $like = "%$value%";

                $q->where(fn (Builder $q) => $q->where('subject_label', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhere('reason', 'like', $like)
                    ->orWhere('user_name', 'like', $like)
                    ->orWhereHas('user', fn (Builder $user) => $user->where('name', 'like', $like)));
            });
    }
}
