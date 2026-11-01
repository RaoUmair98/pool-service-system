<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasActiveStatus
{
    public function initializeHasActiveStatus(): void
    {
        $this->mergeCasts(['is_active' => 'boolean']);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where($this->qualifyColumn('is_active'), true);
    }

    /**
     * Active records for a dropdown, plus the one already saved on the
     * record being edited even if it has since been deactivated.
     */
    public function scopeSelectable(Builder $query, $include = null): Builder
    {
        return $query->where(function (Builder $query) use ($include) {
            $query->where($this->qualifyColumn('is_active'), true)
                ->when($include, fn (Builder $query) => $query->orWhere($this->qualifyColumn('id'), $include));
        });
    }

    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'active' => $query->where($this->qualifyColumn('is_active'), true),
            'inactive' => $query->where($this->qualifyColumn('is_active'), false),
            default => $query,
        };
    }
}
